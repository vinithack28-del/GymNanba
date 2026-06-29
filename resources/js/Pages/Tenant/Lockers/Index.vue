<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    lockers: Object,
    summary: Object,
    canAdd: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
});

const lockerRows = computed(() => props.lockers?.data || []);

const getStatusColor = (status) => {
    const colors = {
        active: { bg: 'rgba(34,197,94,0.12)', fg: '#22c55e' },
        maintenance: { bg: 'rgba(245,158,11,0.12)', fg: '#f59e0b' },
        inactive: { bg: 'rgba(148,163,184,0.12)', fg: '#94a3b8' },
    };
    return colors[status] || colors.inactive;
};
</script>

<template>
    <AppLayout>
        <Head title="Lockers" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Operations</p>
                    <h1 class="mt-2 text-3xl font-semibold">Lockers</h1>
                    <p class="mt-1 text-slate-300">Track locker availability, assignments, and usage history.</p>
                </div>
                <Link v-if="canAdd" href="/lockers/create" class="flex items-center gap-2 rounded-full bg-orange-500 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    <span>+</span> Add Locker
                </Link>
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
                    <input type="text" placeholder="Search locker no. / member" class="w-full bg-transparent text-sm text-slate-300 outline-none">
                </div>
                <select class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                    <option value="">All Availability</option>
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                </select>
                <select class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div v-if="lockerRows.length === 0" class="flex flex-col items-center gap-4 py-20 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-orange-500/10 text-2xl">🔒</div>
                    <p class="text-lg font-bold">No lockers found</p>
                    <p class="text-sm text-slate-400">Get started by adding your first locker.</p>
                    <Link v-if="canAdd" href="/lockers/create" class="mt-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">Add Locker</Link>
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
                                <td class="px-4 py-3 text-slate-400">{{ locker.location || '—' }}</td>
                                <td class="px-4 py-3">{{ locker.branch?.name || '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-bold" :class="locker.currentAssignment ? 'bg-amber-500/10 text-amber-400' : 'bg-emerald-500/10 text-emerald-400'">
                                        {{ locker.currentAssignment ? 'Occupied' : 'Available' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-bold" :style="{ background: getStatusColor(locker.status).bg, color: getStatusColor(locker.status).fg }">
                                        {{ locker.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ locker.currentAssignment?.member?.name || '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Link :href="`/lockers/${locker.id}`" class="text-orange-400 hover:text-orange-300 text-sm">View</Link>
                                        <Link v-if="canEdit" :href="`/lockers/${locker.id}/edit`" class="text-slate-400 hover:text-slate-300 text-sm">Edit</Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
