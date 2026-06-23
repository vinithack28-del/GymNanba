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
    $canAccess = static fn (?string $permission): bool => ! $permission || ($user && method_exists($user, 'canAccess') ? $user->canAccess($permission) : false);

    $quickActions = [];
    if (! $isSuperAdmin && $user) {
        if ($canAccess('members.add')) {
            $quickActions[] = [
                'label' => __('common.quick_actions.add_member'),
                'route' => route('tenant.members.create'),
                'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/>',
            ];
        }
        if ($canAccess('attendance.check_in')) {
            $quickActions[] = [
                'label' => __('common.quick_actions.add_enquiry'),
                'route' => route('tenant.attendance.walkins', ['purpose' => 'inquiry']),
                'icon' => '<path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/>',
            ];
            $quickActions[] = [
                'label' => __('common.quick_actions.member_attendance'),
                'route' => route('tenant.attendance.checkins'),
                'icon' => '<path d="M3 12h4l3 8 4-16 3 8h4"/>',
            ];
        }
        if ($canAccess('staff.view|staff.manage')) {
            $quickActions[] = [
                'label' => __('common.quick_actions.staff_attendance'),
                'route' => route('tenant.staff.attendance'),
                'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M17 11l2 2 4-4"/>',
            ];
        }
    }

    // Renewals sidebar badge: expired + today + 7-day count
    $renewalBadge = null;
    $lowStockBadge = null;
    $dueBadge = null;
    if (!$isSuperAdmin && $user?->tenant) {
        $today = now()->toDateString();
        $renewalBadge = \App\Models\Member::forTenant($user->tenant->id)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(7)->toDateString())
            ->count();
        $renewalBadge = $renewalBadge > 0 ? (string) $renewalBadge : null;

        $lowStockCount = \App\Models\PosProduct::forTenant($user->tenant->id)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();
        $lowStockBadge = $lowStockCount > 0 ? (string) $lowStockCount : null;

        $dueCount = \App\Models\Payment::where('tenant_id', $user->tenant->id)
            ->where('status', 'active')
            ->where('is_partial', true)
            ->where('due_paise', '>', 0)
            ->count();
        $dueBadge = $dueCount > 0 ? (string) $dueCount : null;
    }
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
                    'label' => __('common.tenant_nav.dashboard'),
                    'route' => route('tenant.dashboard'),
                    'icon'  => 'grid',
                    'active' => request()->routeIs('tenant.dashboard'),
                    'permission' => 'dashboard.view',
                ],
            ],
        ],
        [
            'heading' => __('common.tenant_nav.section_members'),
            'items' => [
                ['label' => __('common.tenant_nav.members'),          'slug' => 'members',           'route' => route('tenant.members.index'), 'icon' => 'users',  'active' => request()->routeIs('tenant.members.*'),  'permission' => 'members.view|members.add|members.edit|members.delete'],
                ['label' => __('common.tenant_nav.memberships_plans'), 'slug' => 'memberships-plans', 'route' => route('tenant.plans.index'),   'icon' => 'card',   'active' => request()->routeIs('tenant.plans.*'),    'permission' => 'members.view|members.add|members.edit|members.delete'],
                ['label' => __('common.tenant_nav.renewals_due'),      'route' => route('tenant.renewals.index'),                               'icon' => 'clock',  'active' => request()->routeIs('tenant.renewals.*'), 'badge' => $renewalBadge, 'permission' => 'renewals.view'],
                ['label' => __('common.tenant_nav.attendance'),        'slug' => 'attendance',        'route' => route('tenant.attendance.checkins'), 'icon' => 'scan',   'active' => request()->routeIs('tenant.attendance.checkins'), 'permission' => 'attendance.check_in|attendance.view_log'],
                ['label' => __('common.tenant_nav.walk_ins'),          'slug' => 'walk-ins',          'route' => route('tenant.attendance.walkins'),  'icon' => 'walkin', 'active' => request()->routeIs('tenant.attendance.walkins'),  'permission' => 'attendance.check_in'],
            ],
        ],
        [
            'heading' => __('common.tenant_nav.section_operations'),
            'items' => [
                [
                    'label' => __('common.tenant_nav.classes_schedules'),
                    'slug'  => 'classes-schedules',
                    'route' => route('tenant.classes.timetable'),
                    'icon'  => 'calendar',
                    'active' => request()->routeIs('tenant.classes.*'),
                    'permission' => 'classes.view_timetable|classes.manage|classes.book',
                    'children' => [
                        ['label' => __('common.tenant_nav.timetable'),   'slug' => 'timetable',   'route' => route('tenant.classes.timetable'), 'active' => request()->routeIs('tenant.classes.timetable')],
                        ['label' => __('common.tenant_nav.book_a_class'), 'slug' => 'book-a-class', 'route' => route('tenant.classes.book'),      'active' => request()->routeIs('tenant.classes.book')],
                        ['label' => __('common.tenant_nav.trainers'),    'slug' => 'trainers',    'route' => route('tenant.classes.trainers'),  'active' => request()->routeIs('tenant.classes.trainers')],
                    ],
                ],
                ['label' => __('common.tenant_nav.branches'),  'slug' => 'branches',  'route' => route('tenant.branches.index'),  'icon' => 'office',     'active' => request()->routeIs('tenant.branches.*'),  'permission' => 'branches.view|branches.manage'],
                ['label' => __('common.tenant_nav.equipment'), 'slug' => 'equipment', 'route' => route('tenant.equipment.index'), 'icon' => 'equipment', 'active' => request()->routeIs('tenant.equipment.*'), 'permission' => 'equipment.view'],
                ['label' => __('common.tenant_nav.lockers'), 'slug' => 'lockers', 'route' => route('tenant.lockers.index'), 'icon' => 'locker', 'active' => request()->routeIs('tenant.lockers.*'), 'permission' => 'locker.view|locker.assign|locker.add|locker.edit|locker.delete'],
                [
                    'label' => __('common.tenant_nav.staff'),
                    'slug'  => 'staff',
                    'route' => route('tenant.staff.index'),
                    'icon'  => 'team',
                    'active' => request()->routeIs('tenant.staff.index', 'tenant.staff.create', 'tenant.staff.edit', 'tenant.staff.show'),
                    'permission' => 'staff.view|staff.manage',
                    'children' => [
                        ['label' => __('common.tenant_nav.all_staff'),         'slug' => 'all-staff',         'route' => route('tenant.staff.index'),      'active' => request()->routeIs('tenant.staff.index', 'tenant.staff.create', 'tenant.staff.edit', 'tenant.staff.show')],
                        ['label' => __('common.tenant_nav.roles_permissions'), 'slug' => 'roles-permissions', 'route' => route('tenant.staff.roles'),       'active' => request()->routeIs('tenant.staff.roles'), 'owner_only' => true],
                        ['label' => __('common.tenant_nav.staff_attendance'),  'slug' => 'staff-attendance',  'route' => route('tenant.staff.attendance'), 'active' => request()->routeIs('tenant.staff.attendance')],
                    ],
                ],
            ],
        ],
        [
            'heading' => __('common.tenant_nav.section_assess'),
            'items' => [
                [
                    'label' => __('common.tenant_nav.assess'),
                    'slug'  => 'assess',
                    'route' => route('tenant.assess.report'),
                    'icon'  => 'chart',
                    'active' => request()->routeIs('tenant.assess.*'),
                    'permission' => 'assessment_report.view|parq.view|nutrition.view|body_metrics.view|posture.view|balance.view|vitals.view|fitness.view|goal_forecasting.view',
                    'children' => [
                        ['label' => __('common.tenant_nav.assessment_report'), 'slug' => 'assessment-report', 'route' => route('tenant.assess.report'), 'active' => request()->routeIs('tenant.assess.report'), 'permission' => 'assessment_report.view'],
                        ['label' => __('common.tenant_nav.parq'), 'slug' => 'parq', 'route' => route('tenant.assess.questionnaire'), 'active' => request()->routeIs('tenant.assess.questionnaire'), 'permission' => 'parq.view'],
                        ['label' => __('common.tenant_nav.nutrition'), 'slug' => 'nutrition', 'route' => route('tenant.assess.nutrition'), 'active' => request()->routeIs('tenant.assess.nutrition'), 'permission' => 'nutrition.view'],
                        ['label' => __('common.tenant_nav.body_metrics'), 'slug' => 'body-metrics', 'route' => route('tenant.assess.body-metrics'), 'active' => request()->routeIs('tenant.assess.body-metrics', 'tenant.assess.body-metrics.progress'), 'permission' => 'body_metrics.view'],
                        ['label' => __('common.tenant_nav.posture'), 'slug' => 'posture', 'route' => route('tenant.assess.posture'), 'active' => request()->routeIs('tenant.assess.posture'), 'permission' => 'posture.view'],
                        ['label' => __('common.tenant_nav.balance'), 'slug' => 'balance', 'route' => route('tenant.assess.balance'), 'active' => request()->routeIs('tenant.assess.balance'), 'permission' => 'balance.view'],
                        ['label' => __('common.tenant_nav.vitals'), 'slug' => 'vitals', 'route' => route('tenant.assess.vitals'), 'active' => request()->routeIs('tenant.assess.vitals'), 'permission' => 'vitals.view'],
                        ['label' => __('common.tenant_nav.fitness'), 'slug' => 'fitness', 'route' => route('tenant.assess.fitness'), 'active' => request()->routeIs('tenant.assess.fitness'), 'permission' => 'fitness.view'],
                        ['label' => __('common.tenant_nav.goal_forecasting'), 'slug' => 'goal-forecasting', 'route' => route('tenant.assess.goal-forecasting'), 'active' => request()->routeIs('tenant.assess.goal-forecasting'), 'permission' => 'goal_forecasting.view'],
                    ],
                ],
            ],
        ],
        [
            'heading' => __('common.tenant_nav.section_finance'),
            'items' => [
                [
                    'label' => __('common.tenant_nav.payments'),
                    'slug'  => 'payments',
                    'route' => route('tenant.payments.history'),
                    'icon'  => 'wallet',
                    'active' => request()->routeIs('tenant.payments.*'),
                    'badge' => $dueBadge,
                    'permission' => 'payments.collect|payments.history|payments.void',
                ],
                ['label' => __('common.tenant_nav.invoices'), 'slug' => 'invoices', 'route' => route('tenant.invoices.index'), 'icon' => 'receipt', 'permission' => 'invoices.view|invoices.manage'],
                [
                    'label' => __('common.tenant_nav.pos_store'),
                    'slug'  => 'pos-store',
                    'route' => route('tenant.pos.sales'),
                    'icon'  => 'cart',
                    'active' => request()->routeIs('tenant.pos.*'),
                    'badge' => $lowStockBadge,
                    'permission' => 'pos.billing|pos.products_stock_view|pos.manage_products',
                    'children' => [
                        ['label' => __('common.tenant_nav.products'), 'slug' => 'products', 'route' => route('tenant.pos.products'), 'active' => request()->routeIs('tenant.pos.products', 'tenant.pos.products.create', 'tenant.pos.products.edit')],
                        ['label' => __('common.tenant_nav.sales'),    'slug' => 'sales',    'route' => route('tenant.pos.sales'),    'active' => request()->routeIs('tenant.pos.sales', 'tenant.pos.sales.show')],
                        ['label' => __('common.tenant_nav.stock'),    'slug' => 'stock',    'route' => route('tenant.pos.stock'),    'active' => request()->routeIs('tenant.pos.stock')],
                    ],
                ],
                ['label' => __('common.tenant_nav.expenses'), 'slug' => 'expenses', 'route' => route('tenant.expenses.index'), 'icon' => 'doc', 'active' => request()->routeIs('tenant.expenses.*'), 'permission' => 'expenses.view|expenses.manage'],
            ],
        ],
        [
            'heading' => __('common.tenant_nav.section_insights'),
            'items' => [
                [
                    'label' => __('common.tenant_nav.reports'),
                    'slug'  => 'reports',
                    'route' => route('tenant.reports.index'),
                    'icon'  => 'chart',
                    'active' => request()->routeIs('tenant.reports.*'),
                    'permission' => 'reports.view|reports.revenue_only|reports.branch_only|reports.own_data',
                    'children' => [
                        ['label' => __('common.tenant_nav.revenue_report'),    'slug' => 'revenue-report',    'route' => route('tenant.reports.revenue'),    'active' => request()->routeIs('tenant.reports.revenue')],
                        ['label' => __('common.tenant_nav.member_report'),     'slug' => 'member-report',     'route' => route('tenant.reports.members'),    'active' => request()->routeIs('tenant.reports.members')],
                        ['label' => __('common.tenant_nav.attendance_report'), 'slug' => 'attendance-report', 'route' => route('tenant.reports.attendance'), 'active' => request()->routeIs('tenant.reports.attendance')],
                        ['label' => __('common.tenant_nav.staff_report'),      'slug' => 'staff-report',      'route' => route('tenant.reports.staff'),      'active' => request()->routeIs('tenant.reports.staff')],
                    ],
                ],
            ],
        ],
        [
            'heading' => __('common.tenant_nav.section_config'),
            'items' => [
                ['label' => __('common.tenant_nav.notifications'), 'slug' => 'notifications', 'route' => $tenantRoute('notifications'), 'icon' => 'bell'],
                [
                    'label'  => __('common.tenant_nav.settings'),
                    'slug'   => 'settings',
                    'route'  => route('tenant.settings.profile'),
                    'icon'   => 'settings',
                    'active' => request()->routeIs('tenant.settings.*'),
                    'owner_only' => true,
                    'children' => [
                        ['label' => __('settings.nav.profile'),      'slug' => 'settings-profile',      'route' => route('tenant.settings.profile'),      'active' => request()->routeIs('tenant.settings.profile')],
                        ['label' => __('settings.nav.account'),      'slug' => 'settings-account',      'route' => route('tenant.settings.account'),      'active' => request()->routeIs('tenant.settings.account')],
                        ['label' => __('settings.nav.integrations'), 'slug' => 'settings-integrations', 'route' => route('tenant.settings.integrations'), 'active' => request()->routeIs('tenant.settings.integrations')],
                        ['label' => __('settings.nav.language'),     'slug' => 'settings-language',     'route' => route('tenant.settings.language'),     'active' => request()->routeIs('tenant.settings.language')],
                        ['label' => __('settings.nav.subscription'), 'slug' => 'settings-subscription', 'route' => route('tenant.settings.subscription'), 'active' => request()->routeIs('tenant.settings.subscription')],
                        ['label' => __('settings.nav.data'),         'slug' => 'settings-data',         'route' => route('tenant.settings.data'),         'active' => request()->routeIs('tenant.settings.data')],
                    ],
                ],
            ],
        ],
    ];

    if (!$isSuperAdmin && $user?->role === 'pos') {
        foreach ($tenantSections as &$section) {
            if (($section['heading'] ?? null) === __('common.tenant_nav.section_finance')) {
                foreach ($section['items'] as &$item) {
                    if (($item['slug'] ?? null) === 'pos-store') {
                        $item['children'] = array_values(array_filter(
                            $item['children'] ?? [],
                            fn (array $child): bool => in_array($child['slug'] ?? null, ['sales', 'stock'], true)
                        ));
                    }
                }
                unset($item);
            }
        }
        unset($section);
    }

    // Permission-based nav filtering for staff (not owners / super-admins)
    if (!$isSuperAdmin && $user && $user->isStaffMember()) {
        $tenantSections = array_values(array_map(function (array $section) use ($user): array {
            $section['items'] = array_values(array_filter($section['items'], function (array $item) use ($user): bool {
                if ($item['owner_only'] ?? false) return false;
                $perm = $item['permission'] ?? null;
                if (!$perm) return true;
                return $user->canAccess($perm);
            }));
            // Filter owner_only children too
            $section['items'] = array_map(function (array $item) use ($user): array {
                if (!empty($item['children'])) {
                    $item['children'] = array_values(array_filter($item['children'], function (array $c) use ($user): bool {
                        if ($c['owner_only'] ?? false) {
                            return false;
                        }
                        $perm = $c['permission'] ?? null;
                        return ! $perm || $user->canAccess($perm);
                    }));
                }
                return $item;
            }, $section['items']);
            $section['items'] = array_values(array_filter($section['items'], function (array $item): bool {
                if (! array_key_exists('children', $item)) {
                    return true;
                }

                return ! empty($item['children']);
            }));
            return $section;
        }, $tenantSections));
        // Drop sections that have no items left
        $tenantSections = array_values(array_filter($tenantSections, fn (array $s) => !empty($s['items'])));
    }

    // Branch switcher data (tenant users only)
    $tenantBranches    = collect();
    $selectedBranchId  = null;
    $selectedBranch    = null;
    $isOwnerBranchCtrl = !$isSuperAdmin && $user?->isGymOwner(); // only owners control branch switching
    if (!$isSuperAdmin && $user?->tenant) {
        $tenantBranches = \App\Models\Branch::forTenant($user->tenant->id)
            ->active()
            ->orderByRaw('is_primary DESC, name ASC')
            ->get();
        if ($isOwnerBranchCtrl) {
            $selectedBranchId = session('gymos_selected_branch_id');
            $selectedBranch   = $tenantBranches->find($selectedBranchId);
            if ($selectedBranchId && !$selectedBranch) {
                session()->forget('gymos_selected_branch_id');
                $selectedBranchId = null;
            }
        } else {
            // Staff: locked to their assigned branch
            $selectedBranchId = $user->branch_id;
            $selectedBranch   = $tenantBranches->find($selectedBranchId);
        }
    }

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
            'walkin' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="13" cy="4" r="1.5"/><path d="M8 20l2-5 3 3"/><path d="M14.5 9.5L16 12l3 1"/><path d="M11 9.5l-2 4 3 2.5"/><path d="M13 9l1.5-3"/></svg>',
            'equipment' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6h4v4H6z"/><path d="M14 6h4v4h-4z"/><path d="M6 14h4v4H6z"/><path d="M14 14h4v4h-4z"/><path d="M10 8h4"/><path d="M8 10v4"/><path d="M16 10v4"/><path d="M10 16h4"/></svg>',
            'locker' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="3" width="12" height="18" rx="2"/><circle cx="12" cy="12" r="1.25"/><path d="M12 8v2"/></svg>',
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
        @stack('styles')
        <style>
        /* Branch switcher */
        .bs-trigger { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.75rem; color: var(--app-text); cursor: pointer; display: inline-flex; font-size: 0.82rem; font-weight: 500; gap: 0.4rem; padding: 0.4rem 0.65rem; transition: background 140ms; white-space: nowrap; }
        .bs-trigger:hover { background: color-mix(in srgb, var(--app-border) 60%, transparent); }
        .bs-icon { align-items: center; color: var(--app-brand); display: inline-flex; flex: none; }
        .bs-icon svg { height: 1rem; width: 1rem; }
        .bs-label { max-width: 10rem; overflow: hidden; text-overflow: ellipsis; }
        .bs-chevron { flex: none; height: 0.85rem; opacity: 0.5; width: 0.85rem; }
        .bs-dropdown { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 1rem; box-shadow: 0 8px 32px rgba(0,0,0,0.22); min-width: 210px; padding: 0.35rem; position: absolute; right: 0; top: calc(100% + 0.4rem); z-index: 50; }
        .bs-dropdown-heading { color: var(--app-text-muted); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.12em; padding: 0.4rem 0.65rem 0.3rem; text-transform: uppercase; }
        .bs-option { align-items: center; border-radius: 0.6rem; color: var(--app-text-muted); cursor: pointer; display: flex; font-size: 0.82rem; gap: 0.5rem; padding: 0.45rem 0.6rem; transition: background 120ms, color 120ms; width: 100%; background: transparent; border: none; }
        .bs-option:hover { background: color-mix(in srgb, var(--app-border) 55%, transparent); color: var(--app-text); }
        .bs-option-active { color: var(--app-text); background: color-mix(in srgb, var(--app-brand-soft) 55%, transparent); }
        .bs-option-icon { align-items: center; display: inline-flex; flex: none; opacity: 0.6; }
        .bs-option-icon svg { height: 0.9rem; width: 0.9rem; }
        .bs-primary-tag { background: color-mix(in srgb, var(--app-brand-soft) 80%, transparent); border-radius: 999px; color: var(--app-brand); font-size: 0.6rem; font-weight: 700; margin-left: 0.25rem; padding: 0.05rem 0.35rem; text-transform: uppercase; vertical-align: middle; }
        .qa-trigger { align-items: center; background: var(--app-brand); border: 1px solid color-mix(in srgb, var(--app-brand) 70%, black 10%); border-radius: 999px; color: #0f172a; cursor: pointer; display: inline-flex; font-size: 0.82rem; font-weight: 700; gap: 0.45rem; padding: 0.5rem 0.85rem; transition: opacity 140ms; white-space: nowrap; }
        .qa-trigger:hover { opacity: 0.88; }
        .qa-trigger svg { height: 0.95rem; width: 0.95rem; }
        .qa-dropdown { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 1rem; box-shadow: 0 8px 32px rgba(0,0,0,0.22); min-width: 220px; padding: 0.4rem; position: absolute; right: 0; top: calc(100% + 0.45rem); z-index: 50; }
        .qa-item { align-items: center; border-radius: 0.8rem; color: var(--app-text); display: flex; font-size: 0.82rem; font-weight: 500; gap: 0.7rem; padding: 0.65rem 0.75rem; text-decoration: none; transition: background 120ms, color 120ms; }
        .qa-item:hover { background: color-mix(in srgb, var(--app-border) 55%, transparent); }
        .qa-item-icon { align-items: center; color: var(--app-brand); display: inline-flex; flex: none; }
        .qa-item-icon svg { height: 1rem; width: 1rem; }
        .theme-toggle-btn { align-items: center; background: var(--app-panel-strong); color: var(--app-text-muted); display: inline-flex; gap: 0.3rem; }
        .theme-icon { align-items: center; border-radius: 999px; color: var(--app-text-muted); display: inline-flex; height: 1.8rem; justify-content: center; transition: color 160ms ease, opacity 160ms ease; width: 1.8rem; }
        .theme-icon svg { height: 0.95rem; width: 0.95rem; }
        .theme-toggle-track { align-items: center; background: color-mix(in srgb, var(--app-border) 70%, transparent); border-radius: 999px; display: inline-flex; height: 1.9rem; padding: 0.15rem; position: relative; width: 3.4rem; }
        .theme-toggle-thumb { align-items: center; background: var(--app-brand); border-radius: 999px; color: #0f172a; display: inline-flex; height: 1.55rem; justify-content: center; transform: translateX(0); transition: transform 180ms ease; width: 1.55rem; }
        .theme-toggle-thumb svg { height: 0.8rem; position: absolute; width: 0.8rem; }
        .theme-toggle-thumb-moon { opacity: 0; }
        [data-theme='light'] .theme-icon-sun { color: var(--app-brand); }
        [data-theme='dark'] .theme-icon-moon { color: var(--app-brand); }
        [data-theme='light'] .theme-toggle-thumb { transform: translateX(0); }
        [data-theme='light'] .theme-toggle-thumb-sun { opacity: 1; }
        [data-theme='light'] .theme-toggle-thumb-moon { opacity: 0; }
        [data-theme='dark'] .theme-toggle-thumb { transform: translateX(1.5rem); }
        [data-theme='dark'] .theme-toggle-thumb-sun { opacity: 0; }
        [data-theme='dark'] .theme-toggle-thumb-moon { opacity: 1; }
        </style>
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
                        @if (!empty($quickActions))
                        <div class="relative" id="quick-add-wrap">
                            <button
                                type="button"
                                id="quick-add-btn"
                                class="qa-trigger"
                                aria-haspopup="true"
                                aria-expanded="false"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                <span class="hidden md:inline">{{ __('common.quick_add') }}</span>
                            </button>

                            <div id="quick-add-dropdown" class="qa-dropdown hidden" role="menu">
                                @foreach ($quickActions as $action)
                                    <a href="{{ $action['route'] }}" class="qa-item" role="menuitem">
                                        <span class="qa-item-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $action['icon'] !!}</svg>
                                        </span>
                                        <span>{{ $action['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

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
                        <button
                            type="button"
                            id="theme-toggle"
                            class="theme-toggle-btn inline-flex rounded-full border border-[var(--app-border)] px-1.5 py-1 transition hover:opacity-90"
                            aria-label="Toggle dark and light mode"
                        >
                            <span class="theme-icon theme-icon-sun" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="4"></circle>
                                    <path d="M12 2v2.5M12 19.5V22M4.93 4.93l1.77 1.77M17.3 17.3l1.77 1.77M2 12h2.5M19.5 12H22M4.93 19.07 6.7 17.3M17.3 6.7l1.77-1.77"></path>
                                </svg>
                            </span>
                            <span class="theme-toggle-track" aria-hidden="true">
                                <span class="theme-toggle-thumb">
                                    <svg class="theme-toggle-thumb-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="4"></circle>
                                        <path d="M12 2v2.5M12 19.5V22M4.93 4.93l1.77 1.77M17.3 17.3l1.77 1.77M2 12h2.5M19.5 12H22M4.93 19.07 6.7 17.3M17.3 6.7l1.77-1.77"></path>
                                    </svg>
                                    <svg class="theme-toggle-thumb-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"></path>
                                    </svg>
                                </span>
                            </span>
                            <span class="theme-icon theme-icon-moon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"></path>
                                </svg>
                            </span>
                        </button>

                        {{-- Branch switcher: owners can switch; staff see a static badge --}}
                        @if (!$isSuperAdmin && $tenantBranches->count() > 0 && !$isOwnerBranchCtrl && $selectedBranch)
                        <div class="flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-xs font-medium"
                             style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text-muted)">
                            <svg class="h-3.5 w-3.5" style="color:var(--app-brand)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/></svg>
                            <span class="hidden md:inline">{{ $selectedBranch->name }}</span>
                        </div>
                        @endif
                        @if (!$isSuperAdmin && $isOwnerBranchCtrl && $tenantBranches->count() > 0)
                        <div class="relative" id="branch-sw-wrap">
                            <button
                                type="button"
                                id="branch-sw-btn"
                                class="bs-trigger"
                                aria-haspopup="true"
                                aria-expanded="false"
                            >
                                <span class="bs-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M15 9h.01"/><path d="M15 13h.01"/></svg>
                                </span>
                                <span class="bs-label hidden md:inline">
                                    {{ $selectedBranch ? $selectedBranch->name : 'All branches' }}
                                </span>
                                <svg class="bs-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                            </button>

                            <div id="branch-sw-dropdown" class="bs-dropdown hidden" role="menu">
                                <p class="bs-dropdown-heading">Switch branch</p>

                                <form method="POST" action="{{ route('tenant.switch-branch') }}">
                                    @csrf
                                    <input type="hidden" name="branch_id" value="all">
                                    <button type="submit" class="bs-option {{ !$selectedBranchId ? 'bs-option-active' : '' }}" role="menuitem">
                                        <span class="bs-option-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                                        </span>
                                        <span class="flex-1 text-left">All branches</span>
                                        @if (!$selectedBranchId)
                                            <svg class="h-3.5 w-3.5 text-[var(--app-brand)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                                        @endif
                                    </button>
                                </form>

                                @foreach ($tenantBranches as $b)
                                    <form method="POST" action="{{ route('tenant.switch-branch') }}">
                                        @csrf
                                        <input type="hidden" name="branch_id" value="{{ $b->id }}">
                                        <button type="submit" class="bs-option {{ $selectedBranchId === $b->id ? 'bs-option-active' : '' }}" role="menuitem">
                                            <span class="bs-option-icon">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M15 9h.01"/><path d="M15 13h.01"/></svg>
                                            </span>
                                            <span class="flex-1 text-left">
                                                {{ $b->name }}
                                                @if ($b->is_primary)
                                                    <span class="bs-primary-tag">Primary</span>
                                                @endif
                                            </span>
                                            @if ($selectedBranchId === $b->id)
                                                <svg class="h-3.5 w-3.5 text-[var(--app-brand)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                                            @endif
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- User profile dropdown --}}
                        <div class="relative" id="user-menu-wrap">
                            <button
                                type="button"
                                id="user-menu-btn"
                                class="flex items-center gap-2.5 rounded-2xl border border-[var(--app-border)] px-3 py-2 text-sm transition hover:opacity-90 app-panel-strong"
                                aria-haspopup="true"
                                aria-expanded="false"
                            >
                                <span class="app-brand-soft app-brand-text inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold">
                                    {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                                </span>
                                <span class="hidden font-medium md:inline">{{ $user?->name }}</span>
                                <svg class="h-3.5 w-3.5 app-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                            </button>

                            <div
                                id="user-menu-dropdown"
                                class="app-panel-strong absolute right-0 top-full z-50 mt-2 hidden w-56 rounded-2xl border border-[var(--app-border)] p-1 shadow-xl"
                                role="menu"
                            >
                                <div class="px-3 py-2.5 border-b border-[var(--app-border)] mb-1">
                                    <p class="text-sm font-semibold truncate">{{ $user?->name }}</p>
                                    <p class="text-xs app-muted mt-0.5 truncate">{{ $user?->email }}</p>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm transition hover:bg-red-500/10 hover:text-red-400"
                                        role="menuitem"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                        {{ __('common.logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="flex min-h-[calc(100vh-76px)] w-full flex-col lg:flex-row">
                <aside class="app-sidebar border-b px-4 py-4 backdrop-blur lg:min-h-[calc(100vh-76px)] lg:w-[280px] lg:border-b-0 lg:border-r lg:overflow-y-auto">
                    @if ($isSuperAdmin)
                        <nav class="mt-8 grid gap-2 text-sm">
                            @foreach ($adminLinks as $link)
                                <a
                                    href="{{ route($link['route']) }}"
                                    class="{{ request()->routeIs($link['route']) ? 'bg-[var(--app-brand)] text-slate-950' : 'app-panel-strong hover:opacity-90' }} flex items-center gap-3 rounded-2xl border px-4 py-3 font-medium transition"
                                >
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full {{ request()->routeIs($link['route']) ? 'bg-slate-950/15' : 'app-brand-soft app-brand-text' }}">
                                        <span class="h-4 w-4">{!! $icon($link['icon']) !!}</span>
                                    </span>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>
                    @else
                        <nav class="tenant-nav mt-2 text-sm">
                            @foreach ($tenantSections as $section)
                                <div class="tenant-nav-block {{ $loop->first ? '' : '' }}">
                                    @if ($section['heading'])
                                        <p class="tenant-nav-heading">{{ $section['heading'] }}</p>
                                    @endif

                                    <div class="grid gap-0.5">
                                        @foreach ($section['items'] as $item)
                                            @php
                                                $childItems = $item['children'] ?? [];
                                                $hasChildren = !empty($childItems);
                                                $childActive = collect($childItems)->contains(fn (array $child): bool => ($child['active'] ?? false) || (($child['slug'] ?? null) === $currentTenantSlug));
                                                $active = ($item['active'] ?? false) || $childActive || $isTenantItemActive($item['slug'] ?? null, $childItems);
                                            @endphp
                                            <div class="tenant-nav-entry">
                                                @if ($hasChildren)
                                                    <button
                                                        type="button"
                                                        data-nav-toggle
                                                        aria-expanded="{{ $active ? 'true' : 'false' }}"
                                                        class="{{ $active ? 'tenant-nav-item-active' : 'tenant-nav-item' }} flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left transition"
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
                                                        <span class="tenant-nav-chevron" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                                        </span>
                                                    </button>
                                                @else
                                                    <a
                                                        href="{{ $item['route'] }}"
                                                        class="{{ $active ? 'tenant-nav-item-active' : 'tenant-nav-item' }} flex items-center gap-2.5 rounded-xl px-3 py-2 transition"
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
                                                @endif

                                                @if ($hasChildren)
                                                    <div class="tenant-nav-children {{ $active ? 'tenant-nav-children-open' : '' }}">
                                                        @foreach ($childItems as $child)
                                                            <a
                                                                href="{{ $child['route'] }}"
                                                                class="{{ (($child['active'] ?? false) || ($child['slug'] ?? null) === $currentTenantSlug) ? 'tenant-nav-child-active' : 'tenant-nav-child' }}"
                                                            >
                                                                <span class="tenant-nav-child-dot" aria-hidden="true"></span>
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

                </aside>

                <main class="flex-1 px-4 py-6 lg:px-8 lg:py-8">
                    @isset($headerAction)
                        <div class="mb-5 flex justify-end">{{ $headerAction }}</div>
                    @endisset

                    @if (session('status'))
                        <div id="flash-status" class="flash-msg mb-6 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div id="flash-error" class="flash-msg mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        <script>
            const themeToggle = document.getElementById('theme-toggle');
            const quickAddBtn = document.getElementById('quick-add-btn');
            const quickAddDropdown = document.getElementById('quick-add-dropdown');

            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const nextTheme = document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';
                    document.documentElement.dataset.theme = nextTheme;
                    localStorage.setItem('gymos-theme', nextTheme);
                });
            }

            // Branch switcher dropdown
            const bsBtn      = document.getElementById('branch-sw-btn');
            const bsDropdown = document.getElementById('branch-sw-dropdown');
            const closeTopbarMenus = () => {
                bsDropdown?.classList.add('hidden');
                bsBtn?.setAttribute('aria-expanded', 'false');
                quickAddDropdown?.classList.add('hidden');
                quickAddBtn?.setAttribute('aria-expanded', 'false');
                userMenuDropdown?.classList.add('hidden');
                userMenuBtn?.setAttribute('aria-expanded', 'false');
            };
            if (bsBtn && bsDropdown) {
                bsBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isOpen = !bsDropdown.classList.contains('hidden');
                    quickAddDropdown?.classList.add('hidden');
                    quickAddBtn?.setAttribute('aria-expanded', 'false');
                    bsDropdown.classList.toggle('hidden', isOpen);
                    bsBtn.setAttribute('aria-expanded', String(!isOpen));
                    userMenuDropdown?.classList.add('hidden');
                    userMenuBtn?.setAttribute('aria-expanded', 'false');
                });
                bsDropdown.addEventListener('click', (e) => e.stopPropagation());
            }

            if (quickAddBtn && quickAddDropdown) {
                quickAddBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isOpen = !quickAddDropdown.classList.contains('hidden');
                    bsDropdown?.classList.add('hidden');
                    bsBtn?.setAttribute('aria-expanded', 'false');
                    userMenuDropdown?.classList.add('hidden');
                    userMenuBtn?.setAttribute('aria-expanded', 'false');
                    quickAddDropdown.classList.toggle('hidden', isOpen);
                    quickAddBtn.setAttribute('aria-expanded', String(!isOpen));
                });
                quickAddDropdown.addEventListener('click', (e) => e.stopPropagation());
            }

            // User profile dropdown
            const userMenuBtn = document.getElementById('user-menu-btn');
            const userMenuDropdown = document.getElementById('user-menu-dropdown');
            if (userMenuBtn && userMenuDropdown) {
                userMenuBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    bsDropdown?.classList.add('hidden');
                    bsBtn?.setAttribute('aria-expanded', 'false');
                    quickAddDropdown?.classList.add('hidden');
                    quickAddBtn?.setAttribute('aria-expanded', 'false');
                    const isOpen = !userMenuDropdown.classList.contains('hidden');
                    userMenuDropdown.classList.toggle('hidden', isOpen);
                    userMenuBtn.setAttribute('aria-expanded', String(!isOpen));
                });
                userMenuDropdown.addEventListener('click', (e) => e.stopPropagation());
            }

            document.addEventListener('click', closeTopbarMenus);

            // Auto-dismiss flash messages after 10 s with a fade-out
            document.querySelectorAll('.flash-msg').forEach(el => {
                el.style.maxHeight = el.scrollHeight + 'px'; // capture current height for collapse animation
                const DELAY = 5000;
                const FADE  = 400;
                const timer = setTimeout(() => {
                    el.style.transition = `opacity ${FADE}ms ease, margin-bottom ${FADE}ms ease, max-height ${FADE}ms ease`;
                    el.style.opacity        = '0';
                    el.style.marginBottom   = '0';
                    el.style.maxHeight      = '0';
                    el.style.overflow       = 'hidden';
                    setTimeout(() => el.remove(), FADE);
                }, DELAY);
                // Cancel auto-dismiss if user hovers (they're reading it)
                el.addEventListener('mouseenter', () => clearTimeout(timer));
            });

            document.querySelectorAll('[data-nav-toggle]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const expanded = btn.getAttribute('aria-expanded') === 'true';
                    const children = btn.nextElementSibling;
                    btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                    if (children) {
                        children.classList.toggle('tenant-nav-children-open', !expanded);
                    }
                });
            });
        </script>
        @stack('scripts')
    </body>
</html>
