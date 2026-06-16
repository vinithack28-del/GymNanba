<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sign in | {{ config('app.name', 'GymNanba') }}</title>

        <script>
            document.documentElement.dataset.theme = localStorage.getItem('gymos-theme') || 'dark';
        </script>

        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen">
        <div class="app-theme-shell min-h-screen">
            <header class="app-topbar sticky top-0 z-30 border-b px-4 py-4 backdrop-blur lg:px-6">
                <div class="flex w-full items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="app-brand-soft app-brand-text flex h-11 w-11 items-center justify-center rounded-2xl text-lg font-semibold">
                            G
                        </div>
                        <div>
                            <p class="app-brand-text text-xs font-semibold uppercase tracking-[0.42em]">{{ __('common.app_name') }}</p>
                            <h1 class="mt-1 text-lg font-semibold">{{ __('auth.portal') }}</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="/" class="app-muted text-sm transition hover:opacity-80">{{ __('auth.back_to_home') }}</a>
                        <button
                            type="button"
                            id="theme-toggle"
                            class="inline-flex items-center rounded-full border border-[var(--app-border)] px-1 py-1 transition hover:opacity-90"
                            aria-label="Toggle dark and light mode"
                        >
                            <span class="app-brand-soft inline-flex h-7 w-14 items-center rounded-full">
                                <span class="app-toggle-thumb inline-flex h-5 w-5 rounded-full bg-[var(--app-brand)] shadow"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </header>

            <main class="flex min-h-[calc(100vh-76px)] w-full items-center px-4 py-8 lg:px-8">
                <div class="grid w-full gap-10 lg:grid-cols-[minmax(0,1.2fr)_640px]">
                <section class="flex flex-col justify-center">
                    <p class="app-brand-text text-sm font-semibold uppercase tracking-[0.4em]">GymNanba</p>
                    <h1 class="mt-6 max-w-2xl text-5xl font-semibold tracking-tight sm:text-6xl">
                        Unified access for every role in your gym.
                    </h1>
                    <p class="app-muted mt-6 max-w-xl text-lg leading-8">
                        Sign in as superadmin, admin, trainer, receptionist, or member to manage the work that matters
                        to your role.
                    </p>
                </section>

                <section class="app-panel rounded-[2rem] border p-8 shadow-2xl shadow-black/20 backdrop-blur">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="app-muted text-sm">{{ __('auth.welcome_back') }}</p>
                            <h2 class="mt-2 text-3xl font-semibold">{{ __('auth.sign_in') }}</h2>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="mt-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                        @csrf
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-slate-200">{{ __('auth.email') }}</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                placeholder="name@gymnanba.com"
                                value="{{ old('email') }}"
                                class="app-panel-strong w-full rounded-2xl border px-4 py-3 outline-none transition placeholder:text-slate-500 focus:border-orange-400"
                                required
                                autofocus
                            >
                        </div>
                        <div>
                            <label for="password" class="mb-2 block text-sm font-medium text-slate-200">{{ __('auth.password') }}</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Enter your password"
                                class="app-panel-strong w-full rounded-2xl border px-4 py-3 outline-none transition placeholder:text-slate-500 focus:border-orange-400"
                                required
                            >
                        </div>
                        <label class="app-muted flex items-center gap-3 text-sm">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                class="h-4 w-4 rounded border-white/10 bg-slate-950/70 text-orange-500 focus:ring-orange-400"
                            >
                            {{ __('auth.remember_me') }}
                        </label>
                        <button
                            type="submit"
                            class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-orange-400"
                        >
                            {{ __('auth.sign_in') }}
                        </button>
                    </form>

                    <div class="mt-8">
                        <p class="app-muted text-sm font-medium">{{ __('auth.default_login_details') }}</p>
                        <div class="mt-3 overflow-hidden rounded-2xl border border-[var(--app-border)]">
                            <table class="w-full table-fixed divide-y divide-white/10 text-left text-sm">
                                <thead class="app-panel-strong text-slate-300">
                                    <tr>
                                        <th class="w-[22%] px-3 py-3 font-medium">Role</th>
                                        <th class="w-[36%] px-3 py-3 font-medium">Email</th>
                                        <th class="w-[27%] px-3 py-3 font-medium">Password</th>
                                        <th class="w-[15%] px-3 py-3 font-medium text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10 bg-transparent text-slate-200">
                                    <tr>
                                        <td class="px-3 py-3 align-middle font-medium break-words">Superadmin</td>
                                        <td class="px-3 py-3 align-middle text-slate-300 break-words text-[13px] leading-5">superadmin@gymnanba.com</td>
                                        <td class="px-3 py-3 align-middle font-mono text-[12px] leading-5 tracking-[0.02em] text-orange-200 break-all">SuperAdmin@123</td>
                                        <td class="px-3 py-3 text-center align-middle">
                                            <button
                                                type="button"
                                                class="credential-fill inline-flex items-center justify-center rounded-full border border-[var(--app-border)] bg-[var(--app-brand-soft)] p-2 transition hover:opacity-90"
                                                data-email="superadmin@gymnanba.com"
                                                data-password="SuperAdmin@123"
                                                title="Copy and fill login details"
                                                aria-label="Copy and fill superadmin login details"
                                            >
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <rect x="9" y="9" width="13" height="13" rx="2"></rect>
                                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="app-muted mt-2 text-xs">{{ __('auth.credential_help') }}</p>
                        <p id="credential-feedback" class="mt-3 text-xs text-emerald-300"></p>
                    </div>
                </section>
                </div>
            </main>
        </div>

        <script>
            const themeToggle = document.getElementById('theme-toggle');

            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const nextTheme = document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';
                    document.documentElement.dataset.theme = nextTheme;
                    localStorage.setItem('gymos-theme', nextTheme);
                });
            }

            document.querySelectorAll('.credential-fill').forEach((button) => {
                button.addEventListener('click', async () => {
                    const email = button.dataset.email ?? '';
                    const password = button.dataset.password ?? '';
                    const emailInput = document.getElementById('email');
                    const passwordInput = document.getElementById('password');
                    const feedback = document.getElementById('credential-feedback');

                    if (emailInput) {
                        emailInput.value = email;
                        emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }

                    if (passwordInput) {
                        passwordInput.value = password;
                        passwordInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }

                    try {
                        await navigator.clipboard.writeText(`Email: ${email}\nPassword: ${password}`);

                        if (feedback) {
                            feedback.textContent = 'Superadmin credentials copied and filled into the form.';
                        }
                    } catch (error) {
                        if (feedback) {
                            feedback.textContent = 'Superadmin credentials filled into the form.';
                        }
                    }
                });
            });
        </script>
    </body>
</html>
