<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tenant: Object,
});

const getStatusColors = (status) => {
    const colors = {
        active: { bg: 'bg-emerald-500/15', text: 'text-emerald-400', ring: 'ring-emerald-500/30', dot: 'bg-emerald-400' },
        trial: { bg: 'bg-sky-500/15', text: 'text-sky-400', ring: 'ring-sky-500/30', dot: 'bg-sky-400' },
        suspended: { bg: 'bg-red-500/15', text: 'text-red-400', ring: 'ring-red-500/30', dot: 'bg-red-400' },
        archived: { bg: 'bg-slate-500/15', text: 'text-slate-400', ring: 'ring-slate-500/30', dot: 'bg-slate-400' },
    };
    return colors[status] || { bg: 'bg-white/10', text: 'text-white', ring: 'ring-white/20', dot: 'bg-white' };
};

const latestSub = props.tenant.subscriptions?.sort((a, b) => b.id - a.id)[0];
const totalPaid = props.tenant.payments?.reduce((sum, p) => sum + p.amount_paise, 0) || 0;

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace(',', '').replaceAll('/', '-');
};

const formatCurrency = (paise) => {
    return 'Rs. ' + (paise / 100).toFixed(2);
};

const timeAgo = (date) => {
    if (!date) return '-';
    const now = new Date();
    const past = new Date(date);
    const seconds = Math.floor((now - past) / 1000);
    const intervals = {
        year: 31536000,
        month: 2592000,
        week: 604800,
        day: 86400,
        hour: 3600,
        minute: 60,
    };
    for (const [unit, secondsInUnit] of Object.entries(intervals)) {
        const interval = Math.floor(seconds / secondsInUnit);
        if (interval >= 1) {
            return `${interval} ${unit}${interval > 1 ? 's' : ''} ago`;
        }
    }
    return 'Just now';
};
</script>

<template>
    <AppLayout>
        <Head title="Tenant Details" />
        
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-300">Tenant</p>
                    <h1 class="mt-1 text-2xl font-semibold">{{ tenant.gym_name }}</h1>
                    <p class="mt-0.5 text-sm text-slate-300">Profile overview, subscriptions, and payments.</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <Link
                        href="/admin/tenants"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm font-medium transition hover:bg-white/10"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                        Back
                    </Link>
                    <Link
                        :href="`/admin/tenants/${tenant.id}/edit`"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-slate-950 transition hover:bg-amber-400"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg>
                        Edit
                    </Link>
                    <Link
                        :href="`/admin/tenants/${tenant.id}/delete`"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-600"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        Delete
                    </Link>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-500/20 text-lg font-bold text-orange-300">
                            {{ tenant.gym_name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">{{ tenant.gym_name }}</h2>
                            <p class="mt-1 text-sm text-slate-400">{{ tenant.business_type }} Ã‚- {{ tenant.city }}, {{ tenant.state }}</p>
                            <p class="mt-1 font-mono text-xs text-slate-500">{{ tenant.primary_domain }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold ring-1" :class="[getStatusColors(tenant.status).bg, getStatusColors(tenant.status).text, getStatusColors(tenant.status).ring]">
                        <span class="h-2 w-2 rounded-full" :class="getStatusColors(tenant.status).dot"></span>
                        {{ tenant.status.charAt(0).toUpperCase() + tenant.status.slice(1) }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 border-t border-white/10 pt-4 sm:grid-cols-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Members</p>
                        <p class="mt-1 text-xl font-bold">{{ tenant.members_count }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Plan</p>
                        <p class="mt-1 text-sm font-semibold">{{ latestSub?.plan?.name || '-' }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ latestSub ? `${formatCurrency(latestSub.price_paise)} / ${latestSub.plan.billing_cycle}` : '' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Total Paid</p>
                        <p class="mt-1 text-sm font-semibold">{{ formatCurrency(totalPaid) }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ tenant.payments?.length || 0 }} payment{{ tenant.payments?.length === 1 ? '' : 's' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Joined</p>
                        <p class="mt-1 text-sm font-semibold">{{ formatDate(tenant.created_at) }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ timeAgo(tenant.created_at) }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 xl:grid-cols-[1.15fr_0.85fr]">
                <div class="space-y-4">
                    <section class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Owners</h3>
                        <div class="space-y-4">
                            <div v-if="tenant.users && tenant.users.length > 0" v-for="(owner, index) in tenant.users.filter(u => u.role === 'tenant_owner')" :key="owner.id" class="rounded-xl border border-white/10 bg-white/5 p-3">
                                <div class="mb-3 text-xs uppercase tracking-[0.18em] text-slate-400">{{ index === 0 ? 'Primary Owner' : `Additional Owner ${index}` }}</div>
                                <div class="grid gap-3 text-sm">
                                    <div class="flex justify-between gap-3"><span class="text-slate-400">Name:</span><span class="font-semibold">{{ owner.name }}</span></div>
                                    <div class="flex justify-between gap-3"><span class="text-slate-400">Email:</span><span class="font-semibold">{{ owner.email }}</span></div>
                                    <div class="flex justify-between gap-3"><span class="text-slate-400">Phone:</span><span class="font-semibold">{{ owner.phone || '-' }}</span></div>
                                </div>
                            </div>
                            <div v-else class="text-sm text-slate-400">No owners found.</div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Business Contact</h3>
                        <div class="grid grid-cols-2 gap-0">
                            <div class="border-b border-r border-white/10 p-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.07em] text-slate-400 mb-1">GST Number</p>
                                <p class="text-sm font-semibold font-mono">{{ tenant.gst_number || '-' }}</p>
                            </div>
                            <div class="border-b border-white/10 p-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.07em] text-slate-400 mb-1">Language</p>
                                <p class="text-sm font-semibold">{{ tenant.default_language?.toUpperCase() }}</p>
                            </div>
                            <div class="col-span-2 p-3 pt-0">
                                <p class="text-xs font-semibold uppercase tracking-[0.07em] text-slate-400 mb-1">Address</p>
                                <p class="text-sm font-semibold">{{ tenant.address }}</p>
                                <p class="text-sm text-slate-400 mt-0.5">{{ tenant.city }}, {{ tenant.state }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Routing & Technical</h3>
                        <div class="grid grid-cols-2 gap-0">
                            <div class="border-b border-r border-white/10 p-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.07em] text-slate-400 mb-1">Subdomain</p>
                                <p class="text-sm font-semibold font-mono">{{ tenant.subdomain }}.gymos.in</p>
                            </div>
                            <div class="border-b border-white/10 p-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.07em] text-slate-400 mb-1">Domain Mode</p>
                                <p class="text-sm font-semibold">{{ tenant.domain_mode?.charAt(0).toUpperCase() + tenant.domain_mode?.slice(1) }}</p>
                            </div>
                            <div class="border-b border-r border-white/10 p-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.07em] text-slate-400 mb-1">Database Mode</p>
                                <p class="text-sm font-semibold">{{ tenant.database_mode?.charAt(0).toUpperCase() + tenant.database_mode?.slice(1) }}</p>
                            </div>
                            <div class="border-b border-white/10 p-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.07em] text-slate-400 mb-1">Database Name</p>
                                <p class="text-sm font-semibold font-mono">{{ tenant.database_name || 'Main database' }}</p>
                            </div>
                            <div v-if="tenant.domain_mode === 'separate' && tenant.custom_domain" class="col-span-2 p-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.07em] text-slate-400 mb-1">Custom Domain</p>
                                <p class="text-sm font-semibold font-mono">{{ tenant.custom_domain }}</p>
                            </div>
                        </div>
                    </section>

                    <section v-if="tenant.notes" class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Internal Notes</h3>
                        <p class="text-sm leading-relaxed">{{ tenant.notes }}</p>
                    </section>
                </div>

                <div class="space-y-4">
                    <section class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Subscriptions</h3>
                            <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-0.5 text-xs font-semibold">{{ tenant.subscriptions?.length || 0 }}</span>
                        </div>
                        <div class="space-y-3">
                            <div v-if="tenant.subscriptions && tenant.subscriptions.length > 0" v-for="subscription in tenant.subscriptions.sort((a, b) => b.id - a.id)" :key="subscription.id" class="rounded-xl border border-white/10 bg-white/5 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold">{{ subscription.plan?.name }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ formatCurrency(subscription.price_paise) }} / {{ subscription.plan?.billing_cycle }}</p>
                                    </div>
                                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="[getStatusColors(subscription.status).bg, getStatusColors(subscription.status).text, getStatusColors(subscription.status).ring]">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="getStatusColors(subscription.status).dot"></span>
                                        {{ subscription.status?.charAt(0).toUpperCase() + subscription.status?.slice(1) }}
                                    </span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400">
                                    <span v-if="subscription.start_date">Start: {{ formatDate(subscription.start_date) }}</span>
                                    <span v-if="subscription.end_date">End: {{ formatDate(subscription.end_date) }}</span>
                                    <span v-if="subscription.trial_end_date">Trial ends: {{ formatDate(subscription.trial_end_date) }}</span>
                                </div>
                            </div>
                            <div v-else class="flex flex-col items-center gap-1.5 py-5 text-center">
                                <svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                                <p class="text-sm text-slate-400">No subscriptions found.</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Payments</h3>
                            <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-0.5 text-xs font-semibold">{{ tenant.payments?.length || 0 }}</span>
                        </div>
                        <div class="space-y-3">
                            <div v-if="tenant.payments && tenant.payments.length > 0" v-for="payment in tenant.payments.sort((a, b) => new Date(b.paid_at) - new Date(a.paid_at))" :key="payment.id" class="rounded-xl border border-white/10 bg-white/5 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-base font-bold">{{ formatCurrency(payment.amount_paise) }}</p>
                                    <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-0.5 text-xs font-medium capitalize">{{ payment.payment_method }}</span>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400">
                                    <span v-if="payment.paid_at">{{ formatDateTime(payment.paid_at) }}</span>
                                    <span v-if="payment.transaction_ref" class="font-mono">{{ payment.transaction_ref }}</span>
                                    <span v-if="payment.admin">By {{ payment.admin.name }}</span>
                                </div>
                            </div>
                            <div v-else class="flex flex-col items-center gap-1.5 py-5 text-center">
                                <svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                <p class="text-sm text-slate-400">No payments recorded.</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

