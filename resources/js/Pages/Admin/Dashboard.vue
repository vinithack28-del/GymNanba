<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    auth: Object,
    totalTenants: Number,
    activeTenants: Number,
    trialTenants: Number,
    mrr: Number,
    renewalsThisWeek: Number,
    trialsExpiring: Number,
    mrrTrend: Array,
    maxTrend: Number,
    recentActivities: Array,
    renewalsDue: Array,
});

const form = useForm({});

const logout = () => {
    form.post('/logout');
};

const formatCurrency = (value) => {
    return 'Rs. ' + (value / 100).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return 'Ongoing';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatDateTime = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
                <p class="text-sm text-slate-400">Total Tenants</p>
                <p class="mt-4 text-4xl font-semibold">{{ totalTenants ?? 0 }}</p>
                <p class="mt-2 text-sm text-slate-300">All non-archived gym tenants on the platform.</p>
            </div>
            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
                <p class="text-sm text-slate-400">Active Tenants</p>
                <p class="mt-4 text-4xl font-semibold text-emerald-300">{{ activeTenants ?? 0 }}</p>
                <p class="mt-2 text-sm text-slate-300">Paying gyms currently active and operational.</p>
            </div>
            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
                <p class="text-sm text-slate-400">Trials Active</p>
                <p class="mt-4 text-4xl font-semibold text-sky-300">{{ trialTenants ?? 0 }}</p>
                <p class="mt-2 text-sm text-slate-300">Tenants with trial subscriptions still within term.</p>
            </div>
            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
                <p class="text-sm text-slate-400">MRR</p>
                <p class="mt-4 text-4xl font-semibold text-orange-300">{{ formatCurrency(mrr ?? 0) }}</p>
                <p class="mt-2 text-sm text-slate-300">Monthly recurring revenue normalized across billing cycles.</p>
            </div>
            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
                <p class="text-sm text-slate-400">Renewals</p>
                <p class="mt-4 text-4xl font-semibold text-amber-300">{{ renewalsThisWeek ?? 0 }}</p>
                <p class="mt-2 text-sm text-slate-300">Subscriptions ending within the next 7 days.</p>
            </div>
            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
                <p class="text-sm text-slate-400">Trials Expiring</p>
                <p class="mt-4 text-4xl font-semibold text-fuchsia-300">{{ trialsExpiring ?? 0 }}</p>
                <p class="mt-2 text-sm text-slate-300">Trial tenants that need follow-up before conversion.</p>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
            <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-400">MRR Trend</p>
                        <h3 class="mt-2 text-2xl font-semibold">Revenue Snapshot</h3>
                    </div>
                    <span class="rounded-full bg-slate-950/70 px-3 py-1 text-xs text-slate-300">Last 12 months</span>
                </div>

                <div class="mt-8 flex h-64 items-end gap-3">
                    <div v-for="point in mrrTrend" :key="point.label" class="flex flex-1 flex-col items-center gap-3">
                        <div class="flex h-52 w-full items-end rounded-full bg-slate-950/70 p-2">
                            <div
                                class="w-full rounded-full bg-[linear-gradient(180deg,#f97316_0%,#22c55e_100%)]"
                                :style="{ height: Math.max(10, (point.value / maxTrend) * 100) + '%' }"
                            ></div>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-slate-400">{{ point.label }}</p>
                            <p class="mt-1 text-xs font-medium text-slate-200">Rs. {{ point.value.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                <p class="text-sm text-slate-400">Recent Activity</p>
                <h3 class="mt-2 text-2xl font-semibold">Audit Highlights</h3>

                <div class="mt-6 space-y-4">
                    <article v-for="activity in recentActivities" :key="activity.id" class="rounded-2xl border border-white/10 bg-slate-950/60 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold">{{ activity.action_type }}</p>
                                <p class="mt-1 text-sm text-slate-300">{{ activity.target_name || 'Platform event' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ activity.actor_name }} · {{ formatDateTime(activity.created_at) }}</p>
                            </div>
                            <span class="rounded-full bg-white/5 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-sky-300">
                                {{ activity.target_type }}
                            </span>
                        </div>
                    </article>
                </div>
            </section>
        </div>

        <section class="mt-6 rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-400">Renewals Due</p>
                    <h3 class="mt-2 text-2xl font-semibold">Upcoming Renewals</h3>
                </div>
                <a href="/admin/subscriptions" class="rounded-full border border-white/10 bg-slate-950/70 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">
                    View Subscriptions
                </a>
            </div>

            <div class="mt-6 overflow-hidden rounded-[1.5rem] border border-white/10">
                <table class="w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-slate-950/60 text-slate-300">
                        <tr>
                            <th class="px-4 py-3 font-medium">Gym</th>
                            <th class="px-4 py-3 font-medium">Plan</th>
                            <th class="px-4 py-3 font-medium">Renewal date</th>
                            <th class="px-4 py-3 font-medium">MRR</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 bg-white/5">
                        <tr v-if="renewalsDue && renewalsDue.length > 0" v-for="renewal in renewalsDue" :key="renewal.id">
                            <td class="px-4 py-3">{{ renewal.tenant?.gym_name }}</td>
                            <td class="px-4 py-3">{{ renewal.plan?.name }}</td>
                            <td class="px-4 py-3">{{ formatDate(renewal.end_date) }}</td>
                            <td class="px-4 py-3">{{ formatCurrency(renewal.price_paise) }}</td>
                        </tr>
                        <tr v-else>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-400">No renewals are currently due.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AppLayout>
</template>