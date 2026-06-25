<?php

namespace App\Http\Requests\Admin;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Tenant $tenant */
        $tenant = $this->route('tenant');

        return [
            'gym_name' => ['required', 'string', 'min:2', 'max:80'],
            'business_type' => ['required', Rule::in(['Gym', 'Yoga', 'Turf'])],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'min:10', 'max:200'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'phone' => ['required', 'string', 'min:10', 'max:20'],
            'owner_name' => ['required', 'string', 'min:2', 'max:100'],
            'owner_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('tenants', 'owner_email')->ignore($tenant->id),
                Rule::unique('users', 'email')->ignore($tenant->owner_user_id),
            ],
            'owner_password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'subdomain' => ['required', 'alpha_dash', 'min:3', 'max:20', Rule::unique('tenants', 'subdomain')->ignore($tenant->id)],
            'domain_mode' => ['required', Rule::in(['shared', 'separate'])],
            'custom_domain' => ['nullable', 'string', 'max:255', 'required_if:domain_mode,separate', Rule::unique('tenants', 'custom_domain')->ignore($tenant->id)],
            'database_mode' => ['required', Rule::in(['shared', 'separate'])],
            'status'           => ['required', Rule::in(['active', 'trial', 'trial_ended', 'subscription_expired', 'suspended', 'archived'])],
            'default_language' => ['required', 'exists:platform_languages,locale_code'],
            'plan_id'          => ['nullable', 'exists:plans,id'],
            'trial_end_date'   => ['nullable', 'date'],
            'notes'            => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $domainMode = $this->input('domain_mode', 'shared');

        $this->merge([
            'owner_email' => strtolower((string) $this->input('owner_email')),
            'subdomain' => strtolower((string) $this->input('subdomain')),
            'custom_domain' => $domainMode === 'separate'
                ? strtolower((string) $this->input('custom_domain'))
                : null,
            'database_mode' => $domainMode === 'shared'
                ? 'shared'
                : $this->input('database_mode', 'shared'),
        ]);
    }
}
