<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class PasswordChangeController extends Controller
{
    public function edit()
    {
        return Inertia::render('Auth/ForcePasswordChange');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], (string) $user?->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user?->forceFill([
            'password' => $validated['password'],
            'must_change_password' => false,
        ])->save();

        $dashboardRoute = $user?->isSuperAdmin() ? 'admin.dashboard' : 'tenant.dashboard';

        return redirect()->route($dashboardRoute)->with('status', 'Password updated successfully.');
    }
}
