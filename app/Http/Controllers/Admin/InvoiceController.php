<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Http\Requests\Admin\StoreRenewalRequest;
use App\Services\Admin\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService)
    {
    }

    public function index(Request $request)
    {
        return Inertia::render('Admin/Invoices/Index', array_merge(
            $this->invoiceService->getIndexData(),
            ['tab' => $request->get('tab', 'renewal_due')]
        ));
    }

    public function storeRenewal(StoreRenewalRequest $request): RedirectResponse
    {
        $this->invoiceService->processRenewal($request->validated());

        return redirect()->route('admin.invoices.index', ['tab' => 'renewal_due'])
            ->with('status', 'Renewal processed successfully.');
    }

    public function storePartPayment(StoreRenewalRequest $request): RedirectResponse
    {
        $this->invoiceService->recordPartPayment($request->validated());

        return redirect()->route('admin.invoices.index', ['tab' => 'history'])
            ->with('status', 'Part payment recorded.');
    }

    public function storePayment(StorePaymentRequest $request): RedirectResponse
    {
        $this->invoiceService->recordPayment($request->validated());

        return redirect()->route('admin.invoices.index', ['tab' => 'history'])
            ->with('status', 'Manual payment recorded.');
    }
}
