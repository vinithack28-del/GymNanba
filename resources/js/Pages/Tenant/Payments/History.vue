<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    activeTab: String,
    todaySummary: Object,
    payments: Object,
    branches: Object,
});

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatCurrency = (paise) => {
    return '₹' + (paise / 100).toFixed(0);
};

const getStatusColor = (status) => {
    return status === 'active' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-red-500/15 text-red-300';
};
</script>

<template>
    <AppLayout>
        <Head title="Payments" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div class="inline-flex rounded-xl p-1 bg-slate-950/50 border border-white/10">
                    <Link href="/tenant/payments?tab=dues" :class="['px-4 py-2 rounded-lg text-sm font-semibold transition', activeTab === 'dues' ? 'bg-white/5 text-slate-200' : 'text-slate-400']">
                        Dues
                    </Link>
                    <Link href="/tenant/payments?tab=history" :class="['px-4 py-2 rounded-lg text-sm font-semibold transition', activeTab === 'history' ? 'bg-white/5 text-slate-200' : 'text-slate-400']">
                        History
                    </Link>
                </div>
                <Link href="/tenant/payments/collect" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    + Collect Fee
                </Link>
            </div>

            <div v-if="activeTab === 'history'">
                <div class="grid gap-4 grid-cols-2 sm:grid-cols-2">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Today's Count</p>
                        <p class="mt-1 text-2xl font-bold">{{ todaySummary?.count || 0 }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Today's Total</p>
                        <p class="mt-1 text-2xl font-bold text-orange-400">{{ formatCurrency(todaySummary?.total_paise || 0) }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="mb-1 block text-xs text-slate-400">Search</label>
                        <input type="text" placeholder="Search by name or receipt..." class="w-52 rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-400">Branch</label>
                        <select class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 outline-none">
                            <option value="">All</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-400">Method</label>
                        <select class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 outline-none">
                            <option value="">All</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="upi">UPI</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-400">Status</label>
                        <select class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 outline-none">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="voided">Voided</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-white/10 bg-white/5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-950/60 text-xs font-medium text-slate-400">
                                <tr>
                                    <th class="px-4 py-2.5">Receipt</th>
                                    <th class="px-4 py-2.5">Member</th>
                                    <th class="px-4 py-2.5">Plan</th>
                                    <th class="px-4 py-2.5">Method</th>
                                    <th class="px-4 py-2.5 text-right">Amount</th>
                                    <th class="px-4 py-2.5">Date</th>
                                    <th class="px-4 py-2.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-white/5">
                                <tr v-for="payment in payments" :key="payment.id" class="hover:bg-white/5">
                                    <td class="px-4 py-3 font-mono text-xs">{{ payment.receipt_number }}</td>
                                    <td class="px-4 py-3">{{ payment.member?.name || '—' }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ payment.plan?.name || '—' }}</td>
                                    <td class="px-4 py-3 text-slate-400 capitalize">{{ payment.method }}</td>
                                    <td class="px-4 py-3 text-right">{{ formatCurrency(payment.total_paise) }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ formatDate(payment.payment_date) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="getStatusColor(payment.status)">
                                            {{ payment.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-else>
                <div class="rounded-xl border border-red-400/20 bg-red-500/10 p-4 mb-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-red-400">Total Due</p>
                        <p class="mt-0.5 text-xs text-red-300">{{ payments?.length || 0 }} members due</p>
                    </div>
                    <p class="text-2xl font-bold text-red-400">₹0</p>
                </div>

                <div class="overflow-hidden rounded-xl border border-white/10 bg-white/5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-950/60 text-xs font-medium text-slate-400">
                                <tr>
                                    <th class="px-4 py-2.5">Member</th>
                                    <th class="px-4 py-2.5">Plan</th>
                                    <th class="px-4 py-2.5 text-right">Total</th>
                                    <th class="px-4 py-2.5 text-right">Paid</th>
                                    <th class="px-4 py-2.5 text-right text-red-400">Due</th>
                                    <th class="px-4 py-2.5">Due Date</th>
                                    <th class="px-4 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-white/5">
                                <tr v-for="payment in payments" :key="payment.id" class="hover:bg-white/5">
                                    <td class="px-4 py-3">{{ payment.member?.name || '—' }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ payment.plan?.name || '—' }}</td>
                                    <td class="px-4 py-3 text-right">{{ formatCurrency(payment.total_paise) }}</td>
                                    <td class="px-4 py-3 text-right">{{ formatCurrency(payment.paid_paise) }}</td>
                                    <td class="px-4 py-3 text-right text-red-400 font-semibold">{{ formatCurrency(payment.due_paise) }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ formatDate(payment.due_date) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <Link :href="`/tenant/payments/collect?member_id=${payment.member_id}`" class="text-orange-400 hover:text-orange-300 text-sm">Collect</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>