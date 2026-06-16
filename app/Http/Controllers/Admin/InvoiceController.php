<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Services\Admin\InvoiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService)
    {
    }

    public function index(): View
    {
        return view('admin.invoices.index', $this->invoiceService->getIndexData());
    }

    public function storePayment(StorePaymentRequest $request): RedirectResponse
    {
        $this->invoiceService->recordPayment($request->validated());

        return redirect()->route('admin.invoices.index')->with('status', 'Manual payment recorded.');
    }
}
