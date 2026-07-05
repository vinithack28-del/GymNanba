<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    equipment: Object,
    types: Object,
    statuses: Object,
});

const editing = !!props.equipment;
const pageTitle = editing ? 'Edit Equipment' : 'Add Equipment';

const form = useForm({
    name: props.equipment?.name || '',
    type: props.equipment?.type || '',
    status: props.equipment?.status || 'operational',
    brand: props.equipment?.brand || '',
    model: props.equipment?.model || '',
    purchase_date: props.equipment?.purchase_date || '',
    warranty_expiry: props.equipment?.warranty_expiry || '',
    purchase_price: props.equipment?.purchase_price || '0',
    notes: props.equipment?.notes || '',
});

const submit = () => {
    if (editing) {
        form.put(`/equipment/${props.equipment.id}`);
    } else {
        form.post('/equipment');
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="pageTitle" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>                    
                    <h1 class="mt-2 text-3xl font-semibold">{{ pageTitle }}</h1>
                    <p class="mt-1 text-slate-300">{{ editing ? 'Update equipment details.' : 'Capture equipment details, status, and purchase information.' }}</p>
                </div>
                <Link href="/equipment" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                    Back to Equipment
                </Link>
            </div>

            <form @submit.prevent="submit" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Equipment Name <span class="text-red-400">*</span></label>
                        <input v-model="form.name" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" placeholder="e.g. Treadmill Pro 3000" maxlength="150" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Type <span class="text-red-400">*</span></label>
                        <select v-model="form.type" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            <option value="">Select typeâ€¦</option>
                            <option v-for="(label, value) in types" :key="value" :value="value">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Status</label>
                        <select v-model="form.status" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                            <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Brand</label>
                        <input v-model="form.brand" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" placeholder="Brand name" maxlength="100">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Model</label>
                        <input v-model="form.model" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" placeholder="Model name" maxlength="100">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Purchase Date</label>
                        <input v-model="form.purchase_date" type="date" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Warranty Expiry</label>
                        <input v-model="form.warranty_expiry" type="date" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Purchase Price (Rs.)</label>
                        <input v-model="form.purchase_price" type="number" min="0" step="1" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" placeholder="0">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Notes</label>
                        <textarea v-model="form.notes" rows="3" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <Link href="/equipment" class="rounded-2xl border border-white/10 bg-slate-950/50 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-white/5">Cancel</Link>
                    <button type="submit" class="rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">{{ editing ? 'Update Equipment' : 'Add Equipment' }}</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

