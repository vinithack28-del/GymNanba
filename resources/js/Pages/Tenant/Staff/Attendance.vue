<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({}),
    },
    branches: {
        type: [Array, Object],
        default: () => [],
    },
    staffOptions: {
        type: [Array, Object],
        default: () => [],
    },
    logs: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const attendanceRows = computed(() => props.logs?.data || []);
const paginationLinks = computed(() => (props.logs?.links || []).filter((link) => link.url || link.active));
const branchOptions = computed(() => Object.values(props.branches || {}));
const staffList = computed(() => Object.values(props.staffOptions || {}));
const filterPerPage = ref(props.filters?.per_page || props.logs?.per_page || 25);

const perPageUrl = computed(() => {
    const params = new URLSearchParams();

    Object.entries(props.filters || {}).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            params.set(key, value);
        }
    });

    params.set('per_page', filterPerPage.value);
    params.delete('page');

    return `/staff/attendance?${params.toString()}`;
});

const exportUrl = computed(() => {
    const params = new URLSearchParams();

    Object.entries(props.filters || {}).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            params.set(key, value);
        }
    });

    params.set('export', 'csv');

    return `/staff/attendance?${params.toString()}`;
});

const markAttendanceUrl = computed(() => {
    return props.filters?.staff_id ? `/staff/attendance/create?staff_id=${props.filters.staff_id}` : '/staff/attendance/create';
});
</script>

<template>
    <AppLayout>
        <Head title="Staff Attendance" />

        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold md:text-2xl">Staff Attendance</h1>
                    <p class="app-muted mt-0.5 text-sm">Month-wise staff attendance and working hours.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="exportUrl" class="app-panel rounded-lg border px-3 py-2 text-xs font-semibold transition hover:opacity-80">Export</Link>
                    <Link :href="markAttendanceUrl" class="rounded-lg bg-orange-500 px-3 py-2 text-xs font-semibold text-slate-950 transition hover:bg-orange-400">Mark Attendance</Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                <div class="app-panel rounded-xl border px-3 py-2.5">
                    <p class="app-muted text-[11px] font-bold uppercase tracking-[0.12em]">Days Present</p>
                    <p class="mt-1 text-lg font-semibold">{{ summary?.days_present || 0 }}</p>
                </div>
                <div class="app-panel rounded-xl border px-3 py-2.5">
                    <p class="app-muted text-[11px] font-bold uppercase tracking-[0.12em]">Hours Worked</p>
                    <p class="mt-1 text-lg font-semibold text-emerald-400">{{ summary?.hours_worked || 0 }}</p>
                </div>
                <div class="app-panel rounded-xl border px-3 py-2.5">
                    <p class="app-muted text-[11px] font-bold uppercase tracking-[0.12em]">Manual Notes</p>
                    <p class="mt-1 text-lg font-semibold text-orange-400">{{ summary?.leaves_marked || 0 }}</p>
                </div>
            </div>

            <form method="GET" action="/staff/attendance" class="app-panel flex flex-wrap items-center gap-2 rounded-xl border p-3">
                <input
                    type="month"
                    name="month"
                    :value="filters?.month"
                    class="app-panel-strong rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400"
                >

                <select name="branch_id" class="app-panel-strong min-w-[150px] rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                    <option value="">All Branches</option>
                    <option v-for="branch in branchOptions" :key="branch.id" :value="branch.id" :selected="Number(filters?.branch_id) === Number(branch.id)">{{ branch.name }}</option>
                </select>

                <select name="staff_id" class="app-panel-strong min-w-[180px] rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                    <option value="">All Staff</option>
                    <option v-for="staff in staffList" :key="staff.id" :value="staff.id" :selected="Number(filters?.staff_id) === Number(staff.id)">{{ staff.name }}</option>
                </select>

                <button type="submit" class="rounded-lg bg-orange-500 px-3 py-2 text-xs font-semibold text-slate-950 transition hover:bg-orange-400">Apply</button>
            </form>

            <div class="app-panel overflow-hidden rounded-xl border">
                <div v-if="attendanceRows.length === 0" class="p-6 text-center text-sm app-muted">No attendance records found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[780px] text-left text-sm">
                        <thead class="app-table-head text-[11px] font-bold uppercase tracking-[0.08em] app-muted">
                            <tr>
                                <th class="px-3 py-2">Staff</th>
                                <th class="px-3 py-2">Date</th>
                                <th class="px-3 py-2">Check In</th>
                                <th class="px-3 py-2">Check Out</th>
                                <th class="px-3 py-2">Hours</th>
                                <th class="px-3 py-2">Branch</th>
                                <th class="px-3 py-2">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr v-for="record in attendanceRows" :key="record.id" class="transition hover:bg-white/5">
                                <td class="px-3 py-2 font-semibold">
                                    <div>{{ record.staff_name || '-' }}</div>
                                    <div class="app-muted text-xs">{{ record.role_label || '-' }}</div>
                                </td>
                                <td class="px-3 py-2">{{ record.attendance_date || '-' }}</td>
                                <td class="px-3 py-2 app-muted">{{ record.checked_in_at || '-' }}</td>
                                <td class="px-3 py-2 app-muted">{{ record.checked_out_at || '-' }}</td>
                                <td class="px-3 py-2">{{ record.hours_worked ?? '-' }}</td>
                                <td class="px-3 py-2 app-muted">{{ record.branch_name || '-' }}</td>
                                <td class="px-3 py-2 app-muted">{{ record.reason || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="logs?.links?.length" class="app-panel flex flex-col items-center justify-between gap-3 rounded-xl border px-4 py-3 sm:flex-row">
                <p class="app-muted text-xs">
                    Showing {{ logs.from || 0 }} to {{ logs.to || 0 }} of {{ logs.total || 0 }} records
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
    </AppLayout>
</template>
