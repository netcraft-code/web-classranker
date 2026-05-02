<?php

namespace CustomFeature\ClassRanker\Http\Controllers\Plan;

use CustomFeature\ClassRanker\DataGrids\Customers\CustomerPlanDataGrid;
use Webkul\Admin\Http\Controllers\Controller;

class CustomerPlanController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────
 
    public function index()
    {
        if (request()->ajax()) {
            return app(CustomerPlanDataGrid::class)->toJson();
        }
 
        return view('class_ranker::customer-plans.index');
    }

    public function cancelPlan(int $id)
    {
        $customerPlan = \DB::table('customer_plans')->where('id', $id)->first();

        if (! $customerPlan) {
            return response()->json([
                'message' => 'Plan not found.'
            ], 404);
        }

        // 👉 Already cancelled / expired check
        if (in_array($customerPlan->status, ['cancelled', 'expired'])) {
            return response()->json([
                'message' => 'Plan already ' . $customerPlan->status . '.'
            ], 400);
        }

        // 🔹 Event before
        event('customer.plan.cancel.before', $customerPlan);

        // 🔹 Update status
        \DB::table('customer_plans')
            ->where('id', $id)
            ->update([
                'status'     => 'cancelled',
                'updated_at' => now(),
            ]);

        // 🔹 Event after
        event('customer.plan.cancel.after', $id);

        return response()->json([
            'message' => 'Plan cancelled successfully.'
        ]);
    }
}