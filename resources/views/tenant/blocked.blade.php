<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Blocked | {{ config('app.name', 'GymNanba') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    @vite(['resources/css/app.css'])
    <script>document.documentElement.dataset.theme = localStorage.getItem('gymos-theme') || 'dark';</script>
</head>
<body class="min-h-screen">
<div class="app-theme-shell flex min-h-screen items-center justify-center px-4 py-10">
    <div class="w-full max-w-md text-center">

        @if ($status === 'trial_ended')
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full"
                 style="background:color-mix(in srgb,#f59e0b 15%,transparent)">
                <svg class="h-10 w-10" style="color:#f59e0b" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <h1 class="text-2xl font-semibold" style="color:var(--app-text)">Your free trial has ended</h1>
            <p class="mt-3 text-sm leading-7" style="color:var(--app-text-muted)">
                Your trial period is over. To continue using GymNanba, please contact support to upgrade your account.
            </p>
        @elseif ($status === 'subscription_expired')
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full"
                 style="background:color-mix(in srgb,#ef4444 15%,transparent)">
                <svg class="h-10 w-10" style="color:#ef4444" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                </svg>
            </div>
            <h1 class="text-2xl font-semibold" style="color:var(--app-text)">Subscription expired</h1>
            <p class="mt-3 text-sm leading-7" style="color:var(--app-text-muted)">
                @if ($subscription?->end_date)
                    Your subscription ended on <strong>{{ $subscription->end_date->format('d M Y') }}</strong>.
                @endif
                Please contact support to renew your plan and restore access.
            </p>
        @elseif ($status === 'suspended')
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full"
                 style="background:color-mix(in srgb,#ef4444 15%,transparent)">
                <svg class="h-10 w-10" style="color:#ef4444" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/><path d="M4.93 4.93l14.14 14.14"/>
                </svg>
            </div>
            <h1 class="text-2xl font-semibold" style="color:var(--app-text)">Account suspended</h1>
            <p class="mt-3 text-sm leading-7" style="color:var(--app-text-muted)">
                Your account has been suspended. Please contact support to resolve this.
            </p>
        @else
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full"
                 style="background:color-mix(in srgb,#6b7280 15%,transparent)">
                <svg class="h-10 w-10" style="color:#6b7280" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <h1 class="text-2xl font-semibold" style="color:var(--app-text)">Account inactive</h1>
            <p class="mt-3 text-sm leading-7" style="color:var(--app-text-muted)">
                Your account is no longer active. Please contact support for assistance.
            </p>
        @endif

        <div class="mt-8 rounded-2xl p-5" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <p class="text-sm" style="color:var(--app-text-muted)">
                Contact GymNanba support at
                <a href="mailto:support@gymos.in" class="font-medium underline" style="color:var(--app-brand)">support@gymos.in</a>
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button type="submit" class="text-sm underline" style="color:var(--app-text-muted)">Sign out</button>
        </form>
    </div>
</div>
</body>
</html>
