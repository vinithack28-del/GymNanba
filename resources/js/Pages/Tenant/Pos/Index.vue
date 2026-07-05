<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    products: Object,
    stats: Object,
    categories: Object,
    statuses: Object,
    canManageProducts: Boolean,
});

const productRows = computed(() => props.products?.data || []);

const formatCurrency = (paise) => {
    if (!paise) return 'Rs. 0';
    return 'Rs. ' + (paise / 100).toFixed(2);
};
</script>

<template>
    <AppLayout>
        <Head title="Products" />
        
        <div class="flex flex-col gap-5">
            <div>
                <h1 class="mt-2 text-3xl font-semibold">Products</h1>
                <p class="mt-1 text-slate-300">Manage merchandise, supplements, and consumables available at the counter.</p>
            </div>

            <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Total Products</p>
                    <p class="mt-2 text-2xl font-semibold">{{ stats?.total || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Active</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-400">{{ stats?.active || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Inactive</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-400">{{ stats?.inactive || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Low Stock</p>
                    <p class="mt-2 text-2xl font-semibold text-orange-400">{{ stats?.low_stock || 0 }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex flex-1 items-center gap-2 rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2">
                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                        <input type="text" placeholder="Search products..." class="w-full bg-transparent text-sm text-slate-300 outline-none">
                    </div>
                    <select class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
                    </select>
                    <select class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <option value="">All Statuses</option>
                        <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
                    </select>
                    <Link v-if="canManageProducts" href="/tenant/pos/products/create" class="rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                        + Add Product
                    </Link>
                </div>
            </div>

            <div v-if="productRows.length === 0" class="flex min-h-24 flex-col items-center justify-center gap-4 rounded-2xl border border-white/10 bg-white/5 px-6 py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-orange-500/10 text-2xl">ðŸ›’</div>
                <p class="text-base font-bold">No products found</p>
                <p class="text-sm text-slate-400">Get started by adding your first product.</p>
                <Link v-if="canManageProducts" href="/tenant/pos/products/create" class="mt-2 rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">Add Product</Link>
            </div>

            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div v-for="product in productRows" :key="product.id" class="rounded-2xl border border-white/10 bg-white/5 overflow-hidden">
                    <div class="flex h-32 items-center justify-center bg-slate-950/50">
                        <svg class="h-8 w-8 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 6h15l-1.5 8.5a2 2 0 0 1-2 1.5H9a2 2 0 0 1-2-1.6L5.2 4H3"/><circle cx="9" cy="20" r="1.2"/><circle cx="18" cy="20" r="1.2"/></svg>
                    </div>
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-semibold">{{ product.name }}</h3>
                                <p class="mt-1 truncate text-xs text-slate-400">{{ product.sku || product.category }}</p>
                            </div>
                            <span class="rounded-full border border-white/10 bg-slate-950/50 px-2 py-0.5 text-xs">{{ product.category }}</span>
                        </div>
                        <div class="mt-4 flex items-end justify-between gap-3">
                            <span class="text-base font-semibold">{{ formatCurrency(product.price_paise) }}</span>
                            <span class="text-xs" :class="product.is_low_stock ? 'text-orange-400' : 'text-slate-400'">Stock {{ product.stock_quantity }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

