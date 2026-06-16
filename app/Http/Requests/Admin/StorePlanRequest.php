<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:plans,name'],
            'billing_cycle' => ['required', Rule::in(['Monthly', 'Quarterly', 'Annual'])],
            'price_inr' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'max_members' => ['required', 'integer', 'min:0'],
            'max_branches' => ['required', 'integer', 'min:0'],
            'max_staff_accounts' => ['required', 'integer', 'min:0'],
            'trial_eligible' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['active', 'archived'])],
            'features' => ['nullable', 'array'],
        ];
    }
}
