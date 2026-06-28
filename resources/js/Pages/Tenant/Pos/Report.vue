<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    summary: Object,
    products: Object,
    canManageStock: Boolean,
});

const formatCurrency = (paise) => {
    if (!paise) return '₹0';
    return '₹' + (paise / 100).toFixed(2);
};
</script>

<template>
    <AppLayout>
        <Head title="Stock Report" />
        
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Gym Workspace</p>
                <h1 class="mt-2 text-3xl font-semibold">Stock Report</h1>
                <p class="mt-1 text-slate-300">Track inventory levels, restock items, and log adjustments with a full audit trail.</p>
            </div>

            <div class="grid gap-4 grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Products</p>
                    <p class="mt-2 text-2xl font-semibold">{{ summary?.products || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Low Stock</p>
                    <p class="mt-2 text-2xl font-semibold text-orange-400">{{ summary?.low_stock || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Stock Value</p>
                    <p class="mt-2 text-2xl font-semibold">{{ formatCurrency(summary?.stock_value_paise) }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Out of Stock</p>
                    <p class="mt-2 text-2xl font-semibold text-red-400">{{ summary?.out_of_stock || 0 }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div v-if="!products || products.length === 0" class="p-6 text-center text-sm text-slate-400">No products found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Product</th>
                                <th class="px-4 py-3">SKU</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3 text-right">Stock</th>
                                <th class="px-4 py-3 text-right">Threshold</th>
                                <th class="px-4 py-3 text-right">Value</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="product in products" :key="product.id" class="hover:bg-white/5">
                                <td class="px-4 py-3 font-semibold">{{ product.name }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ product.sku || '—' }}</td>
                                <td class="px-4 py-3">{{ product.category }}</td>
                                <td class="px-4 py-3 text-right">{{ product.stock_quantity }}</td>
                                <td class="px-4 py-3 text-right">{{ product.low_stock_threshold }}</td>
                                <td class="px-4 py-3 text-right">{{ formatCurrency(product.stock_quantity * product.price_paise) }}</td>
                                <td class="px-4 py-3">
                                    <span v-if="product.stock_quantity === 0" class="rounded-full px-2 py-1 text-xs font-bold bg-red-500/10 text-red-400">Out of Stock</span>
                                    <span v-else-if="product.is_low_stock" class="rounded-full px-2 py-1 text-xs font-bold bg-orange-500/10 text-orange-400">Low Stock</span>
                                    <span v-else class="rounded-full px-2 py-1 text-xs font-bold bg-emerald-500/10 text-emerald-400">In Stock</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>