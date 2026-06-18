<?php

namespace App\Services\Tenant;

use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    // ── Access control ────────────────────────────────────────────────────────

    public function canCreate(): bool
    {
        return in_array(Auth::user()->role, ['tenant_owner', 'accountant', 'branch_manager', 'branch_admin']);
    }

    public function canVoid(): bool
    {
        return in_array(Auth::user()->role, ['tenant_owner', 'accountant']);
    }

    private function branchScope(int $tenantId): ?int
    {
        $user = Auth::user();
        if (in_array($user->role, ['branch_manager', 'branch_admin'])) {
            return Staff::where('user_id', $user->id)->where('tenant_id', $tenantId)->value('branch_id');
        }
        return null;
    }

    // ── List ──────────────────────────────────────────────────────────────────

    public function list(Request $request, int $tenantId): array
    {
        $query = Invoice::with(['member', 'branch'])
            ->where('invoices.tenant_id', $tenantId);

        $scopedBranch = $this->branchScope($tenantId);
        if ($scopedBranch) {
            $query->where('invoices.branch_id', $scopedBranch);
        }

        if ($request->branch_id) {
            $query->where('invoices.branch_id', $request->branch_id);
        }

        if ($request->status) {
            $query->where('invoices.status', $request->status);
        }

        if ($request->search) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s): void {
                $q->where('invoices.invoice_number', 'ilike', $s)
                    ->orWhereHas('member', fn ($m) => $m->where('name', 'ilike', $s)
                        ->orWhere('phone', 'ilike', $s)
                        ->orWhere('member_code', 'ilike', $s));
            });
        }

        if ($request->date_from) {
            $query->whereDate('invoices.invoice_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('invoices.invoice_date', '<=', $request->date_to);
        }

        if ($request->amount_min) {
            $query->where('invoices.total_paise', '>=', (int) round($request->amount_min * 100));
        }

        if ($request->amount_max) {
            $query->where('invoices.total_paise', '<=', (int) round($request->amount_max * 100));
        }

        $invoices = $query->orderByDesc('invoices.invoice_date')
            ->orderByDesc('invoices.id')
            ->paginate(25)
            ->withQueryString();

        $branches = Branch::forTenant($tenantId)->active()->orderBy('name')->get();

        return compact('invoices', 'branches');
    }

    // ── Create page data ──────────────────────────────────────────────────────

    public function createPageData(int $tenantId): array
    {
        $branches = Branch::forTenant($tenantId)->active()->orderBy('name')->get();
        return compact('branches');
    }

    // ── Store manual invoice ──────────────────────────────────────────────────

    public function store(Request $request, int $tenantId): Invoice
    {
        $user  = Auth::user();
        $staff = Staff::where('user_id', $user->id)->where('tenant_id', $tenantId)->first();

        $lineItems  = $request->input('line_items', []);
        $totals     = $this->computeTotals($lineItems);

        return DB::transaction(function () use ($request, $tenantId, $staff, $lineItems, $totals) {
            $tenant = Tenant::findOrFail($tenantId);
            $number = $this->generateInvoiceNumber($tenant);

            return Invoice::create([
                'tenant_id'      => $tenantId,
                'member_id'      => $request->member_id,
                'payment_id'     => null,
                'branch_id'      => $request->branch_id ?: $staff?->branch_id,
                'invoice_number' => $number,
                'invoice_date'   => $request->invoice_date ?: today()->toDateString(),
                'due_date'       => $request->due_date ?: null,
                'line_items'     => $lineItems,
                'subtotal_paise' => $totals['subtotal'],
                'gst_paise'      => $totals['gst'],
                'total_paise'    => $totals['total'],
                'status'         => 'unpaid',
                'notes'          => $request->notes ?: null,
                'created_by'     => $staff?->id,
            ]);
        });
    }

    // ── Auto-create from payment ──────────────────────────────────────────────

    public function createFromPayment(Payment $payment, Tenant $tenant): Invoice
    {
        $planName = $payment->plan?->name ?? 'Membership Payment';

        $lineItems = [[
            'description' => $planName,
            'qty'         => 1,
            'rate_paise'  => $payment->amount_paise,
            'gst_rate'    => $payment->gst_paise > 0
                ? round(($payment->gst_paise / $payment->amount_paise) * 100)
                : 0,
            'amount_paise' => $payment->amount_paise,
        ]];

        $number = $this->generateInvoiceNumber($tenant);

        return Invoice::create([
            'tenant_id'      => $tenant->id,
            'member_id'      => $payment->member_id,
            'payment_id'     => $payment->id,
            'branch_id'      => $payment->branch_id,
            'invoice_number' => $number,
            'invoice_date'   => $payment->payment_date->toDateString(),
            'due_date'       => null,
            'line_items'     => $lineItems,
            'subtotal_paise' => $payment->amount_paise,
            'gst_paise'      => $payment->gst_paise,
            'total_paise'    => $payment->total_paise,
            'status'         => 'paid',
            'notes'          => null,
            'created_by'     => $payment->collected_by,
        ]);
    }

    // ── Void ──────────────────────────────────────────────────────────────────

    public function void(Invoice $invoice, Request $request, int $tenantId): void
    {
        abort_if($invoice->status === 'void', 422, __('invoices.flash.already_voided'));

        $invoice->update([
            'status'      => 'void',
            'voided_at'   => now(),
            'void_reason' => $request->void_reason,
        ]);
    }

    // ── Show / receipt data ───────────────────────────────────────────────────

    public function show(Invoice $invoice, int $tenantId): array
    {
        abort_if($invoice->tenant_id !== $tenantId, 404);
        $invoice->load(['member', 'branch', 'payment', 'createdBy']);
        $tenant = Tenant::findOrFail($tenantId);
        return compact('invoice', 'tenant');
    }

    // ── Member search ─────────────────────────────────────────────────────────

    public function memberSearch(string $term, int $tenantId): array
    {
        return Member::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(function ($q) use ($term): void {
                $q->where('name', 'ilike', '%' . $term . '%')
                    ->orWhere('phone', 'ilike', '%' . $term . '%')
                    ->orWhere('member_code', 'ilike', '%' . $term . '%');
            })
            ->limit(10)
            ->get()
            ->map(fn ($m) => [
                'id'          => $m->id,
                'name'        => $m->name,
                'phone'       => $m->phone,
                'member_code' => $m->member_code,
                'branch_id'   => $m->branch_id,
            ])
            ->all();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function computeTotals(array $lineItems): array
    {
        $subtotal = 0;
        $gst      = 0;

        foreach ($lineItems as $item) {
            $rate   = (int) ($item['rate_paise'] ?? 0);
            $qty    = max(1, (int) ($item['qty'] ?? 1));
            $amount = $rate * $qty;
            $itemGst = (int) round($amount * (($item['gst_rate'] ?? 0) / 100));
            $subtotal += $amount;
            $gst      += $itemGst;
        }

        return [
            'subtotal' => $subtotal,
            'gst'      => $gst,
            'total'    => $subtotal + $gst,
        ];
    }

    private function generateInvoiceNumber(Tenant $tenant): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $tenant->gym_name), 0, 3));
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X');
        }
        $year = now()->year;

        $seq = Invoice::where('tenant_id', $tenant->id)
            ->whereYear('invoice_date', $year)
            ->count() + 1;

        return $prefix . '-' . $year . '-' . str_pad($seq, 6, '0', STR_PAD_LEFT);
    }

    public function amountInWords(int $totalPaise): string
    {
        $rupees = intdiv($totalPaise, 100);
        $paise  = $totalPaise % 100;

        $words = $this->numberToWords($rupees);
        $result = 'Rupees ' . $words;

        if ($paise > 0) {
            $result .= ' and ' . $this->numberToWords($paise) . ' Paise';
        }

        return $result . ' Only';
    }

    private function numberToWords(int $n): string
    {
        if ($n === 0) {
            return 'Zero';
        }

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $result = '';

        if ($n >= 10000000) {
            $result .= $this->numberToWords(intdiv($n, 10000000)) . ' Crore ';
            $n %= 10000000;
        }
        if ($n >= 100000) {
            $result .= $this->numberToWords(intdiv($n, 100000)) . ' Lakh ';
            $n %= 100000;
        }
        if ($n >= 1000) {
            $result .= $this->numberToWords(intdiv($n, 1000)) . ' Thousand ';
            $n %= 1000;
        }
        if ($n >= 100) {
            $result .= $ones[intdiv($n, 100)] . ' Hundred ';
            $n %= 100;
        }
        if ($n >= 20) {
            $result .= $tens[intdiv($n, 10)] . ' ';
            $n %= 10;
        }
        if ($n > 0) {
            $result .= $ones[$n] . ' ';
        }

        return trim($result);
    }
}
