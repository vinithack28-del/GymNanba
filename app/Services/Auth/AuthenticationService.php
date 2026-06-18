<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\StaffLoginActivity;
use App\Services\Admin\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(LoginRequest $request): void
    {
        $credentials = $request->validated();

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        if (Auth::user()?->staffProfile && Auth::user()?->staffProfile?->status !== 'active') {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'This staff account is inactive. Please contact the gym owner.',
            ]);
        }

        $request->session()->regenerate();

        Auth::user()?->update([
            'last_login_at' => now(),
        ]);

        if (Auth::user()?->role === 'tenant_owner' && Auth::user()?->tenant) {
            Auth::user()->tenant->update([
                'last_owner_login_at' => now(),
            ]);
        }

        if (Auth::user()?->staffProfile && Auth::user()?->tenant_id) {
            StaffLoginActivity::query()->create([
                'tenant_id' => Auth::user()->tenant_id,
                'staff_id' => Auth::user()->staffProfile->id,
                'ip_address' => $request->ip(),
                'device' => substr((string) $request->userAgent(), 0, 255),
                'location' => null,
                'logged_in_at' => now(),
            ]);
        }

        $this->auditLogService->log(
            'LOGIN',
            'ADMIN_ACCOUNT',
            (string) Auth::id(),
            Auth::user()?->email,
            ['status' => 'Authenticated successfully'],
            $request,
            Auth::user(),
        );
    }

    public function logout(Request $request): void
    {
        $this->auditLogService->log(
            'LOGOUT',
            'ADMIN_ACCOUNT',
            (string) Auth::id(),
            Auth::user()?->email,
            ['status' => 'Session terminated'],
            $request,
            Auth::user(),
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
