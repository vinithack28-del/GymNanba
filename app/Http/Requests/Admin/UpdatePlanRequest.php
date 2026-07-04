<?php

namespace App\Http\Requests\Admin;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Plan $plan */
        $plan = $this->route('plan');
        $isTrial = $this->boolean('is_trial');

        return [
            'name'               => ['required', 'string', 'max:50', Rule::unique('plans', 'name')->ignore($plan->id)],
            'is_trial'           => ['nullable', 'boolean'],
            'trial_days'         => $isTrial ? ['required', 'integer', 'min:1', 'max:14'] : ['nullable'],
            'billing_cycle'      => $isTrial ? ['nullable'] : ['required', Rule::in(['Monthly', 'Quarterly', 'Annual'])],
            'price_inr'          => $isTrial ? ['nullable'] : ['required', 'numeric', 'min:0', 'max:999999.99'],
            'max_members'        => $isTrial ? ['nullable'] : ['required', 'integer', 'min:0'],
            'max_branches'       => $isTrial ? ['nullable'] : ['required', 'integer', 'min:0'],
            'max_staff_accounts' => $isTrial ? ['nullable'] : ['required', 'integer', 'min:0'],
            'trial_eligible'     => ['nullable', 'boolean'],
            'description'        => ['nullable', 'string', 'max:500'],
            'status'             => ['required', Rule::in(['active', 'archived'])],
            'features'           => ['nullable', 'array'],
        ];
    }
}

