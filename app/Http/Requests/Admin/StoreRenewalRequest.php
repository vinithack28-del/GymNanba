<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRenewalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPartPayment = $this->routeIs('admin.invoices.part-payment.store');
        $methods       = ['Cash', 'Bank transfer', 'UPI', 'Cheque'];

        return [
            'tenant_id'         => $isPartPayment ? ['nullable'] : ['required', 'exists:tenants,id'],
            'plan_id'           => $isPartPayment ? ['nullable'] : ['required', 'exists:plans,id'],
            'subscription_id'   => $isPartPayment ? ['required', 'exists:subscriptions,id'] : ['nullable'],
            'paid_at'           => ['required', 'date'],
            'notes'             => ['nullable', 'string', 'max:255'],

            // Split rows — at least one required
            'splits'            => ['required', 'array', 'min:1'],
            'splits.*.method'   => ['required', Rule::in($methods)],
            'splits.*.amount'   => ['required', 'numeric', 'min:0.01'],
            'splits.*.reference'=> ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'splits.required'        => 'Add at least one payment method.',
            'splits.*.method.required' => 'Payment method is required.',
            'splits.*.amount.required' => 'Amount is required for each payment method.',
            'splits.*.amount.min'    => 'Amount must be greater than zero.',
        ];
    }
}
