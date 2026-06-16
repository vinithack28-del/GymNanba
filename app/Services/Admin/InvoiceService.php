<?php

namespace App\Services\Admin;

use App\Models\Tenant;
use App\Models\TenantPayment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class InvoiceService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function getIndexData(): array
    {
        return [
            'payments' => TenantPayment::query()->with(['tenant', 'admin'])->latest('paid_at')->paginate(10),
            'tenants' => Tenant::query()->orderBy('gym_name')->get(),
        ];
    }

    public function recordPayment(array $validated): TenantPayment
    {
        $payment = TenantPayment::query()->create([
            'tenant_id' => $validated['tenant_id'],
            'admin_id' => Auth::id(),
            'amount_paise' => (int) round(((float) $validated['amount_inr']) * 100),
            'payment_method' => $validated['payment_method'],
            'transaction_ref' => $validated['transaction_ref'] ?? null,
            'paid_at' => $validated['paid_at'],
        ]);

        $tenant = Tenant::query()->findOrFail($validated['tenant_id']);

        $this->auditLogService->log(
            'PAYMENT_RECORD',
            'INVOICE',
            (string) $payment->id,
            $tenant->gym_name,
            ['amount_paise' => $payment->amount_paise],
        );

        return $payment;
    }
}
