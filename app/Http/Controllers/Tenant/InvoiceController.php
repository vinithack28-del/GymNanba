<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Tenant\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $svc) {}

    private function tenantId(): int
    {
        return request()->user()->tenant->id;
    }

    // ── List ──────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        if (!$request->filled('branch_id') && $id = session('gymos_selected_branch_id')) {
            $request->merge(['branch_id' => $id]);
        }
        $data = $this->svc->list($request, $this->tenantId());
        return view('tenant.invoices.index', $data);
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(): View
    {
        abort_unless($this->svc->canCreate(), 403);
        $data = $this->svc->createPageData($this->tenantId());
        $data['selectedBranchId'] = session('gymos_selected_branch_id');
        return view('tenant.invoices.create', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->svc->canCreate(), 403);

        $request->validate([
            'member_id'           => ['required', 'integer'],
            'invoice_date'        => ['required', 'date'],
            'due_date'            => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'branch_id'           => ['nullable', 'integer'],
            'notes'               => ['nullable', 'string', 'max:1000'],
            'line_items'          => ['required', 'array', 'min:1'],
            'line_items.*.description' => ['required', 'string', 'max:200'],
            'line_items.*.qty'    => ['required', 'integer', 'min:1'],
            'line_items.*.rate_paise' => ['required', 'integer', 'min:1'],
            'line_items.*.gst_rate'   => ['required', 'in:0,5,12,18,28'],
        ]);

        $invoice = $this->svc->store($request, $this->tenantId());

        return redirect()->route('tenant.invoices.show', $invoice)
            ->with('status', __('invoices.flash.created', ['number' => $invoice->invoice_number]));
    }

    // ── Show / print ──────────────────────────────────────────────────────────

    public function show(Invoice $invoice): View
    {
        $data = $this->svc->show($invoice, $this->tenantId());
        return view('tenant.invoices.show', $data);
    }

    // ── Void ──────────────────────────────────────────────────────────────────

    public function void(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_unless($this->svc->canVoid(), 403);
        abort_if($invoice->tenant_id !== $this->tenantId(), 404);

        $request->validate([
            'void_reason' => ['required', 'in:' . implode(',', Invoice::VOID_REASONS)],
        ]);

        $this->svc->void($invoice, $request, $this->tenantId());

        return redirect()->route('tenant.invoices.index')
            ->with('status', __('invoices.flash.voided', ['number' => $invoice->invoice_number]));
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

    // ── Line-item totals (AJAX) ───────────────────────────────────────────────

    public function computeTotals(Request $request): JsonResponse
    {
        $lineItems = $request->input('line_items', []);
        return response()->json($this->svc->computeTotals($lineItems));
    }
}
