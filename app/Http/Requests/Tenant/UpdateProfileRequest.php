<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'gym_name'      => ['required', 'string', 'min:2', 'max:80'],
            'business_type' => ['required', 'string', 'in:gym,yoga_studio,crossfit,martial_arts,dance,sports_club,other'],
            'logo'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,svg', 'max:2048'],
            'cover_photo'   => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'address'       => ['required', 'string', 'min:5', 'max:100'],
            'address2'      => ['nullable', 'string', 'max:100'],
            'city'          => ['required', 'string', 'min:2', 'max:50'],
            'state'         => ['required', 'string', 'max:50'],
            'pin'           => ['required', 'digits:6'],
            'phone'         => ['required', 'string', 'min:10', 'max:20'],
            'email'         => ['required', 'email', 'max:255'],
            'website'       => ['nullable', 'url', 'max:255'],
            'gstin'         => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][A-Z0-9]Z[0-9A-Z]$/'],
            'pan'           => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'reg_number'    => ['nullable', 'string', 'max:30'],
            'instagram'     => ['nullable', 'url', 'max:255'],
            'facebook'      => ['nullable', 'url', 'max:255'],
            'about'         => ['nullable', 'string', 'max:1000'],

            'hours'             => ['nullable', 'array'],
            'hours.*.open'      => ['nullable', 'date_format:H:i'],
            'hours.*.close'     => ['nullable', 'date_format:H:i'],
            'hours.*.closed'    => ['nullable', 'boolean'],
        ];
    }
}
