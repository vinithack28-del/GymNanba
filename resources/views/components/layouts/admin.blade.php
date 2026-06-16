@props([
    'title' => 'Admin Portal',
    'eyebrow' => 'Platform operations',
    'heading' => 'Portal',
    'subheading' => null,
])

@php
    $user = auth()->user();
    $isSuperAdmin = $user?->isSuperAdmin() ?? false;
    $portalTitle = $isSuperAdmin ? __('common.portal_title') : 'Gym Owner Portal';
    $portalEyebrow = $isSuperAdmin ? __('common.app_name') : ($user?->tenant?->gym_name ?? __('common.app_name'));
    $languageRoute = route('language.update');

    $adminLinks = [
        ['label' => __('common.nav.dashboard'), 'route' => 'admin.dashboard', 'icon' => 'grid'],
        ['label' => __('common.nav.tenants'), 'route' => 'admin.tenants.index', 'icon' => 'building'],
        ['label' => __('common.nav.plans'), 'route' => 'admin.plans.index', 'icon' => 'tag'],
        ['label' => __('common.nav.subscriptions'), 'route' => 'admin.subscriptions.index', 'icon' => 'stack'],
        ['label' => __('common.nav.invoices'), 'route' => 'admin.invoices.index', 'icon' => 'wallet'],
        ['label' => __('common.nav.audit'), 'route' => 'admin.audit-log.index', 'icon' => 'activity'],
        ['label' => __('common.nav.settings'), 'route' => 'admin.settings.index', 'icon' => 'settings'],
    ];

    $currentTenantSlug = request()->route('slug');
    $tenantRoute = static fn (string $slug): string => route('tenant.coming-soon', $slug);
    $isTenantItemActive = static function (?string $slug, array $children = []) use ($currentTenantSlug): bool {
        if ($slug && $currentTenantSlug === $slug) {
            return true;
        }

        return collect($children)->contains(fn (array $child): bool => ($child['slug'] ?? null) === $currentTenantSlug);
    };

    $tenantSections = [
        [
            'heading' => null,
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => route('tenant.dashboard'),
                    'icon' => 'grid',
                    'active' => request()->routeIs('tenant.dashboard'),
                ],
            ],
        ],
        [
            'heading' => 'Members',
            'items' => [
                ['label' => 'Members', 'slug' => 'members', 'route' => $tenantRoute('members'), 'icon' => 'users'],
                ['label' => 'Memberships / plans', 'slug' => 'memberships-plans', 'route' => $tenantRoute('memberships-plans'), 'icon' => 'card'],
                ['label' => 'Renewals due', 'slug' => 'renewals-due', 'route' => $tenantRoute('renewals-due'), 'icon' => 'clock', 'badge' => '12'],
                [
                    'label' => 'Attendance',
                    'slug' => 'attendance',
                    'route' => $tenantRoute('attendance'),
                    'icon' => 'scan',
                    'children' => [
                        ['label' => 'Check-in log', 'slug' => 'check-in-log', 'route' => $tenantRoute('check-in-log')],
                        ['label' => 'Walk-ins', 'slug' => 'walk-ins', 'route' => $tenantRoute('walk-ins')],
                    ],
                ],
            ],
        ],
        [
            'heading' => 'Operations',
            'items' => [
                [
                    'label' => 'Classes & schedules',
                    'slug' => 'classes-schedules',
                    'route' => $tenantRoute('classes-schedules'),
                    'icon' => 'calendar',
                    'children' => [
                        ['label' => 'Timetable', 'slug' => 'timetable', 'route' => $tenantRoute('timetable')],
                        ['label' => 'Book a class', 'slug' => 'book-a-class', 'route' => $tenantRoute('book-a-class')],
                        ['label' => 'Trainers', 'slug' => 'trainers', 'route' => $tenantRoute('trainers')],
                    ],
                ],
                ['label' => 'Branches', 'slug' => 'branches', 'route' => $tenantRoute('branches'), 'icon' => 'office'],
                [
                    'label' => 'Staff',
                    'slug' => 'staff',
                    'route' => $tenantRoute('staff'),
                    'icon' => 'team',
                    'children' => [
                        ['label' => 'All staff', 'slug' => 'all-staff', 'route' => $tenantRoute('all-staff')],
                        ['label' => 'Roles & permissions', 'slug' => 'roles-permissions', 'route' => $tenantRoute('roles-permissions')],
                        ['label' => 'Attendance', 'slug' => 'staff-attendance', 'route' => $tenantRoute('staff-attendance')],
                    ],
                ],
            ],
        ],
        [
            'heading' => 'Finance',
            'items' => [
                [
                    'label' => 'Payments',
                    'slug' => 'payments',
                    'route' => $tenantRoute('payments'),
                    'icon' => 'wallet',
                    'badge' => '3',
                    'children' => [
                        ['label' => 'Collect fee', 'slug' => 'collect-fee', 'route' => $tenantRoute('collect-fee')],
                        ['label' => 'Payment history', 'slug' => 'payment-history', 'route' => $tenantRoute('payment-history')],
                        ['label' => 'Pending dues', 'slug' => 'pending-dues', 'route' => $tenantRoute('pending-dues')],
                    ],
                ],
                ['label' => 'Invoices', 'slug' => 'invoices', 'route' => $tenantRoute('invoices'), 'icon' => 'receipt'],
                [
                    'label' => 'POS / store',
                    'slug' => 'pos-store',
                    'route' => $tenantRoute('pos-store'),
                    'icon' => 'cart',
                    'children' => [
                        ['label' => 'Products', 'slug' => 'products', 'route' => $tenantRoute('products')],
                        ['label' => 'Sales', 'slug' => 'sales', 'route' => $tenantRoute('sales')],
                        ['label' => 'Stock', 'slug' => 'stock', 'route' => $tenantRoute('stock')],
                    ],
                ],
                ['label' => 'Expenses', 'slug' => 'expenses', 'route' => $tenantRoute('expenses'), 'icon' => 'doc'],
            ],
        ],
        [
            'heading' => 'Insights',
            'items' => [
                [
                    'label' => 'Reports',
                    'slug' => 'reports',
                    'route' => $tenantRoute('reports'),
                    'icon' => 'chart',
                    'children' => [
                        ['label' => 'Revenue report', 'slug' => 'revenue-report', 'route' => $tenantRoute('revenue-report')],
                        ['label' => 'Member report', 'slug' => 'member-report', 'route' => $tenantRoute('member-report')],
                        ['label' => 'Attendance report', 'slug' => 'attendance-report', 'route' => $tenantRoute('attendance-report')],
                        ['label' => 'Staff report', 'slug' => 'staff-report', 'route' => $tenantRoute('staff-report')],
                    ],
                ],
            ],
        ],
        [
            'heading' => 'Configuration',
            'items' => [
                ['label' => 'Notifications', 'slug' => 'notifications', 'route' => $tenantRoute('notifications'), 'icon' => 'bell'],
                [
                    'label' => 'Settings',
                    'slug' => 'settings',
                    'route' => $tenantRoute('settings'),
                    'icon' => 'settings',
                    'children' => [
                        ['label' => 'Gym profile', 'slug' => 'gym-profile', 'route' => $tenantRoute('gym-profile')],
                        ['label' => 'My account', 'slug' => 'my-account', 'route' => $tenantRoute('my-account')],
                        ['label' => 'Integrations', 'slug' => 'integrations', 'route' => $tenantRoute('integrations')],
                        ['label' => 'Language', 'slug' => 'language', 'route' => $tenantRoute('language')],
                    ],
                ],
            ],
        ],
    ];

    $icon = static function (string $name): string {
        return match ($name) {
            'grid' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>',
            'building' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V7a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v14"/><path d="M9 21v-4h2v4"/><path d="M8 9h1"/><path d="M8 12h1"/><path d="M12 9h1"/><path d="M12 12h1"/><path d="M16 21h4V11a2 2 0 0 0-2-2h-2"/></svg>',
            'tag' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 10 12 2H4v8l8 8 8-8Z"/><path d="M7.5 7.5h.01"/></svg>',
            'stack' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 9 4.5-9 4.5-9-4.5L12 3Z"/><path d="m3 12 9 4.5 9-4.5"/><path d="m3 16.5 9 4.5 9-4.5"/></svg>',
            'wallet' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M16 12h.01"/><path d="M3 9h18"/></svg>',
            'activity' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12h4l3-7 4 14 3-7h4"/></svg>',
            'settings' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1V21a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-.4-1 1.7 1.7 0 0 0-1-.6 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1-.4H3a2 2 0 1 1 0-4h.09a1.7 1.7 0 0 0 1-.4 1.7 1.7 0 0 0 .6-1 1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 .4 1 1.7 1.7 0 0 0 1 .6 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.25.3.46.65.6 1 .1.32.1.66 0 1-.14.35-.35.7-.6 1Z"/></svg>',
            'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="3"/><path d="M20 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 4.13a4 4 0 0 1 0 7.75"/></svg>',
            'card' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 7h6"/><path d="M9 11h6"/><path d="M9 15h4"/></svg>',
            'clock' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg>',
            'scan' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7V5a1 1 0 0 1 1-1h2"/><path d="M17 4h2a1 1 0 0 1 1 1v2"/><path d="M20 17v2a1 1 0 0 1-1 1h-2"/><path d="M7 20H5a1 1 0 0 1-1-1v-2"/><path d="M7 12h10"/></svg>',
            'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M3 11h18"/></svg>',
            'office' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M15 9h.01"/><path d="M15 13h.01"/></svg>',
            'team' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="7" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M14 20a5 5 0 0 1 7 0"/></svg>',
            'receipt' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6"/><path d="M9 12h6"/></svg>',
            'cart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/><path d="M3 4h2l2.4 10.5a1 1 0 0 0 1 .8h8.9a1 1 0 0 0 1-.76L20 7H7"/></svg>',
            'doc' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M14 3v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>',
            'chart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-7"/><path d="M22 20H2"/></svg>',
            'bell' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 17H5.5a1.5 1.5 0 0 1-1.1-2.52C5.6 13.2 6 11.65 6 10V8a6 6 0 1 1 12 0v2c0 1.65.4 3.2 1.6 4.48A1.5 1.5 0 0 1 18.5 17H15"/><path d="M9.5 20a2.5 2.5 0 0 0 5 0"/></svg>',
            default => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/></svg>',
        };
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} | {{ config('app.name', 'GymNanba') }}</title>

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
                            <p class="app-brand-text text-xs font-semibold uppercase tracking-[0.42em]">{{ $portalEyebrow }}</p>
                            <h1 class="mt-1 text-lg font-semibold">{{ $portalTitle }}</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @if(($activePortalLanguages ?? collect())->isNotEmpty())
                            <form method="POST" action="{{ $languageRoute }}" class="hidden md:block">
                                @csrf
                                <label class="sr-only" for="portal-locale">{{ __('common.language_selector') }}</label>
                                <select id="portal-locale" name="locale_code" onchange="this.form.submit()" class="app-panel rounded-2xl border px-3 py-2 text-sm outline-none">
                                    @foreach ($activePortalLanguages as $language)
                                        <option value="{{ $language->locale_code }}" @selected(app()->getLocale() === $language->locale_code)>
                                            {{ $language->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
                        <span class="app-muted hidden text-sm md:inline">{{ __('common.theme') }}</span>
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

            <div class="flex min-h-[calc(100vh-76px)] w-full flex-col lg:flex-row">
                <aside class="app-sidebar border-b px-5 py-6 backdrop-blur lg:min-h-[calc(100vh-76px)] lg:w-[320px] lg:border-b-0 lg:border-r lg:overflow-y-auto">
                    @if ($isSuperAdmin)
                        <nav class="mt-8 grid gap-2 text-sm">
                            @foreach ($adminLinks as $link)
                                <a
                                    href="{{ route($link['route']) }}"
                                    class="{{ request()->routeIs($link['route']) ? 'bg-[var(--app-brand)] text-slate-950' : 'app-panel-strong hover:opacity-90' }} flex items-center gap-3 rounded-2xl border px-4 py-3 font-medium transition"
                                >
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full {{ request()->routeIs($link['route']) ? 'bg-slate-950/15' : 'app-brand-soft app-brand-text' }}">
                                        <span class="app-nav-icon h-4 w-4" aria-hidden="true">{!! $icon($link['icon']) !!}</span>
                                    </span>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>
                    @else
                        <nav class="tenant-nav mt-3 text-sm">
                            @foreach ($tenantSections as $section)
                                <div class="tenant-nav-block {{ $loop->first ? '' : 'mt-6' }}">
                                    @if ($section['heading'])
                                        <p class="tenant-nav-heading">{{ $section['heading'] }}</p>
                                    @endif

                                    <div class="grid gap-1.5">
                                        @foreach ($section['items'] as $item)
                                            @php
                                                $childItems = $item['children'] ?? [];
                                                $active = $item['active'] ?? $isTenantItemActive($item['slug'] ?? null, $childItems);
                                            @endphp
                                            <div class="tenant-nav-entry">
                                                <a
                                                    href="{{ $item['route'] }}"
                                                    class="{{ $active ? 'tenant-nav-item-active' : 'tenant-nav-item' }} flex items-center gap-3 rounded-2xl px-4 py-3 transition"
                                                >
                                                    <span class="tenant-nav-icon-wrap">
                                                        <span class="tenant-nav-icon app-nav-icon" aria-hidden="true">
                                                            {!! $icon($item['icon']) !!}
                                                        </span>
                                                    </span>
                                                    <span class="tenant-nav-label flex-1">{{ $item['label'] }}</span>
                                                    @if (!empty($item['badge']))
                                                        <span class="tenant-nav-badge">{{ $item['badge'] }}</span>
                                                    @endif
                                                </a>

                                                @if (!empty($childItems))
                                                    <div class="tenant-nav-children">
                                                        @foreach ($childItems as $child)
                                                            <a
                                                                href="{{ $child['route'] }}"
                                                                class="{{ ($child['slug'] ?? null) === $currentTenantSlug ? 'tenant-nav-child-active' : 'tenant-nav-child' }}"
                                                            >
                                                                {{ $child['label'] }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </nav>
                    @endif

                    <div class="app-panel-strong mt-8 rounded-3xl border p-4">
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--app-success)]">{{ __('common.signed_in_as') }}</p>
                        <p class="mt-3 text-lg font-semibold">{{ $user?->name }}</p>
                        <p class="app-muted mt-1 break-all text-sm">{{ $user?->email }}</p>

                        <form method="POST" action="{{ route('logout') }}" class="mt-4">
                            @csrf
                            <button
                                type="submit"
                                class="app-panel w-full rounded-2xl border px-4 py-2.5 text-sm font-semibold transition hover:opacity-90"
                            >
                                {{ __('common.logout') }}
                            </button>
                        </form>
                    </div>
                </aside>

                <main class="flex-1 px-4 py-6 lg:px-8 lg:py-8">
                    <header class="app-panel mb-8 flex flex-col gap-3 rounded-[2rem] border px-6 py-5 backdrop-blur lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.38em] text-[var(--app-info)]">{{ $eyebrow }}</p>
                            <h2 class="mt-3 text-3xl font-semibold tracking-tight">{{ $heading }}</h2>
                            @if (!empty($subheading))
                                <p class="app-muted mt-2 max-w-3xl text-sm leading-7">{{ $subheading }}</p>
                            @endif
                        </div>
                        @isset($headerAction)
                            <div>{{ $headerAction }}</div>
                        @endisset
                    </header>

                    @if (session('status'))
                        <div class="mb-6 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
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
        </script>
    </body>
</html>
