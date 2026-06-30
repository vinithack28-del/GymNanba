<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    plan: Object,
    branches: Object,
    defaultGstRate: Number,
    defaultBranchIds: Array,
    showBranchSelector: {
        type: Boolean,
        default: true,
    },
    canEdit: Boolean,
});

const isEdit = !!props.plan;
const pageTitle = isEdit ? 'Edit plan' : 'Create plan';
const pageSub = isEdit ? `Update details for ${props.plan?.name}.` : 'Define a new membership plan for your members.';
const branchList = computed(() => Array.isArray(props.branches) ? props.branches : []);
const allBranchIds = computed(() => branchList.value.map((branch) => branch.id));
const canToggleAllBranches = computed(() => !isEdit && props.showBranchSelector && allBranchIds.value.length > 1);

const form = useForm({
    name: props.plan?.name || '',
    description: props.plan?.description || '',
    duration_value: props.plan?.duration_value || 1,
    duration_type: props.plan?.duration_type || 'days',
    price_rupees: props.plan?.price_paise ? (props.plan.price_paise / 100).toFixed(2) : '',
    gst_applicable: props.plan?.gst_applicable || false,
    gst_rate: props.plan?.gst_rate || props.defaultGstRate || 18,
    max_members: props.plan?.max_members ?? 0,
    grace_days: props.plan?.grace_days ?? 0,
    allow_freeze: props.plan?.allow_freeze ?? true,
    max_freeze_days: props.plan?.max_freeze_days ?? 30,
    inclusions: Array.isArray(props.plan?.inclusions) ? props.plan.inclusions.join(', ') : '',
    branch_ids: props.plan?.branches?.map(b => b.id) || props.defaultBranchIds || [],
    status: props.plan?.status || 'active',
});

const allBranchesSelected = computed(() =>
    allBranchIds.value.length > 0 && allBranchIds.value.every((id) => form.branch_ids.includes(id)),
);

const toggleAllBranches = () => {
    form.branch_ids = allBranchesSelected.value ? [] : [...allBranchIds.value];
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        price_paise: Math.round(Number(data.price_rupees || 0) * 100),
    }));

    if (isEdit) {
        form.put(`/plans/${props.plan.id}`);
    } else {
        form.post('/plans');
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="pageTitle" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Memberships / Plans</p>
                    <h1 class="mt-2 text-3xl font-semibold">{{ pageTitle }}</h1>
                    <p class="mt-1 text-slate-300">{{ pageSub }}</p>
                </div>
                <Link href="/plans" class="flex items-center gap-2 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <span>←</span> Back to plans
                </Link>
            </div>

            <form @submit.prevent="submit" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Plan name <span class="text-red-400">*</span></label>
                        <input v-model="form.name" type="text" placeholder="e.g. Monthly Premium" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required maxlength="80">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Description</label>
                        <textarea v-model="form.description" rows="3" placeholder="Short description shown to members…" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" maxlength="500"></textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Duration Value <span class="text-red-400">*</span></label>
                        <input v-model="form.duration_value" type="number" min="1" max="730" placeholder="e.g. 30" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Duration Type <span class="text-red-400">*</span></label>
                        <select v-model="form.duration_type" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            <option value="days">Days</option>
                            <option value="months">Months</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Price (₹) <span class="text-red-400">*</span></label>
                        <input v-model="form.price_rupees" type="number" min="0" step="0.01" placeholder="e.g. 1000" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Status</label>
                        <select v-model="form.status" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2">
                            <input v-model="form.gst_applicable" type="checkbox" class="text-orange-400">
                            <span class="text-sm font-medium">GST Applicable</span>
                        </label>
                    </div>
                    <div v-if="form.gst_applicable">
                        <label class="mb-2 block text-sm font-medium">GST Rate (%)</label>
                        <input v-model="form.gst_rate" type="number" step="0.1" placeholder="e.g. 18" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Member Cap</label>
                        <input v-model="form.max_members" type="number" min="0" placeholder="0 = unlimited" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <p class="mt-1 text-xs text-slate-400">0 = unlimited</p>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Grace Period (days)</label>
                        <input v-model="form.grace_days" type="number" min="0" max="30" placeholder="0" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <p class="mt-1 text-xs text-slate-400">Days after expiry access is still allowed</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2">
                            <input v-model="form.allow_freeze" type="checkbox" class="text-orange-400">
                            <span class="text-sm font-medium">Allow Freeze / Pause</span>
                        </label>
                    </div>
                    <div v-if="form.allow_freeze">
                        <label class="mb-2 block text-sm font-medium">Max Freeze Days / Year</label>
                        <input v-model="form.max_freeze_days" type="number" min="1" max="90" placeholder="30" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Inclusions</label>
                        <input v-model="form.inclusions" type="text" placeholder="Pool access, Steam room, Personal trainer..." class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <p class="mt-1 text-xs text-slate-400">Comma-separated list of what's included</p>
                    </div>
                    <div v-if="showBranchSelector || isEdit" class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Branches</label>
                        <div class="flex flex-wrap gap-2">
                            <label
                                v-if="canToggleAllBranches"
                                class="flex items-center gap-2 rounded-lg border border-orange-400/20 bg-orange-500/10 px-3 py-2"
                            >
                                <input
                                    type="checkbox"
                                    :checked="allBranchesSelected"
                                    class="text-orange-400"
                                    @change="toggleAllBranches"
                                >
                                <span class="text-sm font-medium">All branches</span>
                            </label>
                            <label v-for="branch in branchList" :key="branch.id" class="flex items-center gap-2 rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2">
                                <input type="checkbox" :value="branch.id" v-model="form.branch_ids" class="text-orange-400">
                                <span class="text-sm">{{ branch.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="mt-6 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                    {{ isEdit ? 'Update Plan' : 'Create Plan' }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>
