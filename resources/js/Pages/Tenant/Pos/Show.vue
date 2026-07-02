<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    sale: Object,
    canRefund: Boolean,
});

const formatCurrency = (paise) => {
    if (!paise) return '₹0';
    return '₹' + (paise / 100).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <AppLayout>
        <Head :title="sale.bill_number" />
        
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Gym Workspace</p>
                <h1 class="mt-2 text-3xl font-semibold">Receipt {{ sale.bill_number }}</h1>
                <p class="mt-1 text-slate-300">Review line items, payment method, tax, and linked member details for this bill.</p>
            </div>

            <div class="mx-auto max-w-5xl flex flex-col gap-5">
                <section class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Bill Number</p>
                            <h2 class="mt-1 text-2xl font-semibold">{{ sale.bill_number }}</h2>
                            <p class="mt-2 text-sm text-slate-400">{{ formatDate(sale.created_at) }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button @click="printReceipt" class="rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">Print Receipt</button>
                            <Link href="/tenant/pos/sales" class="rounded-full border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/5">Back to Sales</Link>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Member</p>
                            <p class="mt-2 font-semibold">{{ sale.member?.name || 'Walk-in' }}</p>
                            <p class="mt-1 text-sm text-slate-400">{{ sale.member?.member_code || 'No member linked' }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Staff</p>
                            <p class="mt-2 font-semibold">{{ sale.seller?.name || 'Owner / system' }}</p>
                            <p class="mt-1 text-sm text-slate-400">{{ sale.branch?.name || '—' }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Method</p>
                            <p class="mt-2 font-semibold">{{ sale.method_label }}</p>
                            <p class="mt-1 text-sm text-slate-400">{{ sale.reference || 'No reference' }}</p>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-950/60">
                                <tr>
                                    <th class="px-5 py-3 text-left font-medium text-slate-400">Product</th>
                                    <th class="px-5 py-3 text-left font-medium text-slate-400">Qty</th>
                                    <th class="px-5 py-3 text-left font-medium text-slate-400">Unit</th>
                                    <th class="px-5 py-3 text-left font-medium text-slate-400">Subtotal</th>
                                    <th class="px-5 py-3 text-left font-medium text-slate-400">GST</th>
                                    <th class="px-5 py-3 text-left font-medium text-slate-400">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in sale.items" :key="item.id" class="border-t border-white/10">
                                    <td class="px-5 py-4">
                                        <p class="font-semibold">{{ item.product_name }}</p>
                                        <p class="text-xs text-slate-400">{{ item.gst_rate }}% GST</p>
                                    </td>
                                    <td class="px-5 py-4">{{ item.qty }}</td>
                                    <td class="px-5 py-4">{{ formatCurrency(item.unit_price_paise) }}</td>
                                    <td class="px-5 py-4">{{ formatCurrency(item.line_subtotal_paise) }}</td>
                                    <td class="px-5 py-4">{{ formatCurrency(item.gst_paise) }}</td>
                                    <td class="px-5 py-4 font-semibold">{{ formatCurrency(item.line_total_paise) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="grid gap-5 lg:grid-cols-[1fr_0.8fr]">
                    <section class="rounded-2xl border border-white/10 bg-white/5 p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Notes</p>
                        <p class="mt-3 text-sm">{{ sale.notes || 'No notes recorded for this bill.' }}</p>

                        <div v-if="sale.refunded_at" class="mt-5 rounded-2xl border border-red-500/20 bg-red-500/10 p-4">
                            <p class="text-sm font-semibold text-red-300">Refunded</p>
                            <p class="mt-2 text-sm text-red-200">Refunded on {{ formatDate(sale.refunded_at) }} by {{ sale.refundActor?.name || 'Unknown' }}.</p>
                            <p class="mt-1 text-sm text-red-200">{{ sale.refund_reason }}</p>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-white/10 bg-white/5 p-6">
                        <div class="flex flex-col gap-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Subtotal</span>
                                <span>{{ formatCurrency(sale.subtotal_paise) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">GST</span>
                                <span>{{ formatCurrency(sale.gst_paise) }}</span>
                            </div>
                            <div class="mt-2 flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span>{{ formatCurrency(sale.total_paise) }}</span>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>