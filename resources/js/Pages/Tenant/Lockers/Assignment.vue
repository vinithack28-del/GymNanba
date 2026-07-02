<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    locker: Object,
    lockerData: Object,
    canAssign: Boolean,
});

const current = props.locker?.currentAssignment;
const isOccupied = current !== null;

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
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
            <Link href="/tenant/lockers" class="flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-orange-400">
                <span>←</span> Back to Lockers
            </Link>

            <div class="grid gap-6 lg:grid-cols-[320px_1fr]">
                <div class="rounded-2xl border border-white/10 bg-white/5 overflow-hidden">
                    <div class="flex flex-col items-center gap-3 border-b border-white/10 p-6">
                        <div class="flex h-18 w-18 items-center justify-center rounded-full border border-white/10 bg-slate-950/50 text-slate-400">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="2" width="14" height="20" rx="2"/>
                                <circle cx="12" cy="13" r="2"/>
                                <path d="M12 9v2"/>
                            </svg>
                        </div>
                        <p class="text-lg font-bold">{{ locker.locker_number }}</p>
                        <div class="flex flex-wrap gap-2 justify-center">
                            <span class="flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold" :class="isOccupied ? 'bg-amber-500/10 text-amber-400' : 'bg-emerald-500/10 text-emerald-400'">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ isOccupied ? 'Occupied' : 'Available' }}
                            </span>
                            <span class="flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold" :style="{ background: getStatusColor(locker.status).bg, color: getStatusColor(locker.status).fg }">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ locker.status }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 p-6">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center text-slate-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Location / Zone</p>
                                <p class="mt-0.5 text-sm">{{ locker.location || '—' }}</p>
                            </div>
                        </div>

                        <div v-if="locker.branch" class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center text-slate-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M15 9h.01"/><path d="M15 13h.01"/></svg>
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Branch</p>
                                <p class="mt-0.5 text-sm">{{ locker.branch.name }}</p>
                            </div>
                        </div>

                        <div v-if="locker.notes" class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center text-slate-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M14 3v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Notes</p>
                                <p class="mt-0.5 text-sm">{{ locker.notes }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center text-slate-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg>
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Added</p>
                                <p class="mt-0.5 text-sm">{{ formatDate(locker.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="current && current.member" class="border-t border-white/10 p-6">
                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Currently Assigned To</p>
                        <Link :href="`/tenant/members/${current.member.id}`" class="block text-lg font-bold hover:text-orange-400">
                            {{ current.member.name }}
                        </Link>
                        <p class="text-sm text-slate-400">{{ current.member.member_code }} · {{ current.member.phone }}</p>
                        <div class="mt-2 flex items-center gap-2 text-sm text-slate-400">
                            <span>{{ formatDate(current.from_date) }}</span>
                            <span class="text-orange-400">→</span>
                            <span>{{ formatDate(current.to_date) || 'Ongoing' }}</span>
                            <span>·</span>
                            <span>{{ lockerData?.current_assignment?.days_so_far || 0 }} days</span>
                        </div>
                    </div>

                    <div v-else class="border-t border-white/10 p-6">
                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Currently Assigned To</p>
                        <p class="mt-2 text-sm text-slate-400">No active assignment</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 overflow-hidden">
                    <div class="flex items-center justify-between border-b border-white/10 p-4">
                        <h3 class="font-bold">Assignment History</h3>
                        <span class="rounded-full border border-white/10 bg-slate-950/50 px-2 py-1 text-xs font-semibold text-slate-400">
                            {{ lockerData?.history_count || 0 }} records
                        </span>
                    </div>
                    <div v-if="!lockerData?.history || lockerData.history.length === 0" class="p-6 text-center text-sm text-slate-400">
                        No assignment history available.
                    </div>
                    <div v-else class="divide-y divide-white/10">
                        <div v-for="(item, index) in lockerData.history" :key="index" class="p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <Link v-if="item.member" :href="`/tenant/members/${item.member.id}`" class="font-bold hover:text-orange-400">
                                        {{ item.member.name }}
                                    </Link>
                                    <span v-else class="font-bold">—</span>
                                    <p class="text-sm text-slate-400">{{ item.member?.member_code || '' }}</p>
                                </div>
                                <div class="text-right text-sm text-slate-400">
                                    <p>{{ formatDate(item.from_date) }} → {{ formatDate(item.to_date) || 'Ongoing' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>