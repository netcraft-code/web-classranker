<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Customer;

use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\Customer\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Webkul\Core\Rules\PhoneNumber;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerRepository;

class AuthController extends CustomerController
{
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerGroupRepository $customerGroupRepository,
    ) {}

    /**
     * Register the customer.
     */
    public function register(): Response
    {
        $this->validate(request(), [
            'first_name'   => 'required|string|max:50',
            'last_name'    => 'required|string|max:50',
            'phone'        => 'required|digits:10|unique:customers,phone',
            'state'        => 'required|string|max:5',
            'country'      => 'required|string|max:5',
            'city'         => 'required|string|max:20',
        ]);

        Event::dispatch('customer.registration.before');

        $customer = $this->customerRepository->create([
            'first_name'        => request('first_name'),
            'last_name'         => request('last_name'),
            'phone'             => request('phone'),
            'is_verified'       => 0,
            'channel_id'        => core()->getCurrentChannel()->id,
            'customer_group_id' => $this->customerGroupRepository->findOneWhere(['code' => 'general'])->id,
        ]);

        $customer->addresses()->create([
            'customer_id'     => $customer->id,
            'first_name'      => request('first_name'),
            'last_name'       => request('last_name'),
            'phone'           => request('phone'),
            'country'         => request('country'),
            'state'           => request('state'),
            'city'            => request('city'),
            'default_address' => 1,
        ]);

        Event::dispatch('customer.registration.after', $customer);

        return response([
            'success'  => true,
            'message' => trans('class_ranker_api::app.shop.customer.accounts.create-success'),
        ]);
    }

    /**
     * Step 1: Phone number lo, OTP generate karo aur cache mein store karo.
     */
    public function sendOtp(Request $request): Response
    {
        $request->validate([
            'phone' => 'required|digits:10|exists:customers,phone',
        ]);

        $phone = $request->phone;

        if (core()->getConfigData('class_ranker.settings.sms_service.status') != 1) {
            $otp = '0000';

            $cacheKey = 'otp_' . $phone;
            Cache::put($cacheKey, [
                'otp'       => $otp,
                'attempts'  => 0,
            ], now()->addMinutes(10));

            $responseData = [
                'success' => true,
                'message' => 'OTP sent successfully.',
            ];

            if (app()->isLocal()) {
                $responseData['otp'] = $otp;
            }

            return response($responseData);
            
            \Log::error('OTP service is disabled in configuration.');
            return response([
                'success' => false,
                'message' => 'OTP service is currently unavailable. Please try again later.',
            ], 503);
        }

        $authKey    = core()->getConfigData('class_ranker.settings.sms_service.auth_key');
        $templateId = core()->getConfigData('class_ranker.settings.sms_service.template_id');
        
        $response = Http::timeout(5)
            ->retry(3, 200)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post(
                'https://control.msg91.com/api/v5/otp?authkey=' . $authKey .
                '&mobile=+91' . $phone .
                '&template_id=' . $templateId,
                [
                    "OTP" => "1234",
                ]
            );
            
        if (! $response->successful()) {
            return response([
                'success' => false,
                'message' => 'Failed to send OTP.',
            ], 500);
        }
        
        $responseData = [
            'success' => true,
            'message' => 'OTP sent successfully.',
        ];

        return response($responseData);
    }

    public function resendOtp(Request $request): Response
    {
        $request->validate([
            'phone' => 'required|digits:10|exists:customers,phone',
        ]);

        $phone = $request->phone;

        if (core()->getConfigData('class_ranker.settings.sms_service.status') != 1) {
            $otp = '0000';

            $cacheKey = 'otp_' . $phone;
            Cache::put($cacheKey, [
                'otp'       => $otp,
                'attempts'  => 0,
            ], now()->addMinutes(10));

            $responseData = [
                'success' => true,
                'message' => 'OTP sent successfully.',
            ];

            if (app()->isLocal()) {
                $responseData['otp'] = $otp;
            }

            return response($responseData);
            
            \Log::error('OTP service is disabled in configuration.');
            return response([
                'success' => false,
                'message' => 'OTP service is currently unavailable. Please try again later.',
            ], 503);
        }

        $authKey    = core()->getConfigData('class_ranker.settings.sms_service.auth_key');
        $templateId = core()->getConfigData('class_ranker.settings.sms_service.template_id');

        try {
            $response = Http::timeout(5)
                ->retry(3, 200)
                ->get(
                    'https://control.msg91.com/api/v5/otp/retry?' . http_build_query([
                        'authkey'   => $authKey,
                        'mobile'    => '91' . $phone,
                        'retrytype' => 'text',
                    ])
                );
        } catch (\Exception $e) {
            \Log::error('Error during OTP verification: ' . $e->getMessage());
            
            return $this->sendOtp($request);
        }

        if (! $response->successful()) {
            return response([
                'success' => false,
                'message' => 'Failed to send OTP.',
            ], 500);
        }
        
        $responseData = [
            'success' => true,
            'message' => 'OTP sent successfully.',
        ];

        return response($responseData);
    }

    /**
     * Step 2: OTP verify karo aur token return karo.
     */
    public function verifyOtp(Request $request): Response
    {
        $request->validate([
            'phone'       => 'required|digits:10|exists:customers,phone',
            'otp'         => 'required|digits:4',
            'device_name' => 'required|string',
        ]);

        $phone    = $request->phone;

        if (core()->getConfigData('class_ranker.settings.sms_service.status') != 1) {
            $cacheKey = 'otp_' . $phone;

            // Cache mein OTP check karo
            $cached = Cache::get($cacheKey);

            if (! $cached) {
                return response([
                    'success' => false,
                    'message' => 'OTP expired or not found. Please request a new one.',
                ], 422);
            }

            // Max 5 attempts allowed
            if ($cached['attempts'] >= 5) {
                Cache::forget($cacheKey);
                return response([
                    'success' => false,
                    'message' => 'Too many incorrect attempts. Please request a new OTP.',
                ], 429);
            }

            // OTP match check
            if ((string) $cached['otp'] !== (string) $request->otp) {
                // Attempt count badhao
                Cache::put($cacheKey, [
                    ...$cached,
                    'attempts' => $cached['attempts'] + 1,
                ], now()->addMinutes(10));

                $remaining = 4 - $cached['attempts'];

                return response([
                    'success'            => false,
                    'message'            => 'Invalid OTP.',
                    'attempts_remaining' => max($remaining, 0),
                ], 422);
            }

            // OTP sahi hai — cache delete karo
            Cache::forget($cacheKey);
            Cache::forget('otp_throttle_' . $phone);

            // return response([
            //     'success' => false,
            //     'message' => 'OTP service is currently unavailable. Please try again later.',
            // ], 503);
        } else {
            $authKey    = core()->getConfigData('class_ranker.settings.sms_service.auth_key');
            
            try {
                $response = Http::timeout(10)
                    ->retry(3, 200)
                    ->get('https://control.msg91.com/api/v5/otp/verify', [
                        'authkey' => $authKey,
                        'mobile'  => '91' . $phone,
                        'otp'     => $request->otp,
                    ]);
            } catch (\Exception $e) {
                return response([
                    'success' => false,
                    'message' => 'OTP service unavailable. Try again.',
                ], 500);
            }

            if (! $response->successful() || ($response->json()['type'] ?? '') !== 'success') {
                return response([
                    'success' => false,
                    'message' => 'Invalid OTP.',
                ], 400);
            }
        }

        // Customer fetch karo
        $customer = $this->customerRepository->findOneWhere(['phone' => $phone]);

        if (! $customer) {
            return response([
                'success' => false,
                'message' => 'Customer not found.'
            ], 404);
        }

        // Phone verify mark karo
        if (! $customer->is_verified) {
            $this->customerRepository->update(['is_verified' => 1], $customer->id);
            
            $customer->refresh();
        }

        // Purane tokens delete karo
        $customer->tokens()->delete();

        Event::dispatch('customer.after.login', $customer);

        $profileComplete = $customer->board_id && $customer->grade_id;

        return response([
            'success' => true,
            'user'    => new CustomerResource($customer),
            'message' => $profileComplete ? trans('class_ranker_api::app.shop.customer.accounts.logged-in-success') : 'Please choose your board and grade to continue.',
            'token'   => $customer->createToken($request->device_name, ['role:customer'])->plainTextToken,
        ]);
    }

    public function checkUser(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10',
        ]);

        $exists = $this->customerRepository->where('phone', $request->phone)->exists();

        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found. Please sign up first.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'User found',
        ]);
    }

    public function verifyAccessToken(Request $request)
    {
        \Log::info('verifyAccessToken called', ['request' => $request->all()]);
        $request->validate([
            'access-token' => 'required|string',
            'device_name'  => 'nullable|string',
        ]);

        $accessToken = $request->input('access-token');
        $deviceName  = $request->input('device_name', 'Unknown Device');

        \Log::info('Access token received', ['token' => $accessToken]);
        \Log::info('Auth key used', ['key' => env('MSG91_AUTH_KEY')]);

        // MSG91 se verify karo
        $msg91Response = Http::post('https://control.msg91.com/api/v5/widget/verifyAccessToken', [
            'authkey'      => core()->getConfigData('class_ranker.settings.sms_service.auth_key'),
            'access-token' => $accessToken,
        ]);

            \Log::info('MSG91 response', $msg91Response->json());


        $msg91Data = $msg91Response->json();

        \Log::info('MSG91 verify response', $msg91Data); // debug ke liye

        // MSG91 success check
        if (
            !$msg91Response->successful() ||
            ($msg91Data['type'] ?? '') !== 'success'
        ) {
            return response()->json([
                'success' => false,
                'message' => $msg91Data['message'] ?? 'OTP verification failed',
            ], 401);
        }

        // Phone number extract karo
        // MSG91 "91XXXXXXXXXX" format mein deta hai — strip country code
        $rawPhone = $msg91Data['message'] ?? null;

        if (!$rawPhone) {
            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve phone number',
            ], 422);
        }

        // Country code hatao (91 prefix)
        $phone = preg_replace('/^91/', '', $rawPhone);

        // Customer fetch karo
        $customer = $this->customerRepository->findOneWhere(['phone' => $phone]);

        if (! $customer) {
            return response([
                'success' => false,
                'message' => 'Customer not found.'
            ], 404);
        }

        // Phone verify mark karo
        if (! $customer->is_verified) {
            $this->customerRepository->update(['is_verified' => 1], $customer->id);
            
            $customer->refresh();
        }

        // Purane tokens delete karo
        $customer->tokens()->delete();

        Event::dispatch('customer.after.login', $customer);

        $profileComplete = $customer->board_id && $customer->grade_id;

        return response([
            'success' => true,
            'user'    => new CustomerResource($customer),
            'message' => $profileComplete ? trans('class_ranker_api::app.shop.customer.accounts.logged-in-success') : 'Please choose your board and grade to continue.',
            'token'   => $customer->createToken($request->device_name, ['role:customer'])->plainTextToken,
        ]);
    }

    /**
     * Login the customer.
     */
    public function login(Request $request): Response
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! EnsureFrontendRequestsAreStateful::fromFrontend($request)) {
            $request->validate([
                'device_name' => 'required',
            ]);

            $customer = $this->customerRepository->where('email', $request->email)->first();

            if (! $customer || ! Hash::check($request->password, $customer->password)) {
                throw ValidationException::withMessages([
                    'email' => trans('class_ranker_api::app.shop.customer.accounts.error.credential-error'),
                ]);
            }

            /**
             * Preventing multiple token creation.
             */
            $customer->tokens()->delete();

            /**
             * Event passed to prepare cart after login.
             */
            Event::dispatch('customer.after.login', $customer);

            $profileComplete = $customer->board_id && $customer->grade_id;

            return response([
                'success' => true,
                'message' => $profileComplete ? trans('class_ranker_api::app.shop.customer.accounts.logged-in-success') : 'Please choose your board and grade to continue.',
                'user'    => new CustomerResource($customer),
                'token'   => $customer->createToken($request->device_name, ['role:customer'])->plainTextToken,
            ]);
        }

        if (Auth::attempt($request->only(['email', 'password']))) {
            $request->session()->regenerate();

            return response([
                'success' => true,
                'user'    => new CustomerResource($this->resolveShopUser($request)),
                'message' => trans('class_ranker_api::app.shop.customer.accounts.logged-in-success'),
            ]);
        }

        return response([
            'success' => false,
            'message' => trans('class_ranker_api::app.shop.customer.accounts.error.invalid'),
        ], 401);
    }

    /**
     * Get details for current logged in customer.
     */
    public function get(Request $request): Response
    {
        $customer = $this->resolveShopUser($request);

        return response([
            'success' => true,
            'user' => new CustomerResource($customer),
        ]);
    }

    /**
     * Update the customer.
     */
    public function updateProfile(Request $request): Response
    {
        $customer = $this->resolveShopUser($request);

        $request->validate([
            'first_name'    => ['required'],
            'last_name'     => ['required'],
            'gender'        => 'required|in:Other,Male,Female',
            'date_of_birth' => 'date|before:today',
            'email'         => 'email|unique:customers,email,'.$customer->id,
            'image'         => 'array',
            'image.*'       => 'mimes:bmp,jpeg,jpg,png,webp',
            'phone'         => ['required', new PhoneNumber, 'unique:customers,phone,'.$customer->id],
        ]);

        $data = $request->all();

        if (
            core()->getCurrentChannel()->theme === 'default'
            && ! isset($data['image'])
        ) {
            $data['image']['image_0'] = '';
        }

        Event::dispatch('customer.update.before');

        if ($customer = $this->customerRepository->update($data, $customer->id)) {
            Event::dispatch('customer.update.after', $customer);

            if ($request->hasFile('image')) {
                $this->customerRepository->uploadImages($data, $customer);
            } elseif (isset($data['image'])) {
                if (! empty($data['image'])) {
                    Storage::delete((string) $customer->image);
                }

                $customer->image = null;
                $customer->save();
            }

            return response([
                'success' => true,
                'user'    => new CustomerResource($customer),
                'message' => trans('class_ranker_api::app.shop.customer.accounts.update-success'),
            ]);
        }

        return response([
            'success' => false,
            'message' => trans('class_ranker_api::app.shop.customer.accounts.error.update-failed')
        ]);
    }

    public function updateAddress(Request $request)
    {
        $customer = $this->resolveShopUser($request);

        $address = $customer->addresses()->first();

        $data = $request->only([
            'first_name', 'last_name', 'email', 'phone',
            'gender', 'address', 'city', 'state', 'country', 'postcode'
        ]);

        if ($address) {
            $address->update($data);
        } else {
            $customer->addresses()->create([
                ...$data,
                'default_address' => 1,
            ]);
        }

        return response([
            'success' => true,
            'message' => 'Address updated successfully',
            'user'    => new CustomerResource($customer->fresh()),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $customer = $this->resolveShopUser($request);

        $request->validate([
            'password'              => 'required|min:8',
            'password_confirmation' => 'required|same:password',
        ]);

        $customer->update([
            'password' => bcrypt($request->password),
        ]);

        Event::dispatch('customer.password.update.after', $customer);

        return response([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }

    public function updateBoardClass(Request $request): Response
    {
        $customer = $this->resolveShopUser($request);

        $request->validate([
            'board_id'  => ['required', 'exists:boards,id'],
            'grade_id'  => ['required', 'exists:grades,id'],
        ]);

        if ($customer = $this->customerRepository->update([
            'board_id' => $request->input('board_id'),
            'grade_id' => $request->input('grade_id'),
        ], $customer->id)) {
            return response([
                'success' => true,
                'user'    => new CustomerResource($customer),
                'message' => trans('class_ranker_api::app.shop.customer.accounts.update-success'),
            ]);
        }

        return response([
            'success' => false,
            'message' => trans('class_ranker_api::app.shop.customer.accounts.error.update-failed')
        ]);
    }

    /**
     * Logout the customer.
     */
    public function logout(Request $request): Response
    {
        $customer = $this->resolveShopUser($request);

        ! EnsureFrontendRequestsAreStateful::fromFrontend($request)
            ? $customer->tokens()->delete()
            : auth()->guard('customer')->logout();

        Event::dispatch('customer.after.logout', $customer->id);

        return response([
            'success' => false,
            'message' => trans('class_ranker_api::app.shop.customer.accounts.logged-out-success'),
        ]);
    }

    /**
     * Get current authenticated customer.
     */
    public function me(Request $request): Response
    {
        $customer = $this->resolveShopUser($request);

        if (!$customer) {
            return response(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        return response([
            'success' => true,
            'user'    => new CustomerResource($customer),
        ]);
    }
}
