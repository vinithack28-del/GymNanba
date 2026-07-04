<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const page = usePage();
const props = defineProps({
    title: String,
    headerAction: Object,
});

const user = computed(() => page.props.auth?.user);
const tenant = computed(() => page.props.auth?.user?.tenant);
const isSuperAdmin = computed(() => user.value?.role === 'super_admin');
const isGymOwner = computed(() => user.value?.role === 'tenant_owner');
const isStaffMember = computed(() => user.value?.role === 'staff');
const isPosRole = computed(() => user.value?.role === 'pos');
const translations = computed(() => page.props.translations?.common || {});

const t = (key, fallback = '') => {
    return key.split('.').reduce((value, part) => value?.[part], translations.value) || fallback;
};

const portalTitle = computed(() => isSuperAdmin.value ? 'GymNanba Platform' : (tenant.value?.gym_name || 'GymNanba'));
const portalLanguages = computed(() => page.props.portalLanguages || []);
const branchContext = computed(() => page.props.branchContext || null);
const tenantBranches = computed(() => branchContext.value?.branches || []);
const ownerCanSwitchBranch = computed(() => !!branchContext.value?.ownerCanSwitch);
const selectedBranchId = computed(() => branchContext.value?.selectedBranchId ?? null);
const selectedBranchName = computed(() => branchContext.value?.selectedBranchName || t('layout.all_branches', 'All branches'));

const theme = ref(localStorage.getItem('gymos-theme') || 'dark');
const quickAddOpen = ref(false);
const branchSwitcherOpen = ref(false);
const userMenuOpen = ref(false);
const localeForm = useForm({
    locale_code: page.props.locale || user.value?.preferred_language || 'en-IN',
});
const branchForm = useForm({
    branch_id: '',
});

const userMenuRef = ref(null);
const quickAddRef = ref(null);
const branchSwitcherRef = ref(null);
const flashStatusVisible = ref(false);
const flashErrorVisible = ref(false);
let flashStatusTimer = null;
let flashErrorTimer = null;

const navSections = ref([]);
const expandedSections = ref({});

const normalizePath = (value) => {
    if (!value) return '/';

    try {
        if (value.startsWith('http://') || value.startsWith('https://')) {
            return new URL(value).pathname || '/';
        }
    } catch {
        return '/';
    }

    return value.startsWith('/') ? value : `/${value}`;
};

const currentPath = computed(() => {
    if (page.url) {
        return normalizePath(page.url);
    }

    if (typeof window !== 'undefined') {
        return normalizePath(window.location.pathname);
    }

    return normalizePath(page.props.ziggy?.location);
});

const isPathActive = (path) => currentPath.value === path || currentPath.value.startsWith(`${path}/`);

onMounted(() => {
    document.documentElement.dataset.theme = theme.value;
    
    // Click outside handler
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.clearTimeout(flashStatusTimer);
    window.clearTimeout(flashErrorTimer);
});

const showTimedFlash = (kind, message) => {
    const visibleRef = kind === 'status' ? flashStatusVisible : flashErrorVisible;
    const currentTimer = kind === 'status' ? flashStatusTimer : flashErrorTimer;

    window.clearTimeout(currentTimer);
    visibleRef.value = Boolean(message);

    const nextTimer = message
        ? window.setTimeout(() => {
            visibleRef.value = false;
        }, 5000)
        : null;

    if (kind === 'status') {
        flashStatusTimer = nextTimer;
    } else {
        flashErrorTimer = nextTimer;
    }
};

watch(
    () => page.props.flash?.status,
    (message) => showTimedFlash('status', message),
    { immediate: true },
);

watch(
    () => page.props.flash?.error,
    (message) => showTimedFlash('error', message),
    { immediate: true },
);

const handleClickOutside = (event) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
        userMenuOpen.value = false;
    }
    if (quickAddRef.value && !quickAddRef.value.contains(event.target)) {
        quickAddOpen.value = false;
    }
    if (branchSwitcherRef.value && !branchSwitcherRef.value.contains(event.target)) {
        branchSwitcherOpen.value = false;
    }
};

const toggleTheme = () => {
    theme.value = theme.value === 'light' ? 'dark' : 'light';
    document.documentElement.dataset.theme = theme.value;
    localStorage.setItem('gymos-theme', theme.value);
};

const updateLanguage = () => {
    localeForm.post('/language', {
        preserveScroll: true,
    });
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

const switchBranch = (branchId) => {
    branchForm.branch_id = branchId;
    branchForm.post('/switch-branch', {
        preserveScroll: true,
        onFinish: () => {
            branchSwitcherOpen.value = false;
        },
    });
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
    const path = currentPath.value;
    const isAdminContext = path.startsWith('/admin');
    
    if (isSuperAdmin.value && isAdminContext) {
        navSections.value = [
            {
                heading: null,
                items: [
                    { label: t('nav.dashboard', 'Dashboard'), route: '/admin/dashboard', icon: 'grid', active: isPathActive('/admin/dashboard') },
                    { label: t('nav.tenants', 'Tenants'), route: '/admin/tenants', icon: 'building', active: isPathActive('/admin/tenants') },
                    { label: t('nav.plans', 'Plans'), route: '/admin/plans', icon: 'tag', active: isPathActive('/admin/plans') },
                    { label: t('nav.subscriptions', 'Subscriptions'), route: '/admin/subscriptions', icon: 'stack', active: isPathActive('/admin/subscriptions') },
                    { label: t('nav.invoices', 'Invoices'), route: '/admin/invoices', icon: 'wallet', active: isPathActive('/admin/invoices') },
                    { label: t('nav.audit_log', 'Audit Log'), route: '/admin/audit-log', icon: 'activity', active: isPathActive('/admin/audit-log') },
                    { label: t('nav.settings', 'Settings'), route: '/admin/settings', icon: 'settings', active: isPathActive('/admin/settings') },
                ]
            }
        ];
    } else {
        let sections = [
            {
                heading: null,
                items: [
                    { label: 'Dashboard', route: '/dashboard', icon: 'grid', active: isPathActive('/dashboard'), permission: 'dashboard.view' },
                ],
            },
            {
                heading: 'Members',
                items: [
                    { label: 'Members', route: '/members', icon: 'users', active: isPathActive('/members'), permission: 'members.view|members.add|members.edit|members.delete' },
                    { label: 'Membership Plans', route: '/plans', icon: 'card', active: isPathActive('/plans'), permission: 'members.view|members.add|members.edit|members.delete' },
                    { label: 'Renewals Due', route: '/renewals', icon: 'clock', active: isPathActive('/renewals'), permission: 'renewals.view' },
                    { label: 'Attendance', route: '/attendance/checkins', icon: 'scan', active: isPathActive('/attendance'), permission: 'attendance.check_in|attendance.view_log' },
                    { label: 'Walk-ins', route: '/walkins', icon: 'walkin', active: isPathActive('/walkins'), permission: 'attendance.check_in' },
                ],
            },
            {
                heading: 'Operations',
                items: [
                    { 
                        label: 'Classes & Schedules', 
                        route: '/classes/timetable', 
                        icon: 'calendar', 
                        active: isPathActive('/classes'),
                        permission: 'classes.view_timetable|classes.manage|classes.book',
                        children: [
                            { label: 'Timetable', route: '/classes/timetable', permission: 'classes.view_timetable|classes.manage|classes.book', active: isPathActive('/classes/timetable') },
                            { label: 'Book a Class', route: '/classes/book', permission: 'classes.book', active: isPathActive('/classes/book') },
                            { label: 'Trainers', route: '/classes/trainers', permission: 'classes.view_timetable|classes.manage|classes.book', active: isPathActive('/classes/trainers') },
                        ],
                    },
                    { label: 'Branches', route: '/branches', icon: 'office', active: isPathActive('/branches'), permission: 'branches.view|branches.manage' },
                    { label: 'Equipment', route: '/equipment', icon: 'equipment', active: isPathActive('/equipment'), permission: 'equipment.view' },
                    { label: 'Lockers', route: '/lockers', icon: 'locker', active: isPathActive('/lockers'), permission: 'locker.view|locker.assign|locker.add|locker.edit|locker.delete' },
                    { 
                        label: 'Staff', 
                        route: '/staff', 
                        icon: 'team', 
                        active: isPathActive('/staff'),
                        permission: 'staff.view|staff.manage',
                        children: [
                            { label: 'All Staff', route: '/staff', permission: 'staff.view|staff.manage', active: isPathActive('/staff') && !isPathActive('/staff/attendance') && !isPathActive('/staff/roles') },
                            { label: 'Roles & Permissions', route: '/staff/roles', permission: 'staff.view|staff.manage', ownerOnly: true, active: isPathActive('/staff/roles') },
                            { label: 'Staff Attendance', route: '/staff/attendance', permission: 'staff.view|staff.manage', active: isPathActive('/staff/attendance') },
                        ],
                    },
                ],
            },
            {
                heading: 'Assess',
                items: [
                    { 
                        label: 'Assess', 
                        route: '/assess/report', 
                        icon: 'chart', 
                        active: isPathActive('/assess'),
                        permission: 'assessment_report.view|parq.view|nutrition.view|body_metrics.view|posture.view|balance.view|vitals.view|fitness.view|goal_forecasting.view',
                        children: [
                            { label: 'Assessment Report', route: '/assess/report', permission: 'assessment_report.view', active: isPathActive('/assess/report') },
                            { label: 'PARQ', route: '/assess/questionnaire', permission: 'parq.view', active: isPathActive('/assess/questionnaire') },
                            { label: 'Nutrition', route: '/assess/nutrition', permission: 'nutrition.view', active: isPathActive('/assess/nutrition') },
                            { label: 'Body Metrics', route: '/assess/body-metrics', permission: 'body_metrics.view', active: isPathActive('/assess/body-metrics') },
                            { label: 'Posture', route: '/assess/posture', permission: 'posture.view', active: isPathActive('/assess/posture') },
                            { label: 'Balance', route: '/assess/balance', permission: 'balance.view', active: isPathActive('/assess/balance') },
                            { label: 'Vitals', route: '/assess/vitals', permission: 'vitals.view', active: isPathActive('/assess/vitals') },
                            { label: 'Fitness', route: '/assess/fitness', permission: 'fitness.view', active: isPathActive('/assess/fitness') },
                            { label: 'Goal Forecasting', route: '/assess/goal-forecasting', permission: 'goal_forecasting.view', active: isPathActive('/assess/goal-forecasting') },
                        ],
                    },
                ],
            },
            {
                heading: 'Finance',
                items: [
                    { label: 'Payments', route: '/payments/history', icon: 'wallet', active: isPathActive('/payments'), permission: 'payments.collect|payments.history|payments.void' },
                    { label: 'Invoices', route: '/invoices', icon: 'receipt', active: isPathActive('/invoices'), permission: 'invoices.view|invoices.manage' },
                    { 
                        label: 'POS Store', 
                        route: '/pos/sales', 
                        icon: 'cart', 
                        active: isPathActive('/pos'),
                        permission: 'pos.billing|pos.products_stock_view|pos.manage_products',
                        children: [
                            { label: 'Products', route: '/pos/products', permission: 'pos.products_stock_view|pos.manage_products', active: isPathActive('/pos/products') },
                            { label: 'Sales', route: '/pos/sales', permission: 'pos.billing', active: isPathActive('/pos/sales') },
                            { label: 'Stock', route: '/pos/stock', permission: 'pos.products_stock_view|pos.manage_products', active: isPathActive('/pos/stock') },
                        ],
                    },
                    { label: 'Expenses', route: '/expenses', icon: 'doc', active: isPathActive('/expenses'), permission: 'expenses.view|expenses.manage' },
                ],
            },
            {
                heading: 'Insights',
                items: [
                    { 
                        label: 'Reports', 
                        route: '/reports', 
                        icon: 'chart', 
                        active: isPathActive('/reports'),
                        permission: 'reports.view|reports.revenue_only|reports.branch_only|reports.own_data',
                        children: [
                            { label: 'Revenue Report', route: '/reports/revenue', permission: 'reports.view|reports.revenue_only', active: isPathActive('/reports/revenue') },
                            { label: 'Member Report', route: '/reports/members', permission: 'reports.view|reports.own_data', active: isPathActive('/reports/members') },
                            { label: 'Attendance Report', route: '/reports/attendance', permission: 'reports.view|reports.branch_only', active: isPathActive('/reports/attendance') },
                            { label: 'Staff Report', route: '/reports/staff', permission: 'reports.view', active: isPathActive('/reports/staff') },
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
                        route: '/settings/profile', 
                        icon: 'settings', 
                        active: isPathActive('/settings'),
                        ownerOnly: true,
                        children: [
                            { label: 'Profile', route: '/settings/profile', active: isPathActive('/settings/profile') },
                            { label: 'Account', route: '/settings/account', active: isPathActive('/settings/account') },
                            { label: 'Integrations', route: '/settings/integrations', active: isPathActive('/settings/integrations') },
                            { label: 'Language', route: '/settings/language', active: isPathActive('/settings/language') },
                            { label: 'Subscription', route: '/settings/subscription', active: isPathActive('/settings/subscription') },
                            { label: 'Data', route: '/settings/data', active: isPathActive('/settings/data') },
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

    navSections.value.forEach((section, sIdx) => {
        section.items.forEach((item, iIdx) => {
            if (!item.children || expandedSections.value[`${sIdx}-${iIdx}`] !== undefined) {
                return;
            }

            expandedSections.value[`${sIdx}-${iIdx}`] = item.active || item.children.some((child) => child.active);
        });
    });
};

watch([currentPath, translations], () => {
    initNavigation();
}, { immediate: true });

const checkPermission = (permissionString) => {
    if (!user.value || !permissionString) return true;
    // For now, return true for all permissions since we don't have the permission checking logic
    // This can be enhanced later if permissions are passed from the backend
    return true;
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
        { label: t('quick_actions.add_member', 'Add Member'), route: '/members/create', icon: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/>' },
        { label: t('quick_actions.add_enquiry', 'Add Enquiry'), route: '/walkins?purpose=inquiry', icon: '<path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/>' },
        { label: t('quick_actions.member_attendance', 'Member Attendance'), route: '/attendance/checkins', icon: '<path d="M3 12h4l3 8 4-16 3 8h4"/>' },
        { label: t('quick_actions.staff_attendance', 'Staff Attendance'), route: '/staff/attendance', icon: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M17 11l2 2 4-4"/>' },
    ];
});
</script>

<template>
    <div class="min-h-screen app-theme-shell">
        <Head :title="`${title} | GymNanba`" />
        
        <header class="sticky top-0 z-30 border-b app-topbar px-3 py-1.5 backdrop-blur lg:px-4">
            <div class="flex min-h-9 w-full items-center justify-between gap-2.5">
                <div class="flex min-w-0 items-center gap-2">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden rounded-md bg-orange-500">
                        <span class="text-xs font-bold text-white">G</span>
                    </div>
                    <div class="min-w-0">
                        <h1 class="truncate text-sm font-semibold">{{ portalTitle }}</h1>
                    </div>
                </div>

                <div class="flex min-w-0 items-center gap-1.5 sm:gap-2">
                    <div v-if="quickActions.length > 0" class="relative" ref="quickAddRef">
                        <button @click="toggleQuickAdd" class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-orange-500 px-2.5 text-sm font-bold text-slate-950 transition hover:opacity-90">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                            <span class="hidden md:inline">{{ t('quick_add', 'Quick Add') }}</span>
                        </button>
                        <div v-if="quickAddOpen" class="absolute right-0 top-full z-50 mt-2 w-52 rounded-xl border app-panel-strong p-1 shadow-xl">
                            <a v-for="action in quickActions" :key="action.route" :href="action.route" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/5">
                                <span class="text-orange-400">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" v-html="action.icon"></svg>
                                </span>
                                {{ action.label }}
                            </a>
                        </div>
                    </div>

                    <form v-if="portalLanguages.length > 0" @submit.prevent="updateLanguage" class="hidden md:block">
                        <label class="sr-only" for="portal-locale">{{ t('language_selector', 'Language') }}</label>
                        <select
                            id="portal-locale"
                            v-model="localeForm.locale_code"
                            @change="updateLanguage"
                            class="h-8 rounded-lg border app-panel px-2.5 text-sm outline-none"
                        >
                            <option v-for="language in portalLanguages" :key="language.locale_code" :value="language.locale_code">
                                {{ language.display_name }}
                            </option>
                        </select>
                    </form>

                    <button @click="toggleTheme" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border app-panel text-sm text-slate-400 transition hover:opacity-90" :title="t('layout.toggle_theme', 'Toggle theme')" :aria-label="t('layout.toggle_theme', 'Toggle theme')">
                        <svg v-if="theme === 'dark'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
                        <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="4"/><path d="M12 2v2.5M12 19.5V22M4.93 4.93l1.77 1.77M17.3 17.3l1.77 1.77M2 12h2.5M19.5 12H22M4.93 19.07 6.7 17.3M17.3 6.7l1.77-1.77"/></svg>
                    </button>

                    <div v-if="!isSuperAdmin && tenantBranches.length > 0">
                        <div v-if="ownerCanSwitchBranch" class="relative" ref="branchSwitcherRef">
                            <button @click="toggleBranchSwitcher" class="inline-flex h-8 items-center gap-1.5 rounded-lg border app-panel px-2.5 text-sm text-slate-300 transition hover:opacity-90">
                                <svg class="h-4 w-4 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M15 9h.01"/><path d="M15 13h.01"/></svg>
                                <span class="hidden md:inline">{{ selectedBranchName }}</span>
                                <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                            </button>
                            <div v-if="branchSwitcherOpen" class="absolute right-0 top-full z-50 mt-2 w-60 rounded-xl border app-panel-strong p-1 shadow-xl">
                                <p class="px-3 py-1.5 text-[10px] font-semibold uppercase tracking-widest text-slate-500">{{ t('layout.switch_branch', 'Switch Branch') }}</p>
                                <button @click="switchBranch('all')" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition hover:bg-white/5" :class="selectedBranchId === null ? 'text-orange-400' : 'text-slate-300'">
                                    <span class="text-orange-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                                    </span>
                                    <span class="flex-1 text-left">{{ t('layout.all_branches', 'All branches') }}</span>
                                    <svg v-if="selectedBranchId === null" class="h-4 w-4 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                                </button>
                                <button v-for="branch in tenantBranches" :key="branch.id" @click="switchBranch(branch.id)" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition hover:bg-white/5" :class="selectedBranchId === branch.id ? 'text-orange-400' : 'text-slate-300'">
                                    <span class="text-orange-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M15 9h.01"/><path d="M15 13h.01"/></svg>
                                    </span>
                                    <span class="flex-1 text-left">
                                        {{ branch.name }}
                                        <span v-if="branch.is_primary" class="ml-2 rounded-full bg-orange-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-orange-400">{{ t('layout.primary', 'Primary') }}</span>
                                    </span>
                                    <svg v-if="selectedBranchId === branch.id" class="h-4 w-4 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                                </button>
                            </div>
                        </div>
                        <div v-else class="inline-flex h-8 items-center gap-1.5 rounded-lg border app-panel px-2.5 text-sm text-slate-300">
                            <svg class="h-4 w-4 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/></svg>
                            <span class="hidden md:inline">{{ selectedBranchName }}</span>
                        </div>
                    </div>

                    <div class="relative" ref="userMenuRef">
                        <button @click="toggleUserMenu" class="flex h-8 items-center gap-1.5 rounded-lg border app-panel px-2 text-sm transition hover:opacity-90 lg:gap-2 lg:px-2.5">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-orange-500/20 text-xs font-bold text-orange-400">
                                {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                            </span>
                            <span class="hidden font-medium md:inline">{{ user?.name }}</span>
                            <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div v-if="userMenuOpen" class="absolute right-0 top-full z-50 mt-2 w-52 rounded-xl border app-panel-strong p-1 shadow-xl">
                            <div class="mb-1 border-b border-white/10 px-3 py-2">
                                <p class="text-sm font-semibold truncate">{{ user?.name }}</p>
                                <p class="text-xs text-slate-400 mt-0.5 truncate">{{ user?.email }}</p>
                            </div>
                            <form method="POST" action="/logout">
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-slate-300 transition hover:bg-red-500/10 hover:text-red-400">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    {{ t('logout', 'Logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex min-h-[calc(100vh-45px)] w-full flex-col lg:min-h-[calc(100vh-45px)] lg:flex-row">
            <aside class="border-b app-sidebar px-2 py-2 backdrop-blur lg:min-h-[calc(100vh-45px)] lg:w-[196px] xl:w-[208px] lg:border-b-0 lg:border-r lg:overflow-y-auto">
                <nav class="text-[12.5px]">
                    <div v-for="(section, sIdx) in navSections" :key="sIdx" class="mb-2">
                        <p v-if="section.heading" class="tenant-sidebar-heading mb-1 px-2 text-[9px] font-bold uppercase tracking-[0.14em]">{{ section.heading }}</p>
                        <div class="grid gap-0.5">
                            <div v-for="(item, iIdx) in section.items" :key="iIdx">
                                <div v-if="item.children">
                                    <div :class="item.active ? 'bg-orange-500 text-slate-950 shadow-sm shadow-orange-950/20' : 'tenant-sidebar-link hover:bg-white/5'" class="flex items-center gap-1 rounded-md px-1 py-0.5 transition">
                                        <Link :href="item.route" class="flex min-w-0 flex-1 items-center gap-1.5 rounded-md px-1 py-0.5">
                                            <span class="inline-flex h-5.5 w-5.5 items-center justify-center rounded-md" :class="item.active ? 'bg-slate-950/15' : 'bg-orange-500/10 text-orange-400'">
                                                <span class="h-3 w-3" v-html="getIcon(item.icon)"></span>
                                            </span>
                                            <span class="truncate font-medium">{{ item.label }}</span>
                                        </Link>
                                        <button type="button" @click="toggleNavSection(`${sIdx}-${iIdx}`)" class="inline-flex h-5.5 w-5.5 items-center justify-center rounded-md transition hover:bg-white/10">
                                            <svg class="h-3 w-3 transition-transform" :class="expandedSections[`${sIdx}-${iIdx}`] ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9l6 6 6-6"/></svg>
                                        </button>
                                    </div>
                                    <div v-show="expandedSections[`${sIdx}-${iIdx}`]" class="ml-7 mt-0.5 space-y-0.5">
                                        <Link v-for="(child, cIdx) in item.children" :key="cIdx" :href="child.route" :class="child.active ? 'bg-orange-500/10 text-orange-400' : 'tenant-sidebar-child-link hover:bg-white/5'" class="flex items-center gap-1.5 rounded-md px-1.5 py-0.5 text-[11.5px] transition">
                                            <span class="h-1 w-1 rounded-full" :class="child.active ? 'bg-orange-400' : 'bg-slate-600'"></span>
                                            {{ child.label }}
                                        </Link>
                                    </div>
                                </div>
                                <Link v-else :href="item.route" :class="item.active ? 'bg-orange-500 text-slate-950 shadow-sm shadow-orange-950/20' : 'tenant-sidebar-link hover:bg-white/5'" class="flex items-center gap-1.5 rounded-md px-2 py-1.5 transition">
                                    <span class="inline-flex h-5.5 w-5.5 items-center justify-center rounded-md" :class="item.active ? 'bg-slate-950/15' : 'bg-orange-500/10 text-orange-400'">
                                        <span class="h-3 w-3" v-html="getIcon(item.icon)"></span>
                                    </span>
                                    <span class="truncate font-medium">{{ item.label }}</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </nav>
            </aside>

            <main class="min-w-0 flex-1 overflow-auto px-4 py-4 lg:px-6 lg:py-5 xl:px-8 xl:py-6">
                <div v-if="headerAction" class="mb-4 flex justify-end">
                    <component :is="headerAction" />
                </div>

                <div v-if="flashStatusVisible && page.props.flash?.status" class="mb-4 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ page.props.flash.status }}
                </div>

                <div v-if="flashErrorVisible && page.props.flash?.error" class="mb-4 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                    {{ page.props.flash.error }}
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>

