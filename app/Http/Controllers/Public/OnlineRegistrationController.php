<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MemberRegistration;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OnlineRegistrationController extends Controller
{
    public function show(string $token)
    {
        $tenant = Tenant::where('registration_token', $token)
            ->where('status', 'active')
            ->firstOrFail();

        return Inertia::render('Public/Registration/Show', compact('tenant', 'token'));
    }

    public function submit(Request $request, string $token): RedirectResponse
    {
        $tenant = Tenant::where('registration_token', $token)
            ->where('status', 'active')
            ->firstOrFail();

        $validated = $request->validate([
            'name'    => 'required|string|min:2|max:100',
            'phone'   => 'required|string|max:20',
            'email'   => 'nullable|email|max:255',
            'gender'  => 'nullable|in:male,female,other',
            'dob'     => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
        ]);

        MemberRegistration::create([
            ...$validated,
            'tenant_id' => $tenant->id,
            'status'    => 'pending',
        ]);

        return redirect()->route('register.success', $token);
    }

    public function success(string $token)
    {
        $tenant = Tenant::where('registration_token', $token)->firstOrFail();

        return Inertia::render('Public/Registration/Success', compact('tenant'));
    }
}
