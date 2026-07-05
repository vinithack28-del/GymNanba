<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    payment: Object,
    tenant: Object,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const formatCurrency = (paise) => {
    return 'Rs. ' + (paise / 100).toFixed(2);
};

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <AppLayout>
        <Head :title="`Receipt - ${payment.receipt_number}`" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <Link href="/payments/history?tab=history" class="text-sm text-slate-400 hover:text-orange-400">
                    <- History
                </Link>
                <button @click="printReceipt" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">
                    Print
                </button>
            </div>

            <div class="mx-auto max-w-lg rounded-xl border border-white/10 bg-white/5 p-8">
                <div class="text-center mb-6">
                    <h2 class="text-lg font-bold">{{ tenant?.name }}</h2>
                    <p v-if="payment.branch" class="text-sm text-slate-400">{{ payment.branch.name }}</p>
                    <p class="mt-3 text-xs font-bold uppercase tracking-wider text-slate-400">Receipt</p>
                    <p class="mt-1 font-mono text-xl font-bold text-orange-400">{{ payment.receipt_number }}</p>
                </div>

                <div v-if="payment.status === 'voided'" class="mb-4 rounded-lg bg-red-500/10 py-2 text-center text-sm font-semibold text-red-400">
                    Voided - {{ formatDate(payment.voided_at) }}
                </div>

                <hr class="mb-6 border-white/10">

                <div class="mb-5">
                    <p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-400">Member</p>
                    <p class="font-semibold">{{ payment.member?.name }}</p>
                    <p class="text-sm text-slate-400">{{ payment.member?.phone }}</p>
                    <p v-if="payment.plan" class="mt-1 text-sm text-slate-400">Plan: {{ payment.plan.name }}</p>
                </div>

                <div class="mb-5 rounded-lg border border-white/10 bg-slate-950/50 p-4">
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="text-slate-400">Amount</span>
                        <span>{{ formatCurrency(payment.amount_paise) }}</span>
                    </div>
                    <div v-if="payment.gst_paise > 0" class="mb-2 flex justify-between text-sm">
                        <span class="text-slate-400">GST</span>
                        <span>{{ formatCurrency(payment.gst_paise) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-white/10 pt-2 font-bold">
                        <span>Total</span>
                        <span class="text-orange-400">{{ formatCurrency(payment.total_paise) }}</span>
                    </div>
                </div>

                <div class="space-y-1.5 text-sm">
                    <p v-if="payment.splits && payment.splits.length > 0" class="mb-1 text-xs font-bold uppercase tracking-wide text-slate-400">Payment Mode</p>
                    <div v-for="split in payment.splits" :key="split.id" class="flex justify-between">
                        <span class="text-slate-400">
                            {{ split.method.charAt(0).toUpperCase() + split.method.slice(1) }}
                            <span v-if="split.reference" class="font-mono text-xs">({{ split.reference }})</span>
                        </span>
                        <span>{{ formatCurrency(split.amount_paise) }}</span>
                    </div>
                    <div v-if="payment.is_partial" class="flex justify-between border-t border-white/10 pt-1 font-semibold">
                        <span class="text-slate-400">Collected Now</span>
                        <span class="text-emerald-400">{{ formatCurrency(payment.paid_paise) }}</span>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-xs text-slate-400">Payment Date: {{ formatDate(payment.payment_date) }}</p>
                    <p v-if="payment.due_date" class="text-xs text-slate-400">Due Date: {{ formatDate(payment.due_date) }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

