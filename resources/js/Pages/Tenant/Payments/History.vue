<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    activeTab: String,
    todaySummary: Object,
    payments: Object,
    duePayments: Object,
    totalDuePaise: Number,
    branches: Object,
});

const paymentRows = computed(() => props.payments?.data || []);
const dueRows = computed(() => props.duePayments?.data || []);
const totalDuePaise = computed(() => Number(props.totalDuePaise || 0));
const activePaginator = computed(() => props.activeTab === 'history' ? props.payments : props.duePayments);

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const formatCurrency = (paise) => {
    return 'Rs. ' + (Number(paise || 0) / 100).toFixed(0);
};

const getStatusColor = (status) => {
    return status === 'active' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-red-500/15 text-red-300';
};
</script>

<template>
    <AppLayout>
        <Head title="Payments" />

        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="inline-flex rounded-xl border border-white/10 bg-slate-950/50 p-1">
                    <Link href="/payments/history?tab=dues" :class="['rounded-lg px-3 py-1.5 text-xs font-semibold transition md:text-sm', activeTab === 'dues' ? 'bg-white/5 text-slate-200' : 'text-slate-400']">
                        Dues
                    </Link>
                    <Link href="/payments/history?tab=history" :class="['rounded-lg px-3 py-1.5 text-xs font-semibold transition md:text-sm', activeTab === 'history' ? 'bg-white/5 text-slate-200' : 'text-slate-400']">
                        History
                    </Link>
                </div>
                <Link href="/payments/collect" class="rounded-lg bg-orange-500 px-3 py-1.5 text-xs font-semibold text-slate-950 hover:bg-orange-400 md:text-sm">
                    + Collect Fee
                </Link>
            </div>

            <div v-if="activeTab === 'history'" class="flex flex-col gap-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Today's Count</p>
                        <p class="mt-1 text-xl font-bold">{{ todaySummary?.count || 0 }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Today's Total</p>
                        <p class="mt-1 text-xl font-bold text-orange-400">{{ formatCurrency(todaySummary?.total_paise || 0) }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-end gap-2">
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
                                    <th class="px-3 py-2">Receipt</th>
                                    <th class="px-3 py-2">Member</th>
                                    <th class="px-3 py-2">Plan</th>
                                    <th class="px-3 py-2">Method</th>
                                    <th class="px-3 py-2 text-right">Amount</th>
                                    <th class="px-3 py-2">Date</th>
                                    <th class="px-3 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-white/5">
                                <tr v-for="payment in paymentRows" :key="payment.id" class="hover:bg-white/5">
                                    <td class="px-3 py-2.5 font-mono text-xs">{{ payment.receipt_number }}</td>
                                    <td class="px-3 py-2.5">{{ payment.member?.name || '-' }}</td>
                                    <td class="px-3 py-2.5 text-slate-400">{{ payment.plan?.name || '-' }}</td>
                                    <td class="px-3 py-2.5 text-slate-400 capitalize">{{ payment.method }}</td>
                                    <td class="px-3 py-2.5 text-right">{{ formatCurrency(payment.total_paise) }}</td>
                                    <td class="px-3 py-2.5 text-slate-400">{{ formatDate(payment.payment_date) }}</td>
                                    <td class="px-3 py-2.5">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="getStatusColor(payment.status)">
                                            {{ payment.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="activePaginator?.links?.length" class="flex flex-col items-center justify-between gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 sm:flex-row">
                    <p class="text-xs text-slate-400">
                        Showing {{ activePaginator.from || 0 }} to {{ activePaginator.to || 0 }} of {{ activePaginator.total || 0 }}
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-1.5">
                        <Link
                            v-for="link in activePaginator.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                link.active ? 'bg-orange-500 text-slate-950' : 'bg-white/5 text-slate-300 hover:bg-white/10',
                                !link.url ? 'pointer-events-none opacity-45' : ''
                            ]"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>

            <div v-else class="flex flex-col gap-4">
                <div class="flex items-center justify-between rounded-xl border border-red-400/20 bg-red-500/10 p-3">
                    <div>
                        <p class="text-sm font-semibold text-red-400">Total Due</p>
                        <p class="mt-0.5 text-xs text-red-300">{{ dueRows.length || 0 }} pending payment{{ dueRows.length === 1 ? '' : 's' }}</p>
                    </div>
                    <p class="text-xl font-bold text-red-400">{{ formatCurrency(totalDuePaise) }}</p>
                </div>

                <div class="overflow-hidden rounded-xl border border-white/10 bg-white/5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-950/60 text-xs font-medium text-slate-400">
                                <tr>
                                    <th class="px-3 py-2">Member</th>
                                    <th class="px-3 py-2">Branch</th>
                                    <th class="px-3 py-2">Plan</th>
                                    <th class="px-3 py-2">Receipt</th>
                                    <th class="px-3 py-2 text-right">Total</th>
                                    <th class="px-3 py-2 text-right">Paid</th>
                                    <th class="px-3 py-2 text-right text-red-400">Due</th>
                                    <th class="px-3 py-2">Due Date</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-white/5">
                                <tr v-for="payment in dueRows" :key="payment.id" class="hover:bg-white/5">
                                    <td class="px-3 py-2.5">
                                        <p class="font-semibold text-slate-100">{{ payment.member?.name || '-' }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ payment.member?.phone || payment.member?.member_code || '-' }}</p>
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-400">{{ payment.member?.branch?.name || payment.branch?.name || '-' }}</td>
                                    <td class="px-3 py-2.5 text-slate-400">{{ payment.plan?.name || '-' }}</td>
                                    <td class="px-3 py-2.5 font-mono text-xs text-slate-400">{{ payment.receipt_number }}</td>
                                    <td class="px-3 py-2.5 text-right">{{ formatCurrency(payment.total_paise) }}</td>
                                    <td class="px-3 py-2.5 text-right">{{ formatCurrency(payment.paid_paise) }}</td>
                                    <td class="px-3 py-2.5 text-right font-semibold text-red-400">{{ formatCurrency(payment.due_paise) }}</td>
                                    <td class="px-3 py-2.5 text-slate-400">{{ formatDate(payment.due_date) }}</td>
                                    <td class="px-3 py-2.5 text-right">
                                        <Link :href="`/payments/collect?member_id=${payment.member_id}`" class="text-sm text-orange-400 hover:text-orange-300">Collect</Link>
                                    </td>
                                </tr>
                                <tr v-if="dueRows.length === 0">
                                    <td colspan="9" class="px-4 py-8 text-center text-sm text-slate-400">
                                        No payment dues found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="activePaginator?.links?.length" class="flex flex-col items-center justify-between gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 sm:flex-row">
                    <p class="text-xs text-slate-400">
                        Showing {{ activePaginator.from || 0 }} to {{ activePaginator.to || 0 }} of {{ activePaginator.total || 0 }}
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-1.5">
                        <Link
                            v-for="link in activePaginator.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                link.active ? 'bg-orange-500 text-slate-950' : 'bg-white/5 text-slate-300 hover:bg-white/10',
                                !link.url ? 'pointer-events-none opacity-45' : ''
                            ]"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

