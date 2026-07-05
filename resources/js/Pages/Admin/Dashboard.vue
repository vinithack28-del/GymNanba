<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

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
const page = usePage();
const translations = computed(() => page.props.translations?.common || {});

const t = (key, fallback = '') => {
    return key.split('.').reduce((value, part) => value?.[part], translations.value) || fallback;
};

const logout = () => {
    form.post('/logout');
};

const formatCurrency = (value) => {
    return 'Rs. ' + (value / 100).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return t('admin.dashboard.ongoing', 'Ongoing');
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const formatDateTime = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace(',', '').replaceAll('/', '-');
};
</script>

<template>
    <AppLayout>
        <Head :title="t('admin.dashboard.title', 'Dashboard')" />
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-3.5">
                <p class="text-sm text-slate-400">{{ t('admin.dashboard.total_tenants', 'Total Tenants') }}</p>
                <p class="mt-2 text-2xl font-semibold">{{ totalTenants ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-300">{{ t('admin.dashboard.total_tenants_help', 'All non-archived gym tenants.') }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-3.5">
                <p class="text-sm text-slate-400">{{ t('admin.dashboard.active_tenants', 'Active Tenants') }}</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-300">{{ activeTenants ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-300">{{ t('admin.dashboard.active_tenants_help', 'Paying gyms currently active.') }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-3.5">
                <p class="text-sm text-slate-400">{{ t('admin.dashboard.trials_active', 'Trials Active') }}</p>
                <p class="mt-2 text-2xl font-semibold text-sky-300">{{ trialTenants ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-300">{{ t('admin.dashboard.trials_active_help', 'Trial subscriptions within term.') }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-3.5">
                <p class="text-sm text-slate-400">{{ t('admin.dashboard.mrr', 'MRR') }}</p>
                <p class="mt-2 text-2xl font-semibold text-orange-300">{{ formatCurrency(mrr ?? 0) }}</p>
                <p class="mt-1 text-xs text-slate-300">{{ t('admin.dashboard.mrr_help', 'Recurring revenue normalized monthly.') }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-3.5">
                <p class="text-sm text-slate-400">{{ t('admin.dashboard.renewals', 'Renewals') }}</p>
                <p class="mt-2 text-2xl font-semibold text-amber-300">{{ renewalsThisWeek ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-300">{{ t('admin.dashboard.renewals_help', 'Ending in the next 7 days.') }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-3.5">
                <p class="text-sm text-slate-400">{{ t('admin.dashboard.trials_expiring', 'Trials Expiring') }}</p>
                <p class="mt-2 text-2xl font-semibold text-fuchsia-300">{{ trialsExpiring ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-300">{{ t('admin.dashboard.trials_expiring_help', 'Trials needing follow-up.') }}</p>
            </div>
        </div>

        <div class="mt-4 grid gap-4 xl:grid-cols-[1.4fr_0.9fr]">
            <section class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-400">{{ t('admin.dashboard.mrr_trend', 'MRR Trend') }}</p>
                        <h3 class="mt-1 text-lg font-semibold">{{ t('admin.dashboard.revenue_snapshot', 'Revenue Snapshot') }}</h3>
                    </div>
                    <span class="rounded-lg bg-slate-950/70 px-2.5 py-1 text-xs text-slate-300">{{ t('admin.dashboard.last_12_months', 'Last 12 months') }}</span>
                </div>

                <div class="mt-5 flex h-52 items-end gap-2">
                    <div v-for="point in mrrTrend" :key="point.label" class="flex flex-1 flex-col items-center gap-2">
                        <div class="flex h-40 w-full items-end rounded-full bg-slate-950/70 p-1.5">
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

            <section class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">{{ t('admin.dashboard.recent_activity', 'Recent Activity') }}</p>
                <h3 class="mt-1 text-lg font-semibold">{{ t('admin.dashboard.audit_highlights', 'Audit Highlights') }}</h3>

                <div class="mt-4 space-y-2">
                    <article v-for="activity in recentActivities" :key="activity.id" class="rounded-xl border border-white/10 bg-slate-950/60 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold">{{ activity.action_type }}</p>
                                <p class="mt-0.5 text-sm text-slate-300">{{ activity.target_name || t('admin.dashboard.platform_event', 'Platform event') }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ activity.actor_name }} - {{ formatDateTime(activity.created_at) }}</p>
                            </div>
                            <span class="rounded-lg bg-white/5 px-2 py-1 text-[10px] uppercase tracking-[0.16em] text-sky-300">
                                {{ activity.target_type }}
                            </span>
                        </div>
                    </article>
                </div>
            </section>
        </div>

        <section class="mt-4 rounded-2xl border border-white/10 bg-white/5 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-400">{{ t('admin.dashboard.renewals_due', 'Renewals Due') }}</p>
                    <h3 class="mt-1 text-lg font-semibold">{{ t('admin.dashboard.upcoming_renewals', 'Upcoming Renewals') }}</h3>
                </div>
                <a href="/admin/subscriptions" class="rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-900">
                    {{ t('admin.dashboard.view_subscriptions', 'View Subscriptions') }}
                </a>
            </div>

            <div class="mt-4 overflow-hidden rounded-xl border border-white/10">
                <table class="w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-slate-950/60 text-slate-300">
                        <tr>
                            <th class="px-3 py-2 font-medium">{{ t('admin.dashboard.gym', 'Gym') }}</th>
                            <th class="px-3 py-2 font-medium">{{ t('admin.dashboard.plan', 'Plan') }}</th>
                            <th class="px-3 py-2 font-medium">{{ t('admin.dashboard.renewal_date', 'Renewal date') }}</th>
                            <th class="px-3 py-2 font-medium">{{ t('admin.dashboard.mrr', 'MRR') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 bg-white/5">
                        <tr v-if="renewalsDue && renewalsDue.length > 0" v-for="renewal in renewalsDue" :key="renewal.id">
                            <td class="px-3 py-2">{{ renewal.tenant?.gym_name }}</td>
                            <td class="px-3 py-2">{{ renewal.plan?.name }}</td>
                            <td class="px-3 py-2">{{ formatDate(renewal.end_date) }}</td>
                            <td class="px-3 py-2">{{ formatCurrency(renewal.price_paise) }}</td>
                        </tr>
                        <tr v-else>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-400">{{ t('admin.dashboard.no_renewals_due', 'No renewals are currently due.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AppLayout>
</template>

