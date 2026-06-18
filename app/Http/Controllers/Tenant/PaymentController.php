<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Tenant\PaymentService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $svc) {}

    private function tenantId(): int
    {
        return request()->user()->tenant->id;
    }

    // ── Collect ───────────────────────────────────────────────────────────────

    public function collect(): View
    {
        abort_unless($this->svc->canCollect(), 403);

        $data = $this->svc->collectPage($this->tenantId());
        $data['selectedBranchId'] = session('gymos_selected_branch_id');

        return view('tenant.payments.collect', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->svc->canCollect(), 403);

        $request->validate([
            'member_id'    => ['required', 'integer'],
            'branch_id'    => ['required', 'integer'],
            'plan_id'      => ['nullable', 'integer'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'method'       => ['required', 'in:' . implode(',', Payment::METHODS)],
            'reference'    => ['nullable', 'string', 'max:100',
                'required_if:method,upi', 'required_if:method,card',
                'required_if:method,bank', 'required_if:method,cheque'],
            'payment_date' => ['required', 'date'],
            'notes'        => ['nullable', 'string', 'max:500'],
            'apply_gst'    => ['nullable', 'boolean'],
            'gst_rate'     => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $payment = $this->svc->storePayment($request, $this->tenantId());

        return redirect()->route('tenant.payments.receipt', $payment)
            ->with('status', __('payments.flash.collected', ['receipt' => $payment->receipt_number]));
    }

    // ── History ───────────────────────────────────────────────────────────────

    public function history(Request $request): View
    {
        if (!$request->filled('branch_id') && $id = session('gymos_selected_branch_id')) {
            $request->merge(['branch_id' => $id]);
        }
        $data = $this->svc->history($request, $this->tenantId());

        return view('tenant.payments.history', $data);
    }

    // ── Dues ──────────────────────────────────────────────────────────────────

    public function dues(Request $request): View
    {
        if (!$request->filled('branch_id') && $id = session('gymos_selected_branch_id')) {
            $request->merge(['branch_id' => $id]);
        }
        $data = $this->svc->dues($request, $this->tenantId());

        return view('tenant.payments.dues', $data);
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

    public function receipt(Payment $payment): View
    {
        $data = $this->svc->receiptData($payment, $this->tenantId());

        return view('tenant.payments.receipt', $data);
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
