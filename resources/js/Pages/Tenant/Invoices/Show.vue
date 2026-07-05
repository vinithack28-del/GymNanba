<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    invoice: Object,
    tenant: Object,
    canVoid: Boolean,
});

const formatCurrency = (paise) => {
    if (!paise) return 'Rs. 0';
    return 'Rs. ' + (paise / 100).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const printInvoice = () => {
    window.print();
};

const voidForm = useForm({});
const voidInvoice = () => {
    if (confirm('Are you sure you want to void this invoice?')) {
        voidForm.post(`/tenant/invoices/${props.invoice.id}/void`);
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Invoice Details" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between no-print">
                <Link href="/tenant/invoices" class="text-sm text-slate-400"><- Invoices</Link>
                <div class="flex gap-2">
                    <button v-if="canVoid && invoice.status !== 'void'" @click="voidInvoice" class="rounded-lg border border-red-500/50 bg-red-500/10 px-3 py-1.5 text-sm text-red-400 hover:bg-red-500/20">
                        Void Invoice
                    </button>
                    <button @click="printInvoice" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">
                        Print
                    </button>
                </div>
            </div>

            <div id="printArea" class="max-w-2xl rounded-2xl border border-white/10 bg-white/5 p-8">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-lg font-bold">{{ tenant?.gym_name }}</h2>
                        <p class="mt-0.5 text-xs text-slate-400">{{ tenant?.address }}</p>
                        <p class="text-xs text-slate-400">{{ tenant?.city }}, {{ tenant?.state }}</p>
                        <p v-if="tenant?.gst_number" class="mt-1 text-xs font-mono text-slate-400">GSTIN: {{ tenant.gst_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">INVOICE</p>
                        <p class="mt-0.5 font-mono text-lg font-bold text-orange-400">{{ invoice.invoice_number }}</p>
                        <p class="mt-1 text-xs text-slate-400">Date: {{ formatDate(invoice.invoice_date) }}</p>
                        <p v-if="invoice.due_date" class="text-xs text-slate-400">Due: {{ formatDate(invoice.due_date) }}</p>
                    </div>
                </div>

                <div v-if="invoice.status === 'void'" class="mb-4 rounded-lg bg-red-500/10 py-2 text-center text-sm font-semibold text-red-400">
                    Void - {{ formatDate(invoice.voided_at) }}
                </div>

                <hr class="mb-5 border-white/10">

                <div class="mb-5">
                    <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-400">Bill To</p>
                    <p class="font-bold">{{ invoice.member?.name }}</p>
                    <p class="text-sm text-slate-400">{{ invoice.member?.phone }}</p>
                    <p class="text-xs text-slate-400">{{ invoice.member?.member_code }}</p>
                </div>

                <div class="mb-5 overflow-hidden rounded-lg border border-white/10">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-950/60">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-400">#</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-400">Description</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-slate-400">Qty</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-slate-400">Rate</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-slate-400">GST</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-400">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in invoice.line_items" :key="index" class="border-b border-white/10">
                                <td class="px-4 py-2">{{ index + 1 }}</td>
                                <td class="px-4 py-2">{{ item.description }}</td>
                                <td class="px-3 py-2 text-center">{{ item.quantity }}</td>
                                <td class="px-3 py-2 text-right">{{ formatCurrency(item.rate_paise) }}</td>
                                <td class="px-3 py-2 text-center">{{ item.gst_rate }}%</td>
                                <td class="px-4 py-2 text-right">{{ formatCurrency(item.amount_paise) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end">
                    <div class="w-64">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Subtotal</span>
                            <span>{{ formatCurrency(invoice.subtotal_paise) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">GST</span>
                            <span>{{ formatCurrency(invoice.gst_paise) }}</span>
                        </div>
                        <div class="mt-2 flex justify-between text-lg font-bold">
                            <span>Total</span>
                            <span>{{ formatCurrency(invoice.total_paise) }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="invoice.notes" class="mt-5 text-sm text-slate-400">
                    <p class="mb-1 font-medium text-slate-300">Notes:</p>
                    <p>{{ invoice.notes }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
