<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Services\Admin\PlanService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PlanController extends Controller
{
    public function __construct(private readonly PlanService $planService)
    {
    }

    public function index(): View
    {
        return view('admin.plans.index', [
            'plans' => $this->planService->all(),
        ]);
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $this->planService->create($request->validated());

        return redirect()->route('admin.plans.index')->with('status', 'Plan added successfully.');
    }
}
