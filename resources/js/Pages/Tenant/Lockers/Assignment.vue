<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    locker: Object,
    lockerData: Object,
    canEdit: Boolean,
});

const current = computed(() => props.lockerData?.current_assignment || null);
const historyRows = computed(() => props.lockerData?.history || []);

const formatDate = (date) => {
    if (!date) return 'â€”';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const getStatusColor = (status) => {
    const colors = {
        active: { bg: 'rgba(34,197,94,0.12)', fg: '#22c55e' },
        inactive: { bg: 'rgba(148,163,184,0.12)', fg: '#94a3b8' },
        maintenance: { bg: 'rgba(245,158,11,0.12)', fg: '#f59e0b' },
    };
    return colors[status] || colors.inactive;
};
</script>

<template>
    <AppLayout>
        <Head :title="`Locker ${locker.locker_number}`" />

        <div class="flex flex-col gap-5">
            <Link href="/lockers" class="inline-flex items-center gap-2 text-sm font-semibold app-muted transition hover:text-orange-500">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                    <path d="M19 12H5" />
                    <path d="M12 19l-7-7 7-7" />
                </svg>
                Back to Lockers
            </Link>

            <div class="grid items-start gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
                <div class="overflow-hidden rounded-[28px] border app-panel shadow-sm">
                    <div class="flex flex-col items-center gap-3 border-b px-6 py-6">
                        <div class="flex h-[72px] w-[72px] items-center justify-center rounded-full bg-orange-500/10 text-orange-500">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <rect x="6" y="3" width="12" height="18" rx="2" />
                                <circle cx="12" cy="12" r="1.4" />
                                <path d="M12 8v2" />
                            </svg>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold">{{ locker.locker_number }}</p>
                            <p class="app-muted text-xs font-semibold">{{ locker.location || 'No location added' }}</p>
                        </div>
                        <div class="flex flex-wrap justify-center gap-2">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase" :class="current ? 'bg-amber-500/10 text-amber-500' : 'bg-emerald-500/10 text-emerald-500'">
                                {{ current ? 'Occupied' : 'Available' }}
                            </span>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase" :style="{ background: getStatusColor(locker.status).bg, color: getStatusColor(locker.status).fg }">
                                {{ lockerData?.status_label || locker.status }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 px-6 py-5">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 app-muted">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            </span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] app-muted">Location / Zone</p>
                                <p class="mt-1 text-sm">{{ locker.location || 'â€”' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 app-muted">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01"/><path d="M15 9h.01"/><path d="M9 13h.01"/><path d="M15 13h.01"/></svg>
                            </span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] app-muted">Branch</p>
                                <p class="mt-1 text-sm">{{ locker.branch?.name || lockerData?.branch_name || 'â€”' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 app-muted">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg>
                            </span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] app-muted">Added</p>
                                <p class="mt-1 text-sm">{{ formatDate(locker.created_at) }}</p>
                            </div>
                        </div>

                        <div v-if="locker.notes" class="flex items-start gap-3">
                            <span class="mt-0.5 app-muted">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M14 3v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>
                            </span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] app-muted">Notes</p>
                                <p class="mt-1 text-sm">{{ locker.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t px-6 py-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.08em] app-muted">Currently Assigned To</p>
                        <div v-if="current" class="mt-3">
                            <a v-if="current.member_url" :href="current.member_url" class="font-semibold text-orange-500 hover:underline">
                                {{ current.member_name }}
                            </a>
                            <p v-else class="font-semibold">{{ current.member_name }}</p>
                            <p class="app-muted mt-1 text-xs">
                                {{ current.member_code || 'No code' }} Â· {{ current.member_phone || 'No phone' }}
                            </p>
                            <p class="app-muted mt-2 text-xs">
                                {{ current.from_date || 'â€”' }} â†’ {{ current.to_date || 'Ongoing' }}
                            </p>
                            <span class="mt-2 inline-flex rounded-full bg-orange-500/10 px-2.5 py-1 text-xs font-bold text-orange-500">
                                {{ current.days_so_far || 0 }} days
                            </span>
                        </div>
                        <p v-else class="app-muted mt-3">No active assignment.</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[28px] border app-panel shadow-sm">
                    <div class="flex items-center justify-between border-b px-6 py-4">
                        <h3 class="text-[15px] font-semibold">Usage History</h3>
                        <span class="rounded-full border app-panel-strong px-3 py-1 text-xs font-semibold app-muted">
                            {{ historyRows.length }} {{ historyRows.length === 1 ? 'record' : 'records' }}
                        </span>
                    </div>

                    <div v-if="historyRows.length === 0" class="px-6 py-14 text-center text-sm app-muted">
                        No usage history yet.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full min-w-[620px] text-left">
                            <thead class="app-table-head">
                                <tr>
                                    <th class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] app-muted">Member</th>
                                    <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] app-muted">From</th>
                                    <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] app-muted">To</th>
                                    <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.18em] app-muted">Days</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                <tr v-for="(item, index) in historyRows" :key="index" :class="item.is_current ? 'bg-orange-500/5' : ''">
                                    <td class="px-6 py-3">
                                        <a v-if="item.member_url" :href="item.member_url" class="font-semibold hover:underline">{{ item.member_name || 'â€”' }}</a>
                                        <p v-else class="font-semibold">{{ item.member_name || 'â€”' }}</p>
                                        <p class="app-muted mt-0.5 text-xs">{{ item.member_code || '' }}</p>
                                        <span v-if="item.is_current" class="mt-1 inline-flex rounded-full bg-orange-500/10 px-2 py-0.5 text-xs font-bold text-orange-400">Current</span>
                                    </td>
                                    <td class="px-4 py-3 app-muted">{{ item.from_date || 'â€”' }}</td>
                                    <td class="px-4 py-3 app-muted">{{ item.to_date || 'â€”' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">{{ item.days || 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

