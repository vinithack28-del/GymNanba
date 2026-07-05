<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    lockers: Object,
    summary: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
    branches: {
        type: Array,
        default: () => [],
    },
    selectedBranchId: [String, Number, null],
    canAdd: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
});

const lockerRows = computed(() => props.lockers?.data || []);
const drawerOpen = ref(false);
const drawerMode = ref('add');
const selectedLocker = ref(null);
const actionError = ref('');
const filterSearch = ref(props.filters?.search || '');
const filterAvailability = ref(props.filters?.availability || '');
const filterStatus = ref(props.filters?.status || '');
const filterPerPage = ref(props.filters?.per_page || props.lockers?.per_page || 25);
let searchTimer = null;

const defaultBranchId = computed(() => props.selectedBranchId || (props.branches.length === 1 ? props.branches[0].id : ''));
const showBranchField = computed(() => !props.selectedBranchId && props.branches.length > 1);
const paginationLinks = computed(() => (props.lockers?.links || []).filter((link) => link.url || link.active));
const hasActiveFilters = computed(() => filterSearch.value || filterAvailability.value || filterStatus.value);

const form = useForm({
    branch_id: defaultBranchId.value,
    locker_number: '',
    status: 'active',
    location: '',
    notes: '',
});

const getStatusColor = (status) => {
    const colors = {
        active: { bg: 'rgba(34,197,94,0.12)', fg: '#22c55e' },
        maintenance: { bg: 'rgba(245,158,11,0.12)', fg: '#f59e0b' },
        inactive: { bg: 'rgba(148,163,184,0.12)', fg: '#94a3b8' },
    };
    return colors[status] || colors.inactive;
};

const rowAssignment = (locker) => locker?.currentAssignment || locker?.current_assignment || null;
const rowBranchName = (locker) => locker?.branch?.name || locker?.branch_name || '';
const drawerTitle = computed(() => drawerMode.value === 'edit' ? 'Edit Locker' : 'Add Locker');
const drawerSubtitle = computed(() => drawerMode.value === 'edit' ? 'Update locker number, location, and notes.' : 'Create an available active locker.');
const submitLabel = computed(() => {
    if (form.processing) {
        return drawerMode.value === 'edit' ? 'Saving...' : 'Adding...';
    }

    return drawerMode.value === 'edit' ? 'Save Changes' : 'Add Locker';
});

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const refreshLockers = () => {
    router.reload({
        only: ['lockers', 'summary', 'filters'],
        preserveScroll: true,
    });
};

const parseErrors = async (response) => {
    const fallback = 'Something went wrong. Please try again.';
    try {
        const payload = await response.json();
        if (payload?.errors) {
            return Object.fromEntries(
                Object.entries(payload.errors).map(([field, messages]) => [
                    field,
                    Array.isArray(messages) ? messages[0] : messages,
                ])
            );
        }
        return { general: payload?.message || fallback };
    } catch {
        return { general: fallback };
    }
};

const statusText = (status) => {
    const labels = {
        active: 'Active',
        inactive: 'Inactive',
        maintenance: 'Maintenance',
    };

    return labels[status] || status || 'Inactive';
};

const openDrawer = () => {
    drawerMode.value = 'add';
    selectedLocker.value = null;
    form.reset();
    form.clearErrors();
    actionError.value = '';
    form.branch_id = defaultBranchId.value;
    form.status = 'active';
    drawerOpen.value = true;
};

const openEditDrawer = (locker) => {
    drawerMode.value = 'edit';
    selectedLocker.value = locker;
    form.clearErrors();
    actionError.value = '';
    form.branch_id = locker.branch_id || defaultBranchId.value;
    form.locker_number = locker.locker_number || '';
    form.status = locker.status || 'active';
    form.location = locker.location || '';
    form.notes = locker.notes || '';
    drawerOpen.value = true;
};

const closeDrawer = () => {
    drawerOpen.value = false;
    form.clearErrors();
    actionError.value = '';
};

const applyFilters = (overrides = {}) => {
    const params = {
        search: filterSearch.value.trim() || undefined,
        availability: filterAvailability.value || undefined,
        status: filterStatus.value || undefined,
        per_page: filterPerPage.value || undefined,
        page: undefined,
        ...overrides,
    };

    router.get('/lockers', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['lockers', 'summary', 'filters'],
    });
};

const applySearch = () => {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => applyFilters(), 250);
};

const clearFilters = () => {
    filterSearch.value = '';
    filterAvailability.value = '';
    filterStatus.value = '';
    applyFilters();
};

const submit = async () => {
    form.branch_id = showBranchField.value ? form.branch_id : defaultBranchId.value;

    if (drawerMode.value === 'add') {
        form.post('/lockers', {
            preserveScroll: true,
            onSuccess: () => closeDrawer(),
        });
        return;
    }

    form.clearErrors();
    actionError.value = '';

    try {
        const response = await fetch(`/lockers/${selectedLocker.value.id}`, {
            method: 'PUT',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                branch_id: selectedLocker.value.branch_id || form.branch_id,
                locker_number: form.locker_number,
                status: form.status,
                location: form.location,
                notes: form.notes,
            }),
        });

        if (!response.ok) {
            form.setError(await parseErrors(response));
            return;
        }

        closeDrawer();
        refreshLockers();
    } catch {
        actionError.value = 'Unable to save locker right now.';
    }
};

const toggleStatus = async (locker) => {
    const assignment = rowAssignment(locker);
    if (locker.status === 'active' && assignment) {
        actionError.value = 'Release this locker before marking it inactive.';
        return;
    }

    actionError.value = '';
    const nextStatus = locker.status === 'active' ? 'inactive' : 'active';

    try {
        const response = await fetch(`/lockers/${locker.id}`, {
            method: 'PUT',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                branch_id: locker.branch_id,
                locker_number: locker.locker_number,
                status: nextStatus,
                location: locker.location || '',
                notes: locker.notes || '',
            }),
        });

        if (!response.ok) {
            const errors = await parseErrors(response);
            actionError.value = Object.values(errors)[0] || 'Unable to update locker status.';
            return;
        }

        refreshLockers();
    } catch {
        actionError.value = 'Unable to update locker status.';
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Lockers" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="mt-2 text-3xl font-semibold">Lockers</h1>
                    <p class="mt-1 text-slate-300">Track locker availability, assignments, and usage history.</p>
                </div>
                <button v-if="canAdd" type="button" @click="openDrawer" class="flex items-center gap-2 rounded-full bg-orange-500 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    <span>+</span> Add Locker
                </button>
            </div>

            <div class="grid gap-4 grid-cols-2 sm:grid-cols-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Total</p>
                    <p class="mt-1 text-3xl font-bold">{{ summary?.total || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Available</p>
                    <p class="mt-1 text-3xl font-bold text-emerald-400">{{ summary?.available || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Occupied</p>
                    <p class="mt-1 text-3xl font-bold text-amber-400">{{ summary?.occupied || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Inactive</p>
                    <p class="mt-1 text-3xl font-bold text-slate-400">{{ summary?.inactive || 0 }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 items-center">
                <div class="flex min-w-[220px] flex-1 items-center gap-2 rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                    <input v-model="filterSearch" @input="applySearch" type="text" placeholder="Search locker no. / member" class="w-full bg-transparent text-sm text-slate-300 outline-none">
                </div>
                <select v-model="filterAvailability" @change="applyFilters()" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                    <option value="">All Availability</option>
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                </select>
                <select v-model="filterStatus" @change="applyFilters()" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button v-if="hasActiveFilters" type="button" @click="clearFilters" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 text-sm font-medium text-slate-400 hover:bg-white/5">
                    Clear
                </button>
            </div>

            <div v-if="actionError" class="rounded-xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-500">
                {{ actionError }}
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div v-if="lockerRows.length === 0" class="flex flex-col items-center gap-4 py-20 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-orange-500/10 text-2xl">ðŸ”’</div>
                    <p class="text-lg font-bold">No lockers found</p>
                    <p class="text-sm text-slate-400">Get started by adding your first locker.</p>
                    <button v-if="canAdd" type="button" @click="openDrawer" class="mt-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">Add Locker</button>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Locker No.</th>
                                <th class="px-4 py-3">Location</th>
                                <th class="px-4 py-3">Branch</th>
                                <th class="px-4 py-3">Availability</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Member</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="locker in lockerRows" :key="locker.id" class="hover:bg-white/5">
                                <td class="px-4 py-3 font-bold text-orange-400">{{ locker.locker_number }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ locker.location || '-' }}</td>
                                <td class="px-4 py-3">{{ rowBranchName(locker) || '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-bold" :class="rowAssignment(locker) ? 'bg-amber-500/10 text-amber-400' : 'bg-emerald-500/10 text-emerald-400'">
                                        {{ rowAssignment(locker) ? 'Occupied' : 'Available' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <button
                                        v-if="canEdit"
                                        type="button"
                                        @click="toggleStatus(locker)"
                                        class="locker-status-toggle"
                                        :class="{ 'locker-status-toggle-on': locker.status === 'active' }"
                                        :title="locker.status === 'active' ? 'Mark inactive' : 'Mark active'"
                                    >
                                        <span class="locker-status-toggle-dot"></span>
                                        <span>{{ statusText(locker.status) }}</span>
                                    </button>
                                    <span v-else class="rounded-full px-2 py-1 text-xs font-bold" :style="{ background: getStatusColor(locker.status).bg, color: getStatusColor(locker.status).fg }">
                                        {{ locker.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div v-if="rowAssignment(locker)?.member">
                                        <p class="font-semibold">{{ rowAssignment(locker).member.name }}</p>
                                        <p class="text-xs text-slate-400">{{ rowAssignment(locker).member.phone || rowAssignment(locker).member.member_code || '' }}</p>
                                    </div>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <Link :href="`/lockers/${locker.id}`" class="locker-icon-btn locker-icon-btn-view" title="View locker" aria-label="View locker">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </Link>
                                        <button v-if="canEdit" type="button" @click="openEditDrawer(locker)" class="locker-icon-btn locker-icon-btn-edit" title="Edit locker" aria-label="Edit locker">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="lockers?.links?.length" class="flex flex-col items-center justify-between gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 sm:flex-row">
                <p class="text-xs text-slate-400">
                    Showing {{ lockers.from || 0 }} to {{ lockers.to || 0 }} of {{ lockers.total || 0 }} lockers
                </p>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <select v-model="filterPerPage" @change="applyFilters()" class="rounded-lg border border-white/10 bg-slate-950/50 px-2.5 py-1.5 text-xs outline-none focus:border-orange-400">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                        <option value="100">100 / page</option>
                    </select>
                    <div v-if="paginationLinks.length > 1" class="flex flex-wrap items-center gap-1">
                        <Link
                            v-for="link in paginationLinks"
                            :key="link.label"
                            :href="link.url || '#'"
                            preserve-scroll
                            preserve-state
                            :class="[
                                'rounded-lg px-2.5 py-1.5 text-xs font-semibold transition',
                                link.active ? 'bg-orange-500 text-slate-950' : 'border border-white/10 bg-white/5 text-slate-400 hover:bg-white/10',
                                !link.url && !link.active ? 'pointer-events-none opacity-40' : ''
                            ]"
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="drawerOpen" class="locker-drawer-backdrop fixed inset-0 z-40" @click="closeDrawer"></div>
        <aside
            class="app-panel-strong fixed right-0 top-0 z-50 flex h-full w-[420px] max-w-full flex-col border-l shadow-2xl transition-transform duration-200"
            :class="drawerOpen ? 'translate-x-0' : 'translate-x-full'"
        >
            <div class="flex items-center justify-between border-b px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold">{{ drawerTitle }}</h2>
                    <p class="app-muted mt-1 text-xs">{{ drawerSubtitle }}</p>
                </div>
                <button type="button" @click="closeDrawer" class="rounded-lg border px-2.5 py-1.5 text-sm app-muted transition hover:opacity-80">
                    x
                </button>
            </div>

            <form @submit.prevent="submit" class="flex flex-1 flex-col overflow-y-auto">
                <div class="flex-1 space-y-4 p-5">
                    <div v-if="showBranchField">
                        <label class="mb-1.5 block text-sm font-medium">Branch <span class="text-red-400">*</span></label>
                        <select
                            v-model="form.branch_id"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400"
                            :class="{ 'field-invalid': form.errors.branch_id }"
                            required
                        >
                            <option value="">Select branch</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                        </select>
                        <p v-if="form.errors.branch_id" class="field-error">{{ form.errors.branch_id }}</p>
                    </div>

                    <div v-if="drawerMode === 'edit'" class="rounded-xl border px-3 py-2.5">
                        <p class="text-xs font-bold uppercase tracking-wide app-muted">Branch</p>
                        <p class="mt-1 font-semibold">{{ rowBranchName(selectedLocker) || '-' }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Locker No. <span class="text-red-400">*</span></label>
                        <input
                            v-model="form.locker_number"
                            type="text"
                            maxlength="20"
                            placeholder="e.g. L-07"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400"
                            :class="{ 'field-invalid': form.errors.locker_number }"
                            required
                        >
                        <p v-if="form.errors.locker_number" class="field-error">{{ form.errors.locker_number }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Location / Zone</label>
                        <input
                            v-model="form.location"
                            type="text"
                            maxlength="200"
                            placeholder="e.g. Male changing room, Zone A"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400"
                            :class="{ 'field-invalid': form.errors.location }"
                        >
                        <p v-if="form.errors.location" class="field-error">{{ form.errors.location }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Notes</label>
                        <textarea
                            v-model="form.notes"
                            rows="4"
                            maxlength="1000"
                            placeholder="Optional"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-orange-400"
                            :class="{ 'field-invalid': form.errors.notes }"
                        ></textarea>
                        <p v-if="form.errors.notes" class="field-error">{{ form.errors.notes }}</p>
                    </div>

                    <p v-if="form.errors.general" class="field-error">{{ form.errors.general }}</p>
                </div>

                <div class="flex justify-end gap-3 border-t px-5 py-4">
                    <button type="button" @click="closeDrawer" class="rounded-xl border px-4 py-2 text-sm font-semibold app-muted transition hover:opacity-80">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400 disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing">
                        {{ submitLabel }}
                    </button>
                </div>
            </form>
        </aside>
    </AppLayout>
</template>

