<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    checkins: Object,
});

const formatDate = (date) => {
    if (!date) return 'â€”';
    return new Date(date).toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace(',', '').replaceAll('/', '-');
};

const getMethodBadge = (method) => {
    const badges = {
        manual: 'bg-sky-500/15 text-sky-300',
        qr: 'bg-emerald-500/15 text-emerald-300',
        biometric: 'bg-purple-500/15 text-purple-300',
    };
    return badges[method] || 'bg-slate-500/15 text-slate-300';
};
</script>

<template>
    <AppLayout>
        <Head title="Daily Check-ins" />
        
        <div class="flex flex-col gap-5">
            <div>
                <h1 class="mt-2 text-3xl font-semibold">Daily Check-ins</h1>
                <p class="mt-1 text-slate-300">Track member attendance for today.</p>
            </div>

            <div class="grid gap-4 grid-cols-3 sm:grid-cols-1">
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.1em] text-slate-400">Total Today</p>
                    <p class="mt-1 text-2xl font-bold">{{ stats?.total || 0 }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.1em] text-slate-400">Active Now</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-400">{{ stats?.active || 0 }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.1em] text-slate-400">Checked Out</p>
                    <p class="mt-2 text-2xl font-bold text-slate-400">{{ stats?.checked_out || 0 }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <input type="text" placeholder="Search by name or code..." class="rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                <select class="rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 outline-none">
                    <option value="">All Methods</option>
                    <option value="manual">Manual</option>
                    <option value="qr">QR Code</option>
                    <option value="biometric">Biometric</option>
                </select>
                <button class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    + Check In
                </button>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
                <div v-if="!checkins || checkins.length === 0" class="flex flex-col items-center gap-4 py-20 text-center">
                    <div class="flex h-[4.5rem] w-[4.5rem] items-center justify-center rounded-full border border-white/10 bg-slate-950/50 text-slate-400 text-2xl">ðŸ“‹</div>
                    <p class="text-base font-semibold">No check-ins today</p>
                    <p class="text-sm text-slate-400">Start checking in members to track attendance.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full divide-y divide-white/10 text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Member</th>
                                <th class="px-4 py-3">Check-in Time</th>
                                <th class="px-4 py-3">Method</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="checkin in checkins" :key="checkin.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-500/20 text-xs font-bold text-orange-300">{{ checkin.member?.initials || '?' }}</span>
                                        <div>
                                            <p class="font-medium">{{ checkin.member?.name || 'Unknown' }}</p>
                                            <p class="text-xs text-slate-400">{{ checkin.member?.member_code || 'â€”' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-400">{{ formatDate(checkin.check_in_at) }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-bold uppercase" :class="getMethodBadge(checkin.method)">
                                        {{ checkin.method }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="checkin.check_out_at ? 'text-slate-400' : 'text-emerald-400'">
                                        {{ checkin.check_out_at ? 'Checked Out' : 'Active' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <button v-if="!checkin.check_out_at" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1 text-xs font-semibold text-slate-400 hover:bg-white/5">
                                        Check Out
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

