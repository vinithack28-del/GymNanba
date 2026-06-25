<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Change Password | {{ config('app.name', 'GymNanba') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen">
        <div class="app-theme-shell flex min-h-screen items-center justify-center px-4 py-10">
            <div class="app-panel w-full max-w-lg rounded-[2rem] border p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.34em] text-[var(--app-info)]">Security</p>
                <h1 class="mt-4 text-3xl font-semibold">Change your temporary password</h1>
                <p class="app-muted mt-3 text-sm leading-7">
                    This account was created with a temporary password. Set a new password before continuing.
                </p>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.change.update') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-medium">Current password</label>
                        <input type="password" name="current_password" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">New password</label>
                        <input type="password" name="password" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Confirm new password</label>
                        <input type="password" name="password_confirmation" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <button type="submit" class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-orange-400">
                        Update password
                    </button>
                </form>
            </div>
        </div>
    </body>
</html>
