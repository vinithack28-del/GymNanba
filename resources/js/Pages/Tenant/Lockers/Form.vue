<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    locker: Object,
    branches: Object,
    statuses: Object,
    selectedBranchId: String,
});

const isEdit = !!props.locker;
const pageTitle = isEdit ? 'Edit Locker' : 'Add Locker';
const pageSub = isEdit ? `Update details for ${props.locker?.locker_number}.` : 'Register a locker and keep it ready for member assignment.';

const form = useForm({
    branch_id: props.selectedBranchId || props.locker?.branch_id || '',
    locker_number: props.locker?.locker_number || '',
    status: props.locker?.status || 'active',
    location: props.locker?.location || '',
    notes: props.locker?.notes || '',
});

const submit = () => {
    if (isEdit) {
        form.put(`/tenant/lockers/${props.locker.id}`);
    } else {
        form.post('/tenant/lockers');
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="pageTitle" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Operations</p>
                    <h1 class="mt-2 text-3xl font-semibold">{{ pageTitle }}</h1>
                    <p class="mt-1 text-slate-300">{{ pageSub }}</p>
                </div>
                <Link href="/tenant/lockers" class="flex items-center gap-2 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <span>←</span> Back to Lockers
                </Link>
            </div>

            <form @submit.prevent="submit" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <div class="grid gap-5 md:grid-cols-2">
                    <div v-if="!selectedBranchId">
                        <label class="mb-1 block text-sm font-medium text-slate-300">Branch <span class="text-red-400">*</span></label>
                        <select v-model="form.branch_id" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            <option value="">Select branch…</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Locker No. <span class="text-red-400">*</span></label>
                        <input v-model="form.locker_number" type="text" placeholder="e.g. L-07" maxlength="20" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-300">Status <span class="text-red-400">*</span></label>
                        <select v-model="form.status" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-300">Location / Zone</label>
                        <input v-model="form.location" type="text" placeholder="e.g. Male changing room, Zone A" maxlength="200" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-300">Notes</label>
                        <textarea v-model="form.notes" rows="5" placeholder="Extra details…" maxlength="1000" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button type="submit" class="rounded-full bg-orange-500 px-6 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                        {{ isEdit ? 'Update Locker' : 'Add Locker' }}
                    </button>
                    <Link href="/tenant/lockers" class="rounded-full border border-white/10 bg-slate-950/50 px-6 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>