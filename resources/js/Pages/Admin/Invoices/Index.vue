<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tab: String,
    renewalsDue: Object,
    tenants: Array,
    plans: Array,
    payments: Object,
});

const currentTab = ref(props.tab || 'renewal_due');

const formatCurrency = (paise) => {
    return 'Rs. ' + (paise / 100).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatDateTime = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getPaymentTypeClass = (type) => {
    const classes = {
        new: 'bg-sky-500/15 text-sky-300',
        renewal: 'bg-emerald-500/15 text-emerald-300',
        part_payment: 'bg-amber-500/15 text-amber-300',
    };
    return classes[type] || 'bg-slate-500/15 text-slate-300';
};

const getDaysLeft = (expDate) => {
    if (!expDate) return null;
    const now = new Date();
    const exp = new Date(expDate);
    const diff = Math.ceil((exp - now) / (1000 * 60 * 60 * 24));
    return diff;
};

const getDaysLeftClass = (days) => {
    if (days === null) return 'bg-slate-500/15 text-slate-300';
    if (days <= 0) return 'bg-red-500/15 text-red-300';
    if (days <= 7) return 'bg-amber-500/15 text-amber-300';
    return 'bg-sky-500/15 text-sky-300';
};
</script>

<template>
    <AppLayout>
        <Head title="Invoices & Payments" />
        
        <div class="flex flex-col gap-6">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Finance</p>
                <h1 class="mt-2 text-3xl font-semibold">Invoices & Payments</h1>
                <p class="mt-1 text-slate-300">Process renewals, record part payments, and review collection history.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link
                    href="/admin/invoices?tab=renewal_due"
                    :class="['rounded-full border px-4 py-2 text-sm font-semibold transition', currentTab === 'renewal_due' ? 'bg-orange-500 border-orange-500 text-slate-950' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10']"
                >
                    Renewal Due
                    <span v-if="renewalsDue?.length" class="ml-2 rounded-full bg-black/20 px-1 py-0.5 text-xs">{{ renewalsDue.length }}</span>
                </Link>
                <Link
                    href="/admin/invoices?tab=history"
                    :class="['rounded-full border px-4 py-2 text-sm font-semibold transition', currentTab === 'history' ? 'bg-orange-500 border-orange-500 text-slate-950' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10']"
                >
                    Payment History
                </Link>
            </div>

            <div v-if="currentTab === 'renewal_due'" class="grid gap-6 xl:grid-cols-[1fr_1.4fr]">
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.07em] text-slate-200 mb-2">Process Renewal</p>
                    <p class="text-sm text-slate-400 mb-4">Choose a tenant, pick a plan, enter amount. Part payments allowed.</p>
                    <p class="text-xs text-slate-400 italic">Renewal form functionality to be implemented with Vue reactive forms.</p>
                </div>

                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.07em] text-slate-200">Tenants Due for Renewal</p>
                        <span class="rounded-full bg-amber-500/15 px-2 py-0.5 text-xs font-semibold text-amber-300">{{ renewalsDue?.length || 0 }}</span>
                    </div>

                    <div v-if="!renewalsDue || renewalsDue.length === 0" class="text-center py-8 text-slate-400 text-sm">
                        No renewals due. All subscriptions are current.
                    </div>

                    <div v-else class="flex flex-col gap-3">
                        <div v-for="t in renewalsDue" :key="t.id" class="rounded-[1.2rem] border border-white/10 bg-slate-950/50 p-4 cursor-pointer hover:border-orange-400 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold">{{ t.gym_name }}</p>
                                    <p class="text-xs text-slate-400 mt-1">{{ t._sub?.plan?.name || '—' }}</p>
                                </div>
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold uppercase" :class="getDaysLeftClass(getDaysLeft(t._sub?.end_date || t._sub?.trial_end_date))">
                                    {{ Math.abs(getDaysLeft(t._sub?.end_date || t._sub?.trial_end_date)) || 0 }}d left
                                </span>
                            </div>
                            <div class="flex justify-between mt-2 text-xs text-slate-400">
                                <span>Expires: {{ formatDate(t._sub?.end_date || t._sub?.trial_end_date) }}</span>
                                <span v-if="t._balance_paise > 0" class="font-semibold text-amber-400">Balance: {{ formatCurrency(t._balance_paise) }}</span>
                                <span v-else>{{ formatCurrency(t._sub?.plan?.price_paise) }} / {{ t._sub?.plan?.billing_cycle }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="currentTab === 'history'" class="flex flex-col gap-6">
                <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
                    <div class="px-6 py-4 border-b border-white/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.07em] text-slate-200">Payment History</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-white/10 text-left text-sm">
                            <thead class="bg-slate-950/60 text-slate-300">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Gym</th>
                                    <th class="px-4 py-3 font-medium">Plan</th>
                                    <th class="px-4 py-3 font-medium">Type</th>
                                    <th class="px-4 py-3 font-medium">Amount</th>
                                    <th class="px-4 py-3 font-medium">Method</th>
                                    <th class="px-4 py-3 font-medium">Reference</th>
                                    <th class="px-4 py-3 font-medium">Date</th>
                                    <th class="px-4 py-3 font-medium">By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-white/5">
                                <tr v-if="payments.data && payments.data.length > 0" v-for="payment in payments.data" :key="payment.id">
                                    <td class="px-4 py-4 font-semibold">{{ payment.tenant?.gym_name }}</td>
                                    <td class="px-4 py-4 text-slate-400">{{ payment.subscription?.plan?.name || '—' }}</td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold uppercase" :class="getPaymentTypeClass(payment.payment_type)">
                                            {{ payment.payment_type?.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 font-semibold">{{ formatCurrency(payment.amount_paise) }}</td>
                                    <td class="px-4 py-4 text-slate-400">{{ payment.payment_method }}</td>
                                    <td class="px-4 py-4 text-slate-400 font-mono text-xs">{{ payment.transaction_ref || '—' }}</td>
                                    <td class="px-4 py-4 text-slate-400">{{ formatDate(payment.paid_at) }}</td>
                                    <td class="px-4 py-4 text-slate-400 text-xs">{{ payment.admin?.name || 'System' }}</td>
                                </tr>
                                <tr v-if="payment?.notes">
                                    <td colspan="8" class="px-4 py-2 text-xs text-slate-400 border-t-0">↳ {{ payment.notes }}</td>
                                </tr>
                                <tr v-else>
                                    <td colspan="8" class="px-4 py-8 text-center text-slate-400">No payments recorded yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="payments.links" class="flex items-center gap-2">
                    <Link v-for="link in payments.links" :key="link.label" :href="link.url || '#'" :class="['rounded-lg px-3 py-2 text-sm', link.active ? 'bg-orange-500 text-slate-950' : 'bg-white/5 text-slate-300 hover:bg-white/10']" v-html="link.label"></Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>