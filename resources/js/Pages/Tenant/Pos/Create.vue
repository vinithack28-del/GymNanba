<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    product: Object,
    categories: Object,
});

const isEdit = !!props.product;
const pageTitle = isEdit ? 'Edit Product' : 'Add Product';
const pageSub = isEdit ? `Update details for ${props.product?.name}.` : 'Add a new product to your inventory.';

const form = useForm({
    name: props.product?.name || '',
    sku: props.product?.sku || '',
    category: props.product?.category || '',
    price_paise: props.product?.price_paise ? (props.product.price_paise / 100).toFixed(2) : '',
    gst_rate: props.product?.gst_rate || 18,
    stock_quantity: props.product?.stock_quantity || 0,
    low_stock_threshold: props.product?.low_stock_threshold || 5,
    status: props.product?.status || 'active',
    description: props.product?.description || '',
});

const submit = () => {
    if (isEdit) {
        form.put(`/tenant/pos/products/${props.product.id}`);
    } else {
        form.post('/tenant/pos/products');
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="pageTitle" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="mt-2 text-3xl font-semibold">{{ pageTitle }}</h1>
                    <p class="mt-1 text-slate-300">{{ pageSub }}</p>
                </div>
                <Link href="/tenant/pos/products" class="flex items-center gap-2 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <span>â†</span> Back to Products
                </Link>
            </div>

            <form @submit.prevent="submit" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-300">Product Name <span class="text-red-400">*</span></label>
                        <input v-model="form.name" type="text" placeholder="e.g. Protein Powder" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">SKU</label>
                        <input v-model="form.sku" type="text" placeholder="e.g. PROD-001" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Category <span class="text-red-400">*</span></label>
                        <select v-model="form.category" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            <option value="">Select categoryâ€¦</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Price (â‚¹) <span class="text-red-400">*</span></label>
                        <input v-model="form.price_paise" type="number" step="0.01" min="0" placeholder="0.00" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">GST Rate (%)</label>
                        <select v-model="form.gst_rate" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                            <option value="0">0%</option>
                            <option value="5">5%</option>
                            <option value="12">12%</option>
                            <option value="18">18%</option>
                            <option value="28">28%</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Stock Quantity <span class="text-red-400">*</span></label>
                        <input v-model="form.stock_quantity" type="number" min="0" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Low Stock Threshold</label>
                        <input v-model="form.low_stock_threshold" type="number" min="0" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Status <span class="text-red-400">*</span></label>
                        <select v-model="form.status" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-300">Description</label>
                        <textarea v-model="form.description" rows="3" placeholder="Product descriptionâ€¦" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button type="submit" class="rounded-full bg-orange-500 px-6 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                        {{ isEdit ? 'Update Product' : 'Add Product' }}
                    </button>
                    <Link href="/tenant/pos/products" class="rounded-full border border-white/10 bg-slate-950/50 px-6 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

