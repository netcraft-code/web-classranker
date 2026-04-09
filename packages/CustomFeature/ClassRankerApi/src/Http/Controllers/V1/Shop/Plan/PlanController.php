<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Plan;

use Carbon\Carbon;
use CustomFeature\ClassRanker\Models\CustomerPlan;
use CustomFeature\ClassRanker\Repositories\PlanRepository;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\ShopController;
use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\Plan\PlanResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends ShopController
{
    /**
     * Is resource authorized.
     */
    public function isAuthorized(): bool
    {
        return false;
    }

    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return PlanRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return PlanResource::class;
    }

    // ─── POST /plans/{id}/create-order ───────────────────────────────────────
 
    public function createOrder(Request $request, int $id): JsonResponse
    {
        $plan = $this->getRepositoryInstance()->findOrFail($id);
 
        if (! $plan->status) {
            return response()->json([
                'success' => false,
                'message' => 'This plan is not available.',
            ], 422);
        }
 
        $customer = $this->resolveShopUser($request);
 
        // Block if already has an active plan
        $activePlan = CustomerPlan::where('customer_id', $customer->id)
            ->active()
            ->latest()
            ->first();
 
        if ($activePlan) {
            return response()->json([
                'success'     => false,
                'message'     => 'You already have an active plan expiring on ' . $activePlan->expires_at->format('d M Y') . '.',
                'active_plan' => $this->formatCustomerPlan($activePlan),
            ], 422);
        }
 
        $effectivePrice = (float) ($plan->discount_price ?? $plan->price);
        $txnId          = 'CR_' . $customer->id . '_' . $plan->id . '_' . time();
        $key            = config('services.payu.key');
        $salt           = config('services.payu.salt');
        $amount         = number_format($effectivePrice, 2, '.', '');
        $productInfo    = $plan->name;
        $firstName      = $customer->name;
        $email          = $customer->email;
        $phone          = $customer->phone ?? '';
 
        // surl / furl — PayU will redirect iframe to these after payment
        $baseUrl = config('services.payu.callback_base_url', config('app.url'));
        $surl    = $baseUrl . '/api/v1/payu/success';
        $furl    = $baseUrl . '/api/v1/payu/failure';
 
        // Hash: sha512(key|txnid|amount|productinfo|firstname|email|udf1-5||||||salt)
        $hashString = implode('|', [
            $key, $txnId, $amount, $productInfo,
            $firstName, $email,
            '', '', '', '', '',
            '', '', '', '', '',
            $salt,
        ]);
        $hash = hash('sha512', $hashString);
 
        // Create pending customer_plan record
        CustomerPlan::create([
            'customer_id'        => $customer->id,
            'plan_id'            => $plan->id,
            'payu_txnid'  => $txnId,
            'amount_paid'        => $effectivePrice,
            'currency'           => 'INR',
            'status'             => 'pending',
        ]);
 
        return response()->json([
            'success' => true,
            'data'    => [
                'payu_url'    => config('services.payu.env', 'test') === 'production'
                                    ? 'https://secure.payu.in/_payment'
                                    : 'https://test.payu.in/_payment',
                'key'         => $key,
                'txnid'       => $txnId,
                'amount'      => $amount,
                'productinfo' => $productInfo,
                'firstname'   => $firstName,
                'email'       => $email,
                'phone'       => $phone,
                'surl'        => $surl,
                'furl'        => $furl,
                'hash'        => $hash,
            ],
        ]);
    }
 
    // ─── POST /plans/verify-payment ──────────────────────────────────────────
 
    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'txnid'    => 'required|string',
            'mihpayid' => 'required|string',
            'status'   => 'required|string',
            'hash'     => 'required|string',
        ]);
 
        if (strtolower($request->status) !== 'success') {
            return response()->json([
                'success' => false,
                'message' => 'Payment was not successful.',
            ], 422);
        }
 
        // Reverse hash verification
        $salt        = config('services.payu.salt');
        $key         = config('services.payu.key');
        $reverseHash = hash('sha512', implode('|', [
            $salt,
            $request->status,
            '', '', '', '', '',
            $request->input('udf5', ''),
            $request->input('udf4', ''),
            $request->input('udf3', ''),
            $request->input('udf2', ''),
            $request->input('udf1', ''),
            $request->input('email', ''),
            $request->input('firstname', ''),
            $request->input('productinfo', ''),
            $request->input('amount', ''),
            $request->txnid,
            $key,
        ]));
 
        if ($reverseHash !== $request->hash) {
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Hash mismatch.',
            ], 422);
        }
 
        $customerPlan = CustomerPlan::where('payu_txnid', $request->txnid)
            ->where('customer_id', $this->resolveShopUser($request)->id)
            ->where('status', 'pending')
            ->firstOrFail();
 
        $plan      = $customerPlan->plan;
        $startsAt  = Carbon::now();
        $expiresAt = match ($plan->duration_type) {
            'days'   => $startsAt->copy()->addDays($plan->duration_value),
            'months' => $startsAt->copy()->addMonths($plan->duration_value),
            'years'  => $startsAt->copy()->addYears($plan->duration_value),
        };
 
        $customerPlan->update([
            'payu_mihpayid' => $request->mihpayid,
            'razorpay_signature'  => $request->hash,
            'status'              => 'active',
            'starts_at'           => $startsAt,
            'expires_at'          => $expiresAt,
        ]);
 
        return response()->json([
            'success' => true,
            'message' => 'Plan activated successfully!',
            'data'    => $this->formatCustomerPlan($customerPlan->fresh('plan')),
        ]);
    }
 
    // ─── GET /plans/my-plans ─────────────────────────────────────────────────
 
    public function myPlans(Request $request): JsonResponse
    {
        $customerPlans = CustomerPlan::with('plan')
            ->where('customer_id', $this->resolveShopUser($request)->id)
            ->latest()
            ->get();
 
        $activePlan = $customerPlans->first(fn($cp) => $cp->is_active);
 
        return response()->json([
            'success' => true,
            'data'    => [
                'active_plan' => $activePlan ? $this->formatCustomerPlan($activePlan) : null,
                'history'     => $customerPlans->map(fn($cp) => $this->formatCustomerPlan($cp)),
            ],
        ]);
    }
 
    // ─── Formatters ───────────────────────────────────────────────────────────
 
    private function formatPlan($plan): array
    {
        return [
            'id'               => $plan->id,
            'name'             => $plan->name,
            'code'             => $plan->code,
            'description'      => $plan->description,
            'price'            => (float) $plan->price,
            'discount_price'   => $plan->discount_price ? (float) $plan->discount_price : null,
            'effective_price'  => (float) $plan->effective_price,
            'discount_percent' => $plan->discount_percent,
            'duration_value'   => $plan->duration_value,
            'duration_type'    => $plan->duration_type,
            'duration_label'   => $plan->duration_label,
            'features'         => $plan->features ?? [],
            'is_popular'       => (bool) $plan->is_popular,
            'avatar_url'       => $plan->avatar_url,
            'sort_order'       => $plan->sort_order,
        ];
    }
 
    private function formatCustomerPlan(CustomerPlan $customerPlan): array
    {
        return [
            'id'             => $customerPlan->id,
            'plan'           => $customerPlan->plan ? $this->formatPlan($customerPlan->plan) : null,
            'status'         => $customerPlan->status,
            'is_active'      => $customerPlan->is_active,
            'days_remaining' => $customerPlan->days_remaining,
            'amount_paid'    => (float) $customerPlan->amount_paid,
            'currency'       => $customerPlan->currency,
            'payu_txnid'    => $customerPlan->payu_txnid,
            'payu_mihpayid' => $customerPlan->payu_mihpayid,
            'starts_at'      => $customerPlan->starts_at?->toISOString(),
            'expires_at'     => $customerPlan->expires_at?->toISOString(),
            'purchased_at'   => $customerPlan->created_at->toISOString(),
        ];
    }
}
