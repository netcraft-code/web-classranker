<?php

namespace CustomFeature\ClassRanker\Http\Controllers\Plan;

use CustomFeature\ClassRanker\DataGrids\PlanDataGrid;
use CustomFeature\ClassRanker\Repositories\PlanRepository;
use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;

class PlanController extends Controller
{
    public function __construct(
        protected PlanRepository $planRepository
    ) {}
 
    // ─── Index ────────────────────────────────────────────────────────────────
 
    public function index()
    {
        if (request()->ajax()) {
            return app(PlanDataGrid::class)->toJson();
        }
 
        return view('class_ranker::plans.index');
    }
 
    // ─── Create ───────────────────────────────────────────────────────────────
 
    public function create()
    {
        return view('class_ranker::plans.create');
    }
 
    // ─── Store ────────────────────────────────────────────────────────────────
 
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:191',
            'code'           => 'required|string|max:191|unique:plans,code',
            'price'          => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'duration_value' => 'required|integer|min:1',
            'duration_type'  => 'required|in:days,months,years',
            'features'       => 'nullable|array',
            'features.*'     => 'string|max:255',
            'sort_order'     => 'nullable|integer|min:0',
            'status'         => 'nullable|boolean',
            'is_popular'     => 'nullable|boolean',
            'avatar'         => 'nullable|image|max:2048',
        ]);
 
        $data = $request->only([
            'name', 'code', 'description', 'price', 'discount_price',
            'duration_value', 'duration_type', 'features',
            'sort_order', 'is_popular',
        ]);
 
        $data['status']     = $request->boolean('status');
        $data['is_popular'] = $request->boolean('is_popular');
        $data['features']   = $request->input('features', []);
 
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('plans', 'public');
        }
 
        $this->planRepository->create($data);
 
        session()->flash('success', 'Plan created successfully.');
 
        return redirect()->route('admin.plans.index');
    }
 
    // ─── Edit ─────────────────────────────────────────────────────────────────
 
    public function edit(int $id)
    {
        $plan = $this->planRepository->findOrFail($id);
 
        return view('class_ranker::plans.edit', compact('plan'));
    }
 
    // ─── Update ───────────────────────────────────────────────────────────────
 
    public function update(Request $request, int $id)
    {
        $plan = $this->planRepository->findOrFail($id);
 
        $request->validate([
            'name'           => 'required|string|max:191',
            'code'           => 'required|string|max:191|unique:plans,code,' . $id,
            'price'          => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'duration_value' => 'required|integer|min:1',
            'duration_type'  => 'required|in:days,months,years',
            'features'       => 'nullable|array',
            'features.*'     => 'string|max:255',
            'sort_order'     => 'nullable|integer|min:0',
            'avatar'         => 'nullable|image|max:2048',
        ]);
 
        $data = $request->only([
            'name', 'code', 'description', 'price', 'discount_price',
            'duration_value', 'duration_type', 'sort_order',
        ]);
 
        $data['status']     = $request->boolean('status');
        $data['is_popular'] = $request->boolean('is_popular');
        $data['features']   = $request->input('features', []);
 
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($plan->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($plan->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('plans', 'public');
        }
 
        $this->planRepository->update($data, $id);
 
        session()->flash('success', 'Plan updated successfully.');
 
        return redirect()->route('admin.plans.index');
    }
 
    // ─── Destroy ──────────────────────────────────────────────────────────────
 
    public function destroy(int $id)
    {
        try {
            $plan = $this->planRepository->findOrFail($id);
 
            if ($plan->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($plan->avatar);
            }
 
            $this->planRepository->delete($id);
 
            return response()->json(['message' => 'Plan deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}