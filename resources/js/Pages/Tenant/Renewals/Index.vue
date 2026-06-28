<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    renewals: Object,
    stats: Object,
    plans: Object,
    tab: String,
    from: String,
    to: String,
});

const tabs = {
    all: 'All',
    expired: 'Expired',
    today: 'Today',
    '3days': '3 Days',
    '7days': '7 Days',
    '30days': '30 Days',
    custom: 'Custom',
};

const statCards = [
    { label: 'Expired', value: stats?.expired || 0, tab: 'expired', color: '#E24B4A' },
    { label: 'Today', value: stats?.today || 0, tab: 'today', color: '#f97316' },
    { label: '7 Days', value: stats?.seven_days || 0, tab: '7days', color: '#EAB308' },
    { label: '30 Days', value: stats?.thirty_days || 0, tab: '30days', color: 'var(--app-text)' },
];

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const getDaysRemaining = (expiryDate) => {
    if (!expiryDate) return '—';
    const today = new Date();
    const expiry = new Date(expiryDate);
    const diff = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
    return diff;
};

const getDaysColor = (days) => {
    if (days < 0) return 'text-red-400';
    if (days <= 3) return 'text-orange-400';
    if (days <= 7) return 'text-yellow-400';
    return 'text-emerald-400';
};
</script>

<template>
    <AppLayout>
        <Head title="Renewals" />
        
        <div class="flex flex-col gap-5">
            <div>
                <h1 class="text-3xl font-semibold">Renewals</h1>
                <p class="mt-1 text-slate-300">Track membership expirations and send renewal reminders.</p>
            </div>

            <div class="grid gap-4 grid-cols-2 sm:grid-cols-4">
                <Link v-for="card in statCards" :key="card.tab" 
                      :href="`/tenant/renewals?tab=${card.tab}`"
                      class="rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:opacity-90"
                      :class="tab === card.tab ? 'border-orange-400 bg-orange-500/10' : ''">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">{{ card.label }}</p>
                    <p class="mt-2 text-2xl font-semibold" :style="{ color: card.color }">{{ card.value }}</p>
                </Link>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex flex-wrap gap-1">
                        <Link v-for="(label, val) in tabs" :key="val"
                              :href="`/tenant/renewals?tab=${val}`"
                              class="rounded-lg px-3 py-1.5 text-sm transition"
                              :class="tab === val ? 'bg-orange-500 text-slate-950 font-semibold' : 'text-slate-300 hover:bg-white/5'">
                            {{ label }}
                        </Link>
                    </div>

                    <div class="ml-auto flex flex-wrap items-center gap-2">
                        <form v-if="tab === 'custom'" method="GET" action="/tenant/renewals" class="flex items-center gap-1">
                            <input type="hidden" name="tab" value="custom">
                            <input type="date" name="from" :value="from" class="rounded-lg border border-white/10 bg-slate-950/50 px-2 py-1 text-xs text-slate-300 outline-none focus:border-orange-400">
                            <span class="text-xs text-slate-400">to</span>
                            <input type="date" name="to" :value="to" class="rounded-lg border border-white/10 bg-slate-950/50 px-2 py-1 text-xs text-slate-300 outline-none focus:border-orange-400">
                            <button type="submit" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1 text-xs text-slate-300 hover:bg-white/5">Apply</button>
                        </form>

                        <select class="rounded-lg border border-white/10 bg-slate-950/50 px-2 py-1.5 text-xs text-slate-300 outline-none focus:border-orange-400">
                            <option value="">All Plans</option>
                            <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
                        </select>

                        <Link href="/tenant/renewals?export=csv" class="flex items-center gap-1 rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-xs text-slate-300 hover:bg-white/5">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Export
                        </Link>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div v-if="!renewals || renewals.length === 0" class="p-6 text-center text-sm text-slate-400">No renewals found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Member</th>
                                <th class="px-4 py-3">Plan</th>
                                <th class="px-4 py-3">Branch</th>
                                <th class="px-4 py-3">Expiry Date</th>
                                <th class="px-4 py-3">Days Remaining</th>
                                <th class="px-4 py-3">Phone</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="renewal in renewals" :key="renewal.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <Link :href="`/tenant/members/${renewal.member_id}`" class="font-semibold hover:text-orange-400">{{ renewal.member_name }}</Link>
                                </td>
                                <td class="px-4 py-3 text-slate-400">{{ renewal.plan_name }}</td>
                                <td class="px-4 py-3">{{ renewal.branch_name || '—' }}</td>
                                <td class="px-4 py-3">{{ formatDate(renewal.expiry_date) }}</td>
                                <td class="px-4 py-3">
                                    <span :class="getDaysColor(getDaysRemaining(renewal.expiry_date))">
                                        {{ getDaysRemaining(renewal.expiry_date) }} days
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400">{{ renewal.member_phone }}</td>
                                <td class="px-4 py-3">
                                    <Link :href="`/tenant/members/${renewal.member_id}`" class="text-orange-400 hover:text-orange-300 text-sm">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>