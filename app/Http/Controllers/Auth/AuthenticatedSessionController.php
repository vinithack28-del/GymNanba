<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly AuthenticationService $authenticationService)
    {
    }

    /**
     * Show the login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate the incoming request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $this->authenticationService->login($request);

        $user = $request->user();

        // Super-admins always go to admin dashboard — never follow a stale tenant intended URL.
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('tenant.dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->authenticationService->logout($request);

        return redirect()->route('login');
    }
}
