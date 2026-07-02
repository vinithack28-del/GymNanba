<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use App\Services\Admin\PlanService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function __construct(private readonly PlanService $planService)
    {
    }

    public function index()
    {
        return Inertia::render('Admin/Plans/Index', [
            'plans' => $this->planService->all(),
        ]);
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $this->planService->create($request->validated());

        return redirect()->route('admin.plans.index')->with('status', 'Plan added successfully.');
    }

    public function edit(Plan $plan)
    {
        return Inertia::render('Admin/Plans/Edit', [
            'plan' => $plan,
        ]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $this->planService->update($plan, $request->validated());

        return redirect()->route('admin.plans.index')->with('status', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $planName = $plan->name;
        $this->planService->delete($plan);

        return redirect()->route('admin.plans.index')->with('status', "{$planName} deleted successfully.");
    }
}
