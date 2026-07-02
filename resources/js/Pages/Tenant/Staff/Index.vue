<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    staff: Object,
    stats: Object,
    roles: Object,
    branches: Object,
    statuses: Object,
    filters: Object,
    canManage: Boolean,
});

const staffRows = computed(() => props.staff?.data || []);
const formatRole = (role) => String(role || '—').replaceAll('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
const formatStatus = (status) => {
    const value = String(status || 'inactive');
    return value.charAt(0).toUpperCase() + value.slice(1);
};
</script>

<template>
    <AppLayout>
        <Head title="Staff" />
        
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Gym Workspace</p>
                <h1 class="mt-2 text-3xl font-semibold">Staff</h1>
                <p class="mt-1 text-slate-300">Manage gym staff, trainers, and employees.</p>
            </div>

            <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Total</p>
                    <p class="mt-2 text-2xl font-semibold">{{ stats?.total || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Active</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-400">{{ stats?.active || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Inactive</p>
                    <p class="mt-2 text-2xl font-semibold text-red-400">{{ stats?.inactive || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Late Logins</p>
                    <p class="mt-2 text-2xl font-semibold text-orange-400">{{ stats?.late_logins || 0 }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <form method="GET" action="/staff" class="flex flex-wrap items-center gap-2">
                    <input name="search" :value="filters?.search" placeholder="Search staff..." class="min-w-[220px] flex-1 rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">

                    <select name="role" class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <option value="">All Roles</option>
                        <option v-for="role in roles" :key="role" :value="role">{{ formatRole(role) }}</option>
                    </select>

                    <select name="branch_id" class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <option value="">All Branches</option>
                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                    </select>

                    <select name="status" class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <option value="">All Statuses</option>
                        <option v-for="status in statuses" :key="status" :value="status">{{ status.charAt(0).toUpperCase() + status.slice(1) }}</option>
                    </select>

                    <div class="flex items-center gap-2">
                        <Link href="/staff/roles" class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm font-semibold text-slate-300 hover:bg-white/5">Roles</Link>
                        <Link href="/staff/attendance" class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm font-semibold text-slate-300 hover:bg-white/5">Attendance</Link>
                        <Link v-if="canManage" href="/staff/create" class="rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400">Add Staff</Link>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div v-if="staffRows.length === 0" class="p-6 text-center text-sm text-slate-400">No staff found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Branch</th>
                                <th class="px-4 py-3">Phone</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Joined</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="member in staffRows" :key="member.id" class="hover:bg-white/5">
                                <td class="px-4 py-3 font-semibold">{{ member.name }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ formatRole(member.role) }}</td>
                                <td class="px-4 py-3">{{ member.branch_name || '—' }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ member.phone }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="member.status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'">
                                        {{ formatStatus(member.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400">{{ member.join_date }}</td>
                                <td class="px-4 py-3">
                                    <Link :href="`/staff/${member.id}`" class="text-orange-400 hover:text-orange-300 text-sm">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
