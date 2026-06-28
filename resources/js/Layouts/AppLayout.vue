<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const page = usePage();
const props = defineProps({
    title: String,
    headerAction: Object,
});

const user = computed(() => page.props.auth?.user);
const tenant = computed(() => page.props.auth?.user?.tenant);
const isSuperAdmin = computed(() => user.value?.isSuperAdmin?.() ?? false);
const isGymOwner = computed(() => user.value?.isGymOwner?.() ?? false);
const isStaffMember = computed(() => user.value?.isStaffMember?.() ?? false);
const isPosRole = computed(() => user.value?.role === 'pos');

const portalTitle = computed(() => isSuperAdmin.value ? 'GymNanba Platform' : (tenant.value?.gym_name || 'GymNanba'));
const portalEyebrow = computed(() => isSuperAdmin.value ? 'Platform operations' : (tenant.value?.gym_name || 'GymNanba'));

const theme = ref(localStorage.getItem('gymos-theme') || 'dark');
const quickAddOpen = ref(false);
const branchSwitcherOpen = ref(false);
const userMenuOpen = ref(false);

const userMenuRef = ref(null);
const quickAddRef = ref(null);

const navSections = ref([]);
const expandedSections = ref({});

onMounted(() => {
    document.documentElement.dataset.theme = theme.value;
    initNavigation();
    
    // Click outside handler
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const handleClickOutside = (event) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
        userMenuOpen.value = false;
    }
    if (quickAddRef.value && !quickAddRef.value.contains(event.target)) {
        quickAddOpen.value = false;
    }
};

const toggleTheme = () => {
    theme.value = theme.value === 'light' ? 'dark' : 'light';
    document.documentElement.dataset.theme = theme.value;
    localStorage.setItem('gymos-theme', theme.value);
};

const toggleQuickAdd = () => {
    quickAddOpen.value = !quickAddOpen.value;
    branchSwitcherOpen.value = false;
    userMenuOpen.value = false;
};

const toggleBranchSwitcher = () => {
    branchSwitcherOpen.value = !branchSwitcherOpen.value;
    quickAddOpen.value = false;
    userMenuOpen.value = false;
};

const toggleUserMenu = () => {
    userMenuOpen.value = !userMenuOpen.value;
    quickAddOpen.value = false;
    branchSwitcherOpen.value = false;
};

const closeAllMenus = () => {
    quickAddOpen.value = false;
    branchSwitcherOpen.value = false;
    userMenuOpen.value = false;
};

const toggleNavSection = (key) => {
    expandedSections.value[key] = !expandedSections.value[key];
};

const initNavigation = () => {
    const currentPath = page.props.ziggy?.location || '';
    const isTenantContext = currentPath.startsWith('/tenant/');
    
    // SuperAdmin sees tenant menu when in tenant context, otherwise admin menu
    if (isSuperAdmin.value && !isTenantContext) {
        navSections.value = [
            { label: 'Dashboard', route: 'admin.dashboard', icon: 'grid' },
            { label: 'Tenants', route: 'admin.tenants.index', icon: 'building' },
            { label: 'Plans', route: 'admin.plans.index', icon: 'tag' },
            { label: 'Subscriptions', route: 'admin.subscriptions.index', icon: 'stack' },
            { label: 'Invoices', route: 'admin.invoices.index', icon: 'wallet' },
            { label: 'Audit Log', route: 'admin.audit-log.index', icon: 'activity' },
            { label: 'Settings', route: 'admin.settings.index', icon: 'settings' },
        ];
    } else {
        let sections = [
            {
                heading: null,
                items: [
                    { label: 'Dashboard', route: '/tenant/dashboard', icon: 'grid', active: page.props.ziggy?.location === '/tenant/dashboard', permission: 'dashboard.view' },
                ],
            },
            {
                heading: 'Members',
                items: [
                    { label: 'Members', route: '/tenant/members', icon: 'users', active: page.props.ziggy?.location?.startsWith('/tenant/members'), permission: 'members.view|members.add|members.edit|members.delete' },
                    { label: 'Membership Plans', route: '/tenant/plans', icon: 'card', active: page.props.ziggy?.location?.startsWith('/tenant/plans'), permission: 'members.view|members.add|members.edit|members.delete' },
                    { label: 'Renewals Due', route: '/tenant/renewals', icon: 'clock', active: page.props.ziggy?.location?.startsWith('/tenant/renewals'), permission: 'renewals.view' },
                    { label: 'Attendance', route: '/tenant/attendance/checkins', icon: 'scan', active: page.props.ziggy?.location?.startsWith('/tenant/attendance'), permission: 'attendance.check_in|attendance.view_log' },
                    { label: 'Walk-ins', route: '/tenant/attendance/walkins', icon: 'walkin', active: page.props.ziggy?.location?.includes('/tenant/attendance/walkins'), permission: 'attendance.check_in' },
                ],
            },
            {
                heading: 'Operations',
                items: [
                    { 
                        label: 'Classes & Schedules', 
                        route: '/tenant/classes/timetable', 
                        icon: 'calendar', 
                        active: page.props.ziggy?.location?.startsWith('/tenant/classes'),
                        permission: 'classes.view_timetable|classes.manage|classes.book',
                        children: [
                            { label: 'Timetable', route: '/tenant/classes/timetable', permission: 'classes.view_timetable|classes.manage|classes.book' },
                            { label: 'Book a Class', route: '/tenant/classes/book', permission: 'classes.book' },
                            { label: 'Trainers', route: '/tenant/classes/trainers', permission: 'classes.view_timetable|classes.manage|classes.book' },
                        ],
                    },
                    { label: 'Branches', route: '/tenant/branches', icon: 'office', active: page.props.ziggy?.location?.startsWith('/tenant/branches'), permission: 'branches.view|branches.manage' },
                    { label: 'Equipment', route: '/tenant/equipment', icon: 'equipment', active: page.props.ziggy?.location?.startsWith('/tenant/equipment'), permission: 'equipment.view' },
                    { label: 'Lockers', route: '/tenant/lockers', icon: 'locker', active: page.props.ziggy?.location?.startsWith('/tenant/lockers'), permission: 'locker.view|locker.assign|locker.add|locker.edit|locker.delete' },
                    { 
                        label: 'Staff', 
                        route: '/tenant/staff', 
                        icon: 'team', 
                        active: page.props.ziggy?.location?.startsWith('/tenant/staff'),
                        permission: 'staff.view|staff.manage',
                        children: [
                            { label: 'All Staff', route: '/tenant/staff', permission: 'staff.view|staff.manage' },
                            { label: 'Roles & Permissions', route: '/tenant/staff/roles', permission: 'staff.view|staff.manage', ownerOnly: true },
                            { label: 'Staff Attendance', route: '/tenant/staff/attendance', permission: 'staff.view|staff.manage' },
                        ],
                    },
                ],
            },
            {
                heading: 'Assess',
                items: [
                    { 
                        label: 'Assess', 
                        route: '/tenant/assess/report', 
                        icon: 'chart', 
                        active: page.props.ziggy?.location?.startsWith('/tenant/assess'),
                        permission: 'assessment_report.view|parq.view|nutrition.view|body_metrics.view|posture.view|balance.view|vitals.view|fitness.view|goal_forecasting.view',
                        children: [
                            { label: 'Assessment Report', route: '/tenant/assess/report', permission: 'assessment_report.view' },
                            { label: 'PARQ', route: '/tenant/assess/questionnaire', permission: 'parq.view' },
                            { label: 'Nutrition', route: '/tenant/assess/nutrition', permission: 'nutrition.view' },
                            { label: 'Body Metrics', route: '/tenant/assess/body-metrics', permission: 'body_metrics.view' },
                            { label: 'Posture', route: '/tenant/assess/posture', permission: 'posture.view' },
                            { label: 'Balance', route: '/tenant/assess/balance', permission: 'balance.view' },
                            { label: 'Vitals', route: '/tenant/assess/vitals', permission: 'vitals.view' },
                            { label: 'Fitness', route: '/tenant/assess/fitness', permission: 'fitness.view' },
                            { label: 'Goal Forecasting', route: '/tenant/assess/goal-forecasting', permission: 'goal_forecasting.view' },
                        ],
                    },
                ],
            },
            {
                heading: 'Finance',
                items: [
                    { label: 'Payments', route: '/tenant/payments/history', icon: 'wallet', active: page.props.ziggy?.location?.startsWith('/tenant/payments'), permission: 'payments.collect|payments.history|payments.void' },
                    { label: 'Invoices', route: '/tenant/invoices', icon: 'receipt', active: page.props.ziggy?.location?.startsWith('/tenant/invoices'), permission: 'invoices.view|invoices.manage' },
                    { 
                        label: 'POS Store', 
                        route: '/tenant/pos/sales', 
                        icon: 'cart', 
                        active: page.props.ziggy?.location?.startsWith('/tenant/pos'),
                        permission: 'pos.billing|pos.products_stock_view|pos.manage_products',
                        children: [
                            { label: 'Products', route: '/tenant/pos/products', permission: 'pos.products_stock_view|pos.manage_products' },
                            { label: 'Sales', route: '/tenant/pos/sales', permission: 'pos.billing' },
                            { label: 'Stock', route: '/tenant/pos/stock', permission: 'pos.products_stock_view|pos.manage_products' },
                        ],
                    },
                    { label: 'Expenses', route: '/tenant/expenses', icon: 'doc', active: page.props.ziggy?.location?.startsWith('/tenant/expenses'), permission: 'expenses.view|expenses.manage' },
                ],
            },
            {
                heading: 'Insights',
                items: [
                    { 
                        label: 'Reports', 
                        route: '/tenant/reports', 
                        icon: 'chart', 
                        active: page.props.ziggy?.location?.startsWith('/tenant/reports'),
                        permission: 'reports.view|reports.revenue_only|reports.branch_only|reports.own_data',
                        children: [
                            { label: 'Revenue Report', route: '/tenant/reports/revenue', permission: 'reports.view|reports.revenue_only' },
                            { label: 'Member Report', route: '/tenant/reports/members', permission: 'reports.view|reports.own_data' },
                            { label: 'Attendance Report', route: '/tenant/reports/attendance', permission: 'reports.view|reports.branch_only' },
                            { label: 'Staff Report', route: '/tenant/reports/staff', permission: 'reports.view' },
                        ],
                    },
                ],
            },
            {
                heading: 'Config',
                items: [
                    { label: 'Notifications', route: '#', icon: 'bell' },
                    { 
                        label: 'Settings', 
                        route: '/tenant/settings/profile', 
                        icon: 'settings', 
                        active: page.props.ziggy?.location?.startsWith('/tenant/settings'),
                        ownerOnly: true,
                        children: [
                            { label: 'Profile', route: '/tenant/settings/profile' },
                            { label: 'Account', route: '/tenant/settings/account' },
                            { label: 'Integrations', route: '/tenant/settings/integrations' },
                            { label: 'Language', route: '/tenant/settings/language' },
                            { label: 'Subscription', route: '/tenant/settings/subscription' },
                            { label: 'Data', route: '/tenant/settings/data' },
                        ],
                    },
                ],
            },
        ];

        // Permission-based filtering for staff members
        if (isStaffMember.value) {
            sections = sections.map(section => {
                section.items = section.items.filter(item => {
                    if (item.ownerOnly) return false;
                    if (!item.permission) return true;
                    return checkPermission(item.permission);
                });

                // Filter children
                section.items = section.items.map(item => {
                    if (item.children) {
                        item.children = item.children.filter(child => {
                            if (child.ownerOnly) return false;
                            if (!child.permission) return true;
                            return checkPermission(child.permission);
                        });
                    }
                    return item;
                });

                // Remove items with no children
                section.items = section.items.filter(item => {
                    if (!item.children) return true;
                    return item.children.length > 0;
                });

                return section;
            });

            // Remove empty sections
            sections = sections.filter(section => section.items.length > 0);
        }

        // POS role filtering - only show Sales and Stock under POS
        if (isPosRole.value) {
            sections = sections.map(section => {
                if (section.heading === 'Finance') {
                    section.items = section.items.map(item => {
                        if (item.label === 'POS Store' && item.children) {
                            item.children = item.children.filter(child => 
                                ['sales', 'stock'].includes(child.route.split('/').pop())
                            );
                        }
                        return item;
                    });
                }
                return section;
            });
        }

        navSections.value = sections;
    }
};

const checkPermission = (permissionString) => {
    if (!user.value || !permissionString) return true;
    const permissions = permissionString.split('|');
    return permissions.some(perm => user.value.canAccess?.(perm) ?? true);
};

const getIcon = (name) => {
    const icons = {
        grid: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>',
        building: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V7a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v14"/><path d="M9 21v-4h2v4"/><path d="M8 9h1"/><path d="M8 12h1"/><path d="M12 9h1"/><path d="M12 12h1"/><path d="M16 21h4V11a2 2 0 0 0-2-2h-2"/></svg>',
        tag: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 10 12 2H4v8l8 8 8-8Z"/><path d="M7.5 7.5h.01"/></svg>',
        stack: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 9 4.5-9 4.5-9-4.5L12 3Z"/><path d="m3 12 9 4.5 9-4.5"/><path d="m3 16.5 9 4.5 9-4.5"/></svg>',
        wallet: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M16 12h.01"/><path d="M3 9h18"/></svg>',
        activity: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12h4l3-7 4 14 3-7h4"/></svg>',
        settings: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1V21a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-.4-1 1.7 1.7 0 0 0-1-.6 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1-.4H3a2 2 0 1 1 0-4h.09a1.7 1.7 0 0 0 1-.4 1.7 1.7 0 0 0 .6-1 1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 .4 1 1.7 1.7 0 0 0 1 .6 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.25.3.46.65.6 1 .1.32.1.66 0 1-.14.35-.35.7-.6 1Z"/></svg>',
        users: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="3"/><path d="M20 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 4.13a4 4 0 0 1 0 7.75"/></svg>',
        card: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 7h6"/><path d="M9 11h6"/><path d="M9 15h4"/></svg>',
        clock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg>',
        scan: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7V5a1 1 0 0 1 1-1h2"/><path d="M17 4h2a1 1 0 0 1 1 1v2"/><path d="M20 17v2a1 1 0 0 1-1 1h-2"/><path d="M7 20H5a1 1 0 0 1-1-1v-2"/><path d="M7 12h10"/></svg>',
        calendar: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M3 11h18"/></svg>',
        office: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M15 9h.01"/><path d="M15 13h.01"/></svg>',
        team: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="7" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M14 20a5 5 0 0 1 7 0"/></svg>',
        receipt: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6"/><path d="M9 12h6"/></svg>',
        cart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/><path d="M3 4h2l2.4 10.5a1 1 0 0 0 1 .8h8.9a1 1 0 0 0 1-.76L20 7H7"/></svg>',
        doc: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M14 3v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>',
        chart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-7"/><path d="M22 20H2"/></svg>',
        bell: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 17H5.5a1.5 1.5 0 0 1-1.1-2.52C5.6 13.2 6 11.65 6 10V8a6 6 0 1 1 12 0v2c0 1.65.4 3.2 1.6 4.48A1.5 1.5 0 0 1 18.5 17H15"/><path d="M9.5 20a2.5 2.5 0 0 0 5 0"/></svg>',
        walkin: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="13" cy="4" r="1.5"/><path d="M8 20l2-5 3 3"/><path d="M14.5 9.5L16 12l3 1"/><path d="M11 9.5l-2 4 3 2.5"/><path d="M13 9l1.5-3"/></svg>',
        equipment: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6h4v4H6z"/><path d="M14 6h4v4h-4z"/><path d="M6 14h4v4H6z"/><path d="M14 14h4v4h-4z"/><path d="M10 8h4"/><path d="M8 10v4"/><path d="M16 10v4"/><path d="M10 16h4"/></svg>',
        locker: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="3" width="12" height="18" rx="2"/><circle cx="12" cy="12" r="1.25"/><path d="M12 8v2"/></svg>',
    };
    return icons[name] || icons.grid;
};

const quickActions = computed(() => {
    if (isSuperAdmin.value) return [];
    return [
        { label: 'Add Member', route: '/tenant/members/create', icon: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/>' },
        { label: 'Add Enquiry', route: '/tenant/attendance/walkins?purpose=inquiry', icon: '<path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/>' },
        { label: 'Member Attendance', route: '/tenant/attendance/checkins', icon: '<path d="M3 12h4l3 8 4-16 3 8h4"/>' },
        { label: 'Staff Attendance', route: '/tenant/staff/attendance', icon: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M17 11l2 2 4-4"/>' },
    ];
});
</script>

<template>
    <div class="min-h-screen bg-slate-900 text-slate-100">
        <Head :title="`${title} | GymNanba`" />
        
        <header class="sticky top-0 z-30 border-b border-white/10 bg-slate-900/80 px-4 py-4 backdrop-blur lg:px-6">
            <div class="flex w-full items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl overflow-hidden bg-orange-500">
                        <span class="text-xl font-bold text-white">G</span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.42em] text-orange-400">{{ portalEyebrow }}</p>
                        <h1 class="mt-1 text-lg font-semibold">{{ portalTitle }}</h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div v-if="quickActions.length > 0" class="relative" ref="quickAddRef">
                        <button @click="toggleQuickAdd" class="inline-flex items-center gap-2 rounded-full bg-orange-500 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:opacity-90">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                            <span class="hidden md:inline">Quick Add</span>
                        </button>
                        <div v-if="quickAddOpen" class="absolute right-0 top-full z-50 mt-2 w-56 rounded-2xl border border-white/10 bg-slate-800 p-1 shadow-xl">
                            <a v-for="action in quickActions" :key="action.route" :href="action.route" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/5">
                                <span class="text-orange-400">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" v-html="action.icon"></svg>
                                </span>
                                {{ action.label }}
                            </a>
                        </div>
                    </div>

                    <button @click="toggleTheme" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-slate-800 px-3 py-2 text-sm text-slate-400 transition hover:opacity-90">
                        <svg v-if="theme === 'dark'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
                        <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="4"/><path d="M12 2v2.5M12 19.5V22M4.93 4.93l1.77 1.77M17.3 17.3l1.77 1.77M2 12h2.5M19.5 12H22M4.93 19.07 6.7 17.3M17.3 6.7l1.77-1.77"/></svg>
                    </button>

                    <div class="relative" ref="userMenuRef">
                        <button @click="toggleUserMenu" class="flex items-center gap-2.5 rounded-2xl border border-white/10 bg-slate-800 px-3 py-2 text-sm transition hover:opacity-90">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-orange-500/20 text-xs font-bold text-orange-400">
                                {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                            </span>
                            <span class="hidden font-medium md:inline">{{ user?.name }}</span>
                            <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div v-if="userMenuOpen" class="absolute right-0 top-full z-50 mt-2 w-56 rounded-2xl border border-white/10 bg-slate-800 p-1 shadow-xl">
                            <div class="px-3 py-2.5 border-b border-white/10 mb-1">
                                <p class="text-sm font-semibold truncate">{{ user?.name }}</p>
                                <p class="text-xs text-slate-400 mt-0.5 truncate">{{ user?.email }}</p>
                            </div>
                            <form method="POST" action="/logout">
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-slate-300 transition hover:bg-red-500/10 hover:text-red-400">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex min-h-[calc(100vh-76px)] w-full flex-col lg:flex-row">
            <aside class="border-b border-white/10 bg-slate-900/50 px-4 py-4 backdrop-blur lg:min-h-[calc(100vh-76px)] lg:w-[280px] lg:border-b-0 lg:border-r lg:overflow-y-auto">
                <nav class="mt-2 text-sm">
                    <div v-for="(section, sIdx) in navSections" :key="sIdx" class="mb-4">
                        <p v-if="section.heading" class="mb-2 px-3 text-xs font-bold uppercase tracking-widest text-slate-500">{{ section.heading }}</p>
                        <div class="grid gap-1">
                            <div v-for="(item, iIdx) in section.items" :key="iIdx">
                                <div v-if="item.children">
                                    <button @click="toggleNavSection(`${sIdx}-${iIdx}`)" :class="item.active ? 'bg-orange-500 text-slate-950' : 'text-slate-400 hover:bg-white/5'" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left transition">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full" :class="item.active ? 'bg-slate-950/15' : 'bg-orange-500/10 text-orange-400'">
                                            <span class="h-4 w-4" v-html="getIcon(item.icon)"></span>
                                        </span>
                                        <span class="flex-1 font-medium">{{ item.label }}</span>
                                        <svg class="h-3.5 w-3.5 transition-transform" :class="expandedSections[`${sIdx}-${iIdx}`] ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9l6 6 6-6"/></svg>
                                    </button>
                                    <div v-show="expandedSections[`${sIdx}-${iIdx}`]" class="ml-4 mt-1 space-y-1">
                                        <a v-for="(child, cIdx) in item.children" :key="cIdx" :href="child.route" :class="child.active ? 'text-orange-400' : 'text-slate-500 hover:text-slate-300'" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition">
                                            <span class="h-1 w-1 rounded-full" :class="child.active ? 'bg-orange-400' : 'bg-slate-600'"></span>
                                            {{ child.label }}
                                        </a>
                                    </div>
                                </div>
                                <Link v-else :href="item.route" :class="item.active ? 'bg-orange-500 text-slate-950' : 'text-slate-400 hover:bg-white/5'" class="flex items-center gap-2.5 rounded-xl px-3 py-2 transition">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full" :class="item.active ? 'bg-slate-950/15' : 'bg-orange-500/10 text-orange-400'">
                                        <span class="h-4 w-4" v-html="getIcon(item.icon)"></span>
                                    </span>
                                    <span class="font-medium">{{ item.label }}</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </nav>
            </aside>

            <main class="flex-1 px-4 py-6 lg:px-8 lg:py-8">
                <div v-if="headerAction" class="mb-5 flex justify-end">
                    <component :is="headerAction" />
                </div>

                <div v-if="page.props.flash?.status" class="mb-6 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ page.props.flash.status }}
                </div>

                <div v-if="page.props.flash?.error" class="mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                    {{ page.props.flash.error }}
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>
