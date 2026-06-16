<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTenantRequest;
use App\Http\Requests\Admin\UpdateTenantRequest;
use App\Models\Tenant;
use App\Services\Admin\TenantService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(private readonly TenantService $tenantService)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.tenants.index', [
            'tenants' => $this->tenantService->paginate($request->only(['search', 'status', 'business_type'])),
            'statuses' => ['active', 'trial', 'suspended', 'archived'],
            'businessTypes' => ['Gym', 'Yoga', 'Turf'],
        ]);
    }

    public function create(): View
    {
        return view('admin.tenants.create', $this->tenantService->getCreationMeta());
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $tenant = $this->tenantService->create(
            $request->validated(),
            $request->boolean('trial_enabled'),
        );

        return redirect()->route('admin.tenants.show', $tenant)->with('status', 'Tenant created successfully. Owner login is ready.');
    }

    public function show(Tenant $tenant): View
    {
        return view('admin.tenants.show', [
            'tenant' => $this->tenantService->getDetails($tenant),
        ]);
    }

    public function edit(Tenant $tenant): View
    {
        return view('admin.tenants.edit', $this->tenantService->getEditMeta($tenant));
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->tenantService->update($tenant, $request->validated());

        return redirect()->route('admin.tenants.show', $tenant)->with('status', 'Tenant updated successfully.');
    }

    public function deletePage(Tenant $tenant): View
    {
        return view('admin.tenants.delete', [
            'tenant' => $tenant,
        ]);
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenantName = $tenant->gym_name;
        $this->tenantService->delete($tenant);

        return redirect()->route('admin.tenants.index')->with('status', "{$tenantName} deleted successfully.");
    }
}
