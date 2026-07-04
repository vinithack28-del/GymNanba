<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    sales: Object,
    summary: Object,
});

const saleRows = computed(() => props.sales?.data || []);

const formatCurrency = (paise) => {
    if (!paise) return 'â‚¹0';
    return 'â‚¹' + (paise / 100).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return 'â€”';
    return new Date(date).toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace(',', '').replaceAll('/', '-');
};
</script>

<template>
    <AppLayout>
        <Head title="Sales History" />
        
        <div class="flex flex-col gap-5">
            <div>
                <h1 class="mt-2 text-3xl font-semibold">Sales History</h1>
                <p class="mt-1 text-slate-300">View all POS sales transactions.</p>
            </div>

            <div class="grid gap-4 grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Today Sales</p>
                    <p class="mt-2 text-2xl font-semibold">{{ summary?.today_count || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Today Total</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-400">{{ formatCurrency(summary?.today_total_paise) }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Month Total</p>
                    <p class="mt-2 text-2xl font-semibold">{{ formatCurrency(summary?.month_total_paise) }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Today GST</p>
                    <p class="mt-2 text-2xl font-semibold text-sky-400">{{ formatCurrency(summary?.gst_paise) }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div v-if="saleRows.length === 0" class="p-6 text-center text-sm text-slate-400">No sales found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Sale #</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Member</th>
                                <th class="px-4 py-3">Items</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                                <th class="px-4 py-3 text-right">GST</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="sale in saleRows" :key="sale.id" class="hover:bg-white/5">
                                <td class="px-4 py-3 font-mono font-bold text-orange-400">{{ sale.sale_number }}</td>
                                <td class="px-4 py-3">{{ formatDate(sale.created_at) }}</td>
                                <td class="px-4 py-3">{{ sale.member?.name || 'Walk-in' }}</td>
                                <td class="px-4 py-3">{{ sale.items_count || 0 }} items</td>
                                <td class="px-4 py-3 text-right">{{ formatCurrency(sale.subtotal_paise) }}</td>
                                <td class="px-4 py-3 text-right">{{ formatCurrency(sale.gst_paise) }}</td>
                                <td class="px-4 py-3 text-right font-bold">{{ formatCurrency(sale.total_paise) }}</td>
                                <td class="px-4 py-3">
                                    <Link :href="`/tenant/pos/sales/${sale.id}`" class="text-orange-400 hover:text-orange-300 text-sm">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

