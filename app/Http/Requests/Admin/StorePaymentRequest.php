<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'amount_inr' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', Rule::in(['Cash', 'Bank transfer', 'UPI', 'Cheque'])],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
            'paid_at'         => ['required', 'date'],
            'notes'           => ['nullable', 'string', 'max:255'],
        ];
    }
}
