<?php

namespace App\Services\Tenant;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    // â”€â”€ Access control â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function canAdd(): bool
    {
        return in_array(Auth::user()->role, ['tenant_owner', 'accountant', 'branch_manager', 'branch_admin']);
    }

    public function canEdit(Expense $expense): bool
    {
        $user = Auth::user();
        if (in_array($user->role, ['tenant_owner', 'accountant'])) {
            return true;
        }
        if (in_array($user->role, ['branch_manager', 'branch_admin'])) {
            $staff = Staff::where('user_id', $user->id)->where('tenant_id', $expense->tenant_id)->first();
            return $staff?->branch_id === $expense->branch_id;
        }
        return false;
    }

    public function canDelete(): bool
    {
        return in_array(Auth::user()->role, ['tenant_owner', 'accountant']);
    }

    public function canApprove(): bool
    {
        return Auth::user()->role === 'tenant_owner';
    }

    private function scopedBranchId(int $tenantId): ?int
    {
        $user = Auth::user();
        if (in_array($user->role, ['branch_manager', 'branch_admin'])) {
            return Staff::where('user_id', $user->id)->where('tenant_id', $tenantId)->value('branch_id');
        }
        return null;
    }

    // â”€â”€ List â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function list(Request $request, int $tenantId): array
    {
        $query = Expense::with(['branch', 'createdBy'])
            ->where('expenses.tenant_id', $tenantId);

        $scopedBranch = $this->scopedBranchId($tenantId);
        if ($scopedBranch) {
            $query->where('expenses.branch_id', $scopedBranch);
        } elseif ($request->branch_id) {
            $query->where('expenses.branch_id', $request->branch_id);
        }

        if ($request->category) {
            $query->where('expenses.category', $request->category);
        }

        if ($request->method) {
            $query->where('expenses.method', $request->method);
        }

        if ($request->status) {
            $query->where('expenses.status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('expenses.date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('expenses.date', '<=', $request->date_to);
        }

        if ($request->search) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s): void {
                $q->where('expenses.description', 'ilike', $s)
                    ->orWhere('expenses.vendor', 'ilike', $s)
                    ->orWhere('expenses.reference', 'ilike', $s);
            });
        }

        $expenses = $query->orderByDesc('expenses.date')
            ->orderByDesc('expenses.id')
            ->paginate(25)
            ->withQueryString();

        $branches    = Branch::forTenant($tenantId)->active()->orderBy('name')->get();
        $summary     = $this->monthlySummary($tenantId, $scopedBranch ?? $request->branch_id);

        return compact('expenses', 'branches', 'summary');
    }

    // â”€â”€ Monthly summary â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function monthlySummary(int $tenantId, ?int $branchId = null): array
    {
        $thisMonth  = now()->startOfMonth();
        $lastMonth  = now()->subMonth()->startOfMonth();

        $base = fn ($start, $end) => Expense::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<', $end);

        $thisTotal = (clone $base($thisMonth, now()->addDay()))->sum('amount_paise');
        $lastTotal = (clone $base($lastMonth, $thisMonth))->sum('amount_paise');

        $byCategory = (clone $base($thisMonth, now()->addDay()))
            ->select('category', DB::raw('SUM(amount_paise) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'category' => $r->category,
                'total'    => $r->total,
                'pct'      => $thisTotal > 0 ? round(($r->total / $thisTotal) * 100) : 0,
            ])
            ->all();

        $vsLastPct = $lastTotal > 0
            ? round((($thisTotal - $lastTotal) / $lastTotal) * 100)
            : null;

        return compact('thisTotal', 'lastTotal', 'byCategory', 'vsLastPct');
    }

    // â”€â”€ Form data â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function formData(int $tenantId): array
    {
        $branches = Branch::forTenant($tenantId)->active()->orderBy('name')->get();
        $staffList = Staff::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return compact('branches', 'staffList');
    }

    // â”€â”€ Store â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function store(Request $request, int $tenantId): Expense
    {
        $user  = Auth::user();
        $staff = Staff::where('user_id', $user->id)->where('tenant_id', $tenantId)->first();

        $amountPaise = (int) round($request->amount * 100);
        $gstPaise    = (int) round(($request->gst ?? 0) * 100);

        // Auto-pending for large amounts if not owner/accountant
        $needsApproval = $amountPaise > 1000000  // Rs. 10,000
            && ! in_array($user->role, ['tenant_owner', 'accountant']);
        $status = $needsApproval ? 'pending' : 'approved';

        return Expense::create([
            'tenant_id'      => $tenantId,
            'branch_id'      => $request->branch_id,
            'date'           => $request->date,
            'category'       => $request->category,
            'sub_category'   => $request->sub_category ?: null,
            'description'    => $request->description,
            'amount_paise'   => $amountPaise,
            'gst_paise'      => $gstPaise,
            'method'         => $request->method,
            'vendor'         => $request->vendor ?: null,
            'reference'      => $request->reference ?: null,
            'receipt_url'    => $request->receipt_url ?: null,
            'notes'          => $request->notes ?: null,
            'status'         => $status,
            'is_recurring'   => (bool) $request->is_recurring,
            'recurrence_freq' => $request->is_recurring ? $request->recurrence_freq : null,
            'recurrence_end' => $request->is_recurring ? ($request->recurrence_end ?: null) : null,
            'staff_id'       => $request->category === 'salaries' ? ($request->staff_id ?: null) : null,
            'salary_month'   => $request->category === 'salaries' ? ($request->salary_month ?: null) : null,
            'created_by'     => $staff?->id,
        ]);
    }

    // â”€â”€ Update â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function update(Request $request, Expense $expense): void
    {
        $amountPaise = (int) round($request->amount * 100);
        $gstPaise    = (int) round(($request->gst ?? 0) * 100);

        $expense->update([
            'branch_id'      => $request->branch_id,
            'date'           => $request->date,
            'category'       => $request->category,
            'sub_category'   => $request->sub_category ?: null,
            'description'    => $request->description,
            'amount_paise'   => $amountPaise,
            'gst_paise'      => $gstPaise,
            'method'         => $request->method,
            'vendor'         => $request->vendor ?: null,
            'reference'      => $request->reference ?: null,
            'receipt_url'    => $request->receipt_url ?: null,
            'notes'          => $request->notes ?: null,
            'is_recurring'   => (bool) $request->is_recurring,
            'recurrence_freq' => $request->is_recurring ? $request->recurrence_freq : null,
            'recurrence_end' => $request->is_recurring ? ($request->recurrence_end ?: null) : null,
            'staff_id'       => $request->category === 'salaries' ? ($request->staff_id ?: null) : null,
            'salary_month'   => $request->category === 'salaries' ? ($request->salary_month ?: null) : null,
        ]);
    }

    // â”€â”€ Approve / reject â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function approve(Expense $expense, int $tenantId): void
    {
        $staff = Staff::where('user_id', Auth::id())->where('tenant_id', $tenantId)->first();
        $expense->update([
            'status'      => 'approved',
            'approved_by' => $staff?->id,
            'rejection_reason' => null,
        ]);
    }

    public function reject(Expense $expense, Request $request): void
    {
        $expense->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
    }

    // â”€â”€ CSV export â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function exportCsv(Request $request, int $tenantId): string
    {
        $query = Expense::with('branch')
            ->where('tenant_id', $tenantId);

        $scopedBranch = $this->scopedBranchId($tenantId);
        if ($scopedBranch) {
            $query->where('branch_id', $scopedBranch);
        } elseif ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $rows = $query->orderByDesc('date')->get();

        $lines   = [];
        $lines[] = implode(',', ['Date', 'Category', 'Sub-category', 'Description', 'Amount (Rs)', 'GST Paid (Rs)', 'Method', 'Vendor', 'Reference', 'Branch', 'Status']);

        foreach ($rows as $e) {
            $lines[] = implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', [
                $e->date->format('d-m-Y'),
                $e->category,
                $e->sub_category ?? '',
                $e->description,
                number_format($e->amount_paise / 100, 2),
                number_format($e->gst_paise / 100, 2),
                $e->method,
                $e->vendor ?? '',
                $e->reference ?? '',
                $e->branch?->name ?? '',
                $e->status,
            ]));
        }

        return implode("\n", $lines);
    }
}

