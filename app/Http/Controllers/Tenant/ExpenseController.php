<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Services\Tenant\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function __construct(private readonly ExpenseService $svc) {}

    private function tenantId(): int
    {
        return request()->user()->tenant->id;
    }

    // â”€â”€ List â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function index(Request $request){
        abort_unless($this->svc->canAdd(), 403);
        if (!$request->filled('branch_id') && $id = session('gymos_selected_branch_id')) {
            $request->merge(['branch_id' => $id]);
        }
        $data = $this->svc->list($request, $this->tenantId());
        return Inertia::render('Tenant/Expenses/Index', $data);
    }

    // â”€â”€ Create â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function create(){
        abort_unless($this->svc->canAdd(), 403);
        $data = $this->svc->formData($this->tenantId());
        $data['selectedBranchId'] = session('gymos_selected_branch_id');
        return Inertia::render('Tenant/Expenses/Create', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->svc->canAdd(), 403);

        $request->validate([
            'branch_id'      => ['required', 'integer'],
            'date'           => ['required', 'date', 'after:' . now()->subYear()->toDateString()],
            'category'       => ['required', 'in:' . implode(',', array_keys(Expense::CATEGORIES))],
            'sub_category'   => ['nullable', 'string', 'max:50'],
            'description'    => ['required', 'string', 'min:5', 'max:200'],
            'amount'         => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'gst'            => ['nullable', 'numeric', 'min:0'],
            'method'         => ['required', 'in:' . implode(',', Expense::METHODS)],
            'vendor'         => ['nullable', 'string', 'max:100'],
            'reference'      => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:500'],
            'is_recurring'   => ['nullable', 'boolean'],
            'recurrence_freq' => ['nullable', 'required_if:is_recurring,1', 'in:' . implode(',', Expense::RECURRENCE)],
            'recurrence_end' => ['nullable', 'date'],
            'staff_id'       => ['nullable', 'integer'],
            'salary_month'   => ['nullable', 'string', 'max:7'],
        ]);

        $expense = $this->svc->store($request, $this->tenantId());

        $msg = $expense->status === 'pending'
            ? __('expenses.flash.stored_pending')
            : __('expenses.flash.stored');

        return redirect()->route('tenant.expenses.index')->with('status', $msg);
    }

    // â”€â”€ Edit â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function edit(Expense $expense){
        abort_if($expense->tenant_id !== $this->tenantId(), 404);
        abort_unless($this->svc->canEdit($expense), 403);

        $data = $this->svc->formData($this->tenantId());
        return Inertia::render('Tenant/Expenses/Edit', array_merge($data, compact('expense')));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        abort_if($expense->tenant_id !== $this->tenantId(), 404);
        abort_unless($this->svc->canEdit($expense), 403);

        $request->validate([
            'branch_id'      => ['required', 'integer'],
            'date'           => ['required', 'date', 'after:' . now()->subYear()->toDateString()],
            'category'       => ['required', 'in:' . implode(',', array_keys(Expense::CATEGORIES))],
            'sub_category'   => ['nullable', 'string', 'max:50'],
            'description'    => ['required', 'string', 'min:5', 'max:200'],
            'amount'         => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'gst'            => ['nullable', 'numeric', 'min:0'],
            'method'         => ['required', 'in:' . implode(',', Expense::METHODS)],
            'vendor'         => ['nullable', 'string', 'max:100'],
            'reference'      => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:500'],
            'is_recurring'   => ['nullable', 'boolean'],
            'recurrence_freq' => ['nullable', 'in:' . implode(',', Expense::RECURRENCE)],
            'recurrence_end' => ['nullable', 'date'],
            'staff_id'       => ['nullable', 'integer'],
            'salary_month'   => ['nullable', 'string', 'max:7'],
        ]);

        $this->svc->update($request, $expense);

        return redirect()->route('tenant.expenses.index')
            ->with('status', __('expenses.flash.updated'));
    }

    // â”€â”€ Delete â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function destroy(Expense $expense): RedirectResponse
    {
        abort_if($expense->tenant_id !== $this->tenantId(), 404);
        abort_unless($this->svc->canDelete(), 403);

        $expense->delete();

        return redirect()->route('tenant.expenses.index')
            ->with('status', __('expenses.flash.deleted'));
    }

    // â”€â”€ Approve / Reject â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function approve(Expense $expense): RedirectResponse
    {
        abort_if($expense->tenant_id !== $this->tenantId(), 404);
        abort_unless($this->svc->canApprove(), 403);

        $this->svc->approve($expense, $this->tenantId());

        return redirect()->back()->with('status', __('expenses.flash.approved'));
    }

    public function reject(Request $request, Expense $expense): RedirectResponse
    {
        abort_if($expense->tenant_id !== $this->tenantId(), 404);
        abort_unless($this->svc->canApprove(), 403);

        $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);
        $this->svc->reject($expense, $request);

        return redirect()->back()->with('status', __('expenses.flash.rejected'));
    }

    // â”€â”€ Sub-categories (AJAX) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function subCategories(string $category): JsonResponse
    {
        $subs = Expense::CATEGORIES[$category] ?? [];
        return response()->json($subs);
    }

    // â”€â”€ CSV export â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function export(Request $request): Response
    {
        abort_unless($this->svc->canAdd(), 403);
        $csv = $this->svc->exportCsv($request, $this->tenantId());

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="expenses_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}

