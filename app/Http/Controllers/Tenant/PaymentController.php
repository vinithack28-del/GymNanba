<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Tenant\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $svc) {}

    private function tenantId(): int
    {
        return request()->user()->tenant->id;
    }

    // ── Collect ───────────────────────────────────────────────────────────────

    public function collect(Request $request){
        abort_unless($this->svc->canCollect(), 403);

        $data = $this->svc->collectPage($this->tenantId());
        $data['selectedBranchId'] = session('gymos_selected_branch_id');

        if ($request->filled('member_id')) {
            $member = \App\Models\Member::where('tenant_id', $this->tenantId())
                ->select('id', 'name', 'phone', 'member_code', 'plan_id', 'plan_name', 'balance_paise', 'branch_id')
                ->find($request->integer('member_id'));
            $data['preselectedMember'] = $member;
        }

        return Inertia::render('Tenant/Payments/Collect'$data);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->svc->canCollect(), 403);

        $request->validate([
            'member_id'              => ['required', 'integer'],
            'branch_id'              => ['required', 'integer'],
            'plan_id'                => ['nullable', 'integer'],
            'amount'                 => ['required', 'numeric', 'min:0.01'],
            'splits'                 => ['required', 'array', 'min:1'],
            'splits.*.method'        => ['required', 'in:' . implode(',', Payment::METHODS)],
            'splits.*.amount'        => ['required', 'numeric', 'min:0.01'],
            'splits.*.reference'     => ['nullable', 'string', 'max:100'],
            'payment_date'           => ['required', 'date'],
            'notes'                  => ['nullable', 'string', 'max:500'],
            'is_partial'             => ['nullable', 'boolean'],
            'due_amount'             => ['nullable', 'numeric', 'min:0.01'],
            'due_date'               => ['nullable', 'date', 'after:today'],
        ]);

        $payment = $this->svc->storePayment($request, $this->tenantId());

        return redirect()->route('tenant.payments.receipt', $payment)
            ->with('status', __('payments.flash.collected', ['receipt' => $payment->receipt_number]));
    }

    // ── History ───────────────────────────────────────────────────────────────

    public function history(Request $request){
        if (!$request->filled('branch_id') && $id = session('gymos_selected_branch_id')) {
            $request->merge(['branch_id' => $id]);
        }
        $data = $this->svc->history($request, $this->tenantId());

        return Inertia::render('Tenant/Payments/History'$data);
    }

    // ── Dues ──────────────────────────────────────────────────────────────────

    public function dues(Request $request): RedirectResponse
    {
        $query = array_filter([
            'tab' => 'dues',
            'branch_id' => $request->get('branch_id'),
            'search' => $request->get('search'),
        ], fn ($value) => filled($value));

        return redirect()->route('tenant.payments.history', $query);
    }

    // ── Void ──────────────────────────────────────────────────────────────────

    public function void(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($this->svc->canVoid(), 403);
        abort_if($payment->tenant_id !== $this->tenantId(), 404);

        $request->validate([
            'void_reason' => ['required', 'in:' . implode(',', Payment::VOID_REASONS)],
        ]);

        $this->svc->voidPayment($payment, $request, $this->tenantId());

        return redirect()->route('tenant.payments.history')
            ->with('status', __('payments.flash.voided', ['receipt' => $payment->receipt_number]));
    }

    // ── Receipt ───────────────────────────────────────────────────────────────

    public function receipt(Payment $payment){
        $data = $this->svc->receiptData($payment, $this->tenantId());

        return Inertia::render('Tenant/Payments/Receipt'$data);
    }

    // ── Member search (AJAX) ──────────────────────────────────────────────────

    public function memberSearch(Request $request): JsonResponse
    {
        $term = trim($request->input('q', ''));

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        return response()->json($this->svc->memberSearch($term, $this->tenantId()));
    }
}
