<?php

namespace CustomFeature\ClassRanker\Http\Controllers\Customers;

use CustomFeature\ClassRanker\Repositories\PlanRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Customers\CustomerController as AdminCustomerController;
use Webkul\Admin\Http\Requests\MassUpdateRequest;

class CustomerController extends AdminCustomerController
{
    /**
     * To mass update the customer.
     */
    public function massAssignPlan(MassUpdateRequest $request): JsonResponse
    {
        $customerIds = $request->input('indices');
        $planId      = $request->input('value');

        $planRepository = app(PlanRepository::class);

        $plan = $planRepository->findOrFail($planId);

        $customers = $this->customerRepository->findWhereIn('id', $customerIds);

        foreach ($customers as $customer) {
            Event::dispatch('customer.plan.assign.before', $customer->id);

            \DB::table('customer_plans')
                ->where('customer_id', $customer->id)
                ->where('status', 'active')
                ->update([
                    'status'     => 'cancelled',
                    'updated_at' => now(),
                ]);

            $startsAt  = now();
            $expiresAt = match ($plan->duration_type) {
                'days'   => $startsAt->copy()->addDays($plan->duration_value),
                'months' => $startsAt->copy()->addMonths($plan->duration_value),
                'years'  => $startsAt->copy()->addYears($plan->duration_value),
                default => null,
            };

            \DB::table('customer_plans')->insert([
                'customer_id' => $customer->id,
                'plan_id'     => $plan->id,
                'amount_paid' => 0,
                'payu_txnid'  => 'CR_' . $customer->id . '_' . $plan->id . '_' . time(),
                'currency'    => core()->getCurrentCurrencyCode() ?? 'INR',
                'status'      => 'active',
                'starts_at'   => $startsAt,
                'expires_at'  => $expiresAt,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            Event::dispatch('customer.plan.assign.after', $customer);
        }

        return new JsonResponse([
            'message' => 'Plan assigned & activated successfully.',
        ]);
    }
}
