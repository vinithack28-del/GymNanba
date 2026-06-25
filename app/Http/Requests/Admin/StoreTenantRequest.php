<?php

namespace App\Http\Requests\Admin;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gym_name' => ['required', 'string', 'min:2', 'max:80'],
            'business_type' => ['required', Rule::in(['Gym', 'Yoga', 'Turf'])],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'min:10', 'max:200'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'phone' => ['required', 'string', 'min:10', 'max:20'],
            'owners'              => ['required', 'array', 'min:1'],
            'owners.*.name'       => ['required', 'string', 'min:2', 'max:100'],
            'owners.*.email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'owners.*.phone'      => ['required', 'string', 'min:10', 'max:20'],
            'owners.0.email'      => ['required', 'email', 'max:255', Rule::unique('tenants', 'owner_email'), Rule::unique('users', 'email')],
            'subdomain' => ['required', 'alpha_dash', 'min:3', 'max:20', 'unique:tenants,subdomain'],
            'domain_mode' => ['required', Rule::in(['shared', 'separate'])],
            'custom_domain' => ['nullable', 'string', 'max:255', 'required_if:domain_mode,separate', 'unique:tenants,custom_domain'],
            'database_mode' => ['required', Rule::in(['shared', 'separate'])],
            'plan_id'                    => ['required', 'exists:plans,id'],
            'trial_end_date'             => ['nullable', 'date', 'after_or_equal:today'],
            'notes'                      => ['nullable', 'string'],
            'payment_splits'             => ['nullable', 'array'],
            'payment_splits.*.method'    => ['required_with:payment_splits', Rule::in(['Cash', 'Bank transfer', 'UPI', 'Cheque'])],
            'payment_splits.*.amount'    => ['required_with:payment_splits', 'numeric', 'min:0.01'],
            'payment_splits.*.reference' => ['nullable', 'string', 'max:100'],
            'payment_paid_at'            => ['nullable', 'date'],
            'payment_notes'              => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Trial plans are free — discard any accidentally-submitted payment splits
        $planId = $this->input('plan_id');
        if ($planId) {
            $plan = Plan::find($planId);
            if ($plan && ($plan->is_trial || $plan->price_paise == 0)) {
                $this->merge(['payment_splits' => null]);
            }
        }

        $domainMode = $this->input('domain_mode', 'shared');

        $owners = $this->input('owners', []);
        foreach ($owners as $i => $owner) {
            if (isset($owner['email'])) {
                $owners[$i]['email'] = strtolower(trim($owner['email']));
            }
        }

        $this->merge([
            'owners'        => $owners,
            'subdomain'     => strtolower((string) $this->input('subdomain')),
            'custom_domain' => $domainMode === 'separate'
                ? strtolower((string) $this->input('custom_domain'))
                : null,
            'database_mode' => $domainMode === 'shared'
                ? 'shared'
                : $this->input('database_mode', 'shared'),
        ]);
    }
}
