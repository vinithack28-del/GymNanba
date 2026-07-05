<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import AppConfirmDialog from '../../../Components/AppConfirmDialog.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    staff: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    roles: {
        type: [Array, Object],
        default: () => [],
    },
    branches: {
        type: [Array, Object],
        default: () => [],
    },
    statuses: {
        type: [Array, Object],
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    canManage: Boolean,
});

const staffRows = computed(() => props.staff?.data || []);
const paginationLinks = computed(() => (props.staff?.links || []).filter((link) => link.url || link.active));
const roleOptions = computed(() => Object.values(props.roles || {}));
const branchOptions = computed(() => Object.values(props.branches || {}));
const statusOptions = computed(() => Object.values(props.statuses || {}));
const filterPerPage = ref(props.filters?.per_page || props.staff?.per_page || 25);
const resetTarget = ref(null);
const resetProcessing = ref(false);

const perPageUrl = computed(() => {
    const params = new URLSearchParams();

    Object.entries(props.filters || {}).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            params.set(key, value);
        }
    });

    params.set('per_page', filterPerPage.value);
    params.delete('page');

    return `/staff?${params.toString()}`;
});

const formatRole = (role) => String(role || '-').replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
const formatStatus = (status) => {
    const value = String(status || 'inactive');
    return value.charAt(0).toUpperCase() + value.slice(1);
};
const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    const [date] = String(value).split('T');
    const parts = date.split('-');

    return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : value;
};

const openResetDialog = (member) => {
    resetTarget.value = member;
};

const closeResetDialog = () => {
    if (resetProcessing.value) {
        return;
    }

    resetTarget.value = null;
};

const resetPassword = () => {
    if (!resetTarget.value) {
        return;
    }

    resetProcessing.value = true;
    router.post(`/staff/${resetTarget.value.id}/reset-password`, {}, {
        preserveScroll: true,
        onFinish: () => {
            resetProcessing.value = false;
            resetTarget.value = null;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Staff" />

        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold md:text-2xl">Staff</h1>
                    <p class="app-muted mt-0.5 text-sm">Manage gym staff, trainers, and employees.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Link href="/staff/roles" class="app-panel rounded-lg border px-3 py-2 text-xs font-semibold transition hover:opacity-80">Roles</Link>
                    <Link href="/staff/attendance" class="app-panel rounded-lg border px-3 py-2 text-xs font-semibold transition hover:opacity-80">Attendance</Link>
                    <Link v-if="canManage" href="/staff/create" class="rounded-lg bg-orange-500 px-3 py-2 text-xs font-semibold text-slate-950 transition hover:bg-orange-400">Add Staff</Link>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
                <div class="app-panel rounded-xl border px-3 py-2.5">
                    <p class="app-muted text-[11px] font-bold uppercase tracking-[0.12em]">Total</p>
                    <p class="mt-1 text-lg font-semibold">{{ stats?.total || 0 }}</p>
                </div>
                <div class="app-panel rounded-xl border px-3 py-2.5">
                    <p class="app-muted text-[11px] font-bold uppercase tracking-[0.12em]">Active</p>
                    <p class="mt-1 text-lg font-semibold text-emerald-400">{{ stats?.active || 0 }}</p>
                </div>
                <div class="app-panel rounded-xl border px-3 py-2.5">
                    <p class="app-muted text-[11px] font-bold uppercase tracking-[0.12em]">Inactive</p>
                    <p class="mt-1 text-lg font-semibold text-red-400">{{ stats?.inactive || 0 }}</p>
                </div>
                <div class="app-panel rounded-xl border px-3 py-2.5">
                    <p class="app-muted text-[11px] font-bold uppercase tracking-[0.12em]">Late Logins</p>
                    <p class="mt-1 text-lg font-semibold text-orange-400">{{ stats?.late_logins || 0 }}</p>
                </div>
            </div>

            <form method="GET" action="/staff" class="app-panel flex flex-wrap items-center gap-2 rounded-xl border p-3">
                <input
                    name="search"
                    :value="filters?.search"
                    placeholder="Search staff..."
                    class="app-panel-strong min-w-[180px] flex-1 rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400"
                >

                <select name="role" class="app-panel-strong min-w-[140px] rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                    <option value="">All Roles</option>
                    <option v-for="role in roleOptions" :key="role" :value="role" :selected="filters?.role === role">{{ formatRole(role) }}</option>
                </select>

                <select name="branch_id" class="app-panel-strong min-w-[150px] rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                    <option value="">All Branches</option>
                    <option v-for="branch in branchOptions" :key="branch.id" :value="branch.id" :selected="Number(filters?.branch_id) === Number(branch.id)">{{ branch.name }}</option>
                </select>

                <select name="status" class="app-panel-strong min-w-[140px] rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                    <option value="">All Statuses</option>
                    <option v-for="status in statusOptions" :key="status" :value="status" :selected="filters?.status === status">{{ formatStatus(status) }}</option>
                </select>

                <button type="submit" class="rounded-lg bg-orange-500 px-3 py-2 text-xs font-semibold text-slate-950 transition hover:bg-orange-400">Apply</button>
            </form>

            <div class="app-panel overflow-hidden rounded-xl border">
                <div v-if="staffRows.length === 0" class="p-6 text-center text-sm app-muted">No staff found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[780px] text-left text-sm">
                        <thead class="app-table-head text-[11px] font-bold uppercase tracking-[0.08em] app-muted">
                            <tr>
                                <th class="px-3 py-2">Name</th>
                                <th class="px-3 py-2">Role</th>
                                <th class="px-3 py-2">Branch</th>
                                <th class="px-3 py-2">Phone</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2">Joined</th>
                                <th class="px-3 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr v-for="member in staffRows" :key="member.id" class="transition hover:bg-white/5">
                                <td class="px-3 py-2 font-semibold">
                                    <div class="max-w-[220px] truncate">{{ member.name }}</div>
                                    <div class="app-muted max-w-[220px] truncate text-xs">{{ member.email || '-' }}</div>
                                </td>
                                <td class="px-3 py-2 app-muted">{{ formatRole(member.role) }}</td>
                                <td class="px-3 py-2">{{ member.branch_name || member.branch?.name || '-' }}</td>
                                <td class="px-3 py-2 app-muted">{{ member.phone || '-' }}</td>
                                <td class="px-3 py-2">
                                    <span class="rounded-full px-2 py-1 text-[11px] font-semibold" :class="member.status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'">
                                        {{ formatStatus(member.status) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 app-muted">{{ formatDate(member.join_date) }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex justify-end gap-1.5">
                                        <Link :href="`/staff/${member.id}`" class="locker-icon-btn locker-icon-btn-view" title="View staff" aria-label="View staff">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </Link>
                                        <Link v-if="canManage" :href="`/staff/${member.id}/edit`" class="locker-icon-btn locker-icon-btn-edit" title="Edit staff" aria-label="Edit staff">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </Link>
                                        <button v-if="canManage" type="button" class="locker-icon-btn text-amber-400" title="Reset password" aria-label="Reset password" @click="openResetDialog(member)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 7a4 4 0 1 0-4 4"/><path d="M14 14l6-6"/><path d="M18 8l2 2"/><path d="M15 11l2 2"/><path d="M9 17v4"/><path d="M7 19h4"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="staff?.links?.length" class="app-panel flex flex-col items-center justify-between gap-3 rounded-xl border px-4 py-3 sm:flex-row">
                <p class="app-muted text-xs">
                    Showing {{ staff.from || 0 }} to {{ staff.to || 0 }} of {{ staff.total || 0 }} staff
                </p>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <select v-model="filterPerPage" @change="$inertia.visit(perPageUrl, { preserveScroll: true, preserveState: true })" class="app-panel-strong rounded-lg border px-2.5 py-1.5 text-xs outline-none focus:border-orange-400">
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
                                link.active ? 'bg-orange-500 text-slate-950' : 'app-panel border hover:opacity-80',
                                !link.url && !link.active ? 'pointer-events-none opacity-40' : ''
                            ]"
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>
        </div>

        <AppConfirmDialog
            :open="Boolean(resetTarget)"
            title="Reset staff password"
            :message="`Reset ${resetTarget?.name || 'this staff'}'s password to 123456? They will be asked to change it after login.`"
            confirm-label="Reset Password"
            :processing="resetProcessing"
            @cancel="closeResetDialog"
            @confirm="resetPassword"
        />
    </AppLayout>
</template>
