<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    plans: Array,
});

const isTrial = ref(false);
const showDeleteModal = ref(false);
const planToDelete = ref(null);
const editingPlan = ref(null);
const theme = ref(localStorage.getItem('gymos-theme') || 'dark');
const form = useForm({
    name: '',
    is_trial: false,
    trial_days: '',
    billing_cycle: 'Monthly',
    price_inr: '',
    max_members: 0,
    max_branches: 0,
    max_staff_accounts: 0,
    features: [],
    trial_eligible: false,
    description: '',
    status: 'active',
});

const toggleTrialMode = (checked) => {
    isTrial.value = checked;
    form.is_trial = checked;
};

const startEdit = (plan) => {
    editingPlan.value = plan;
    isTrial.value = plan.is_trial;
    form.name = plan.name;
    form.is_trial = plan.is_trial;
    form.trial_days = plan.trial_days;
    form.billing_cycle = plan.billing_cycle;
    form.price_inr = plan.price_paise / 100;
    form.max_members = plan.max_members;
    form.max_branches = plan.max_branches;
    form.max_staff_accounts = plan.max_staff_accounts;
    form.features = Object.keys(plan.feature_flags || {});
    form.trial_eligible = plan.trial_eligible;
    form.description = plan.description;
    form.status = plan.status;
};

const cancelEdit = () => {
    editingPlan.value = null;
    isTrial.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (editingPlan.value) {
        form.put(`/admin/plans/${editingPlan.value.id}`, {
            onSuccess: () => {
                editingPlan.value = null;
                form.reset();
                isTrial.value = false;
            },
        });
    } else {
        form.post('/admin/plans', {
            onSuccess: () => {
                form.reset();
                isTrial.value = false;
            },
        });
    }
};

const deletePlan = (planId, planName) => {
    planToDelete.value = { id: planId, name: planName };
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (planToDelete.value) {
        form.delete(`/admin/plans/${planToDelete.value.id}`);
        showDeleteModal.value = false;
        planToDelete.value = null;
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    planToDelete.value = null;
};

const formatCurrency = (paise) => {
    return 'Rs. ' + (paise / 100).toFixed(2);
};
</script>

<template>
    <AppLayout>
        <Head title="Plans" />
        
        <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <form @submit.prevent="submit" class="space-y-4 rounded-[2rem] border border-white/10 bg-white/5 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold" :class="editingPlan ? 'text-amber-300' : 'text-slate-200'">{{ editingPlan ? 'Edit Plan' : 'New Plan' }}</h2>
                    <button v-if="editingPlan" type="button" @click="cancelEdit" class="rounded-xl border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-medium text-slate-300 hover:bg-white/10 transition">
                        Cancel
                    </button>
                </div>
                <div v-if="form.errors && Object.keys(form.errors).length > 0" class="rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                    <div v-for="(error, field) in form.errors" :key="field">{{ error }}</div>
                </div>
                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                    <input type="checkbox" v-model="isTrial" @change="toggleTrialMode($event.target.checked)" class="h-4 w-4 rounded border-white/10 bg-slate-950/70 accent-orange-500">
                    <div>
                        <span class="text-sm font-semibold text-slate-200">Trial Plan</span>
                        <p class="text-xs text-slate-400 mt-0.5">Trial access only — no billing. Max 14 days.</p>
                    </div>
                    <span v-if="isTrial" class="ml-auto rounded-full bg-amber-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-amber-300">Trial</span>
                </label>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">Plan Name</label>
                    <input v-model="form.name" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none">
                </div>

                <div v-if="isTrial" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-200">Trial Duration (days)</label>
                        <input type="number" v-model="form.trial_days" placeholder="Max 14 days" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none">
                        <p class="mt-1 text-xs text-slate-400">Tenant loses access after these many days. Cannot exceed 14.</p>
                    </div>
                </div>

                <div v-if="!isTrial" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-200">Billing Cycle</label>
                            <select v-model="form.billing_cycle" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none">
                                <option value="Monthly">Monthly</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Annual">Annual</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-200">Price</label>
                            <input type="number" step="0.01" v-model="form.price_inr" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-200">Max Members</label>
                            <input type="number" v-model="form.max_members" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-200">Max Branches</label>
                            <input type="number" v-model="form.max_branches" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-200">Max Staff</label>
                            <input type="number" v-model="form.max_staff_accounts" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none">
                        </div>
                    </div>
                    <div>
                        <p class="mb-3 text-sm font-medium text-slate-200">Feature Flags</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label v-for="(label, value) in { pos: 'Inventory / POS', analytics: 'Advanced analytics', white_label: 'White-label', api_access: 'API access', biometric: 'Biometric integration', whatsapp: 'WhatsApp integration', gst_mode: 'GST compliance mode' }" :key="value" class="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300">
                                <input type="checkbox" :value="value" v-model="form.features" class="h-4 w-4 rounded border-white/10 bg-slate-950/70 accent-orange-500">
                                {{ label }}
                            </label>
                        </div>
                    </div>
                    <label class="flex items-center gap-3 text-sm text-slate-300">
                        <input type="checkbox" v-model="form.trial_eligible" class="h-4 w-4 rounded border-white/10 bg-slate-950/70 accent-orange-500">
                        Trial Eligible
                    </label>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">Description</label>
                    <textarea v-model="form.description" rows="2" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none"></textarea>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">Status</label>
                    <select v-model="form.status" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none">
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>

                <div class="flex gap-3">
                    <button v-if="editingPlan" type="button" @click="cancelEdit" class="flex-1 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-slate-300 hover:bg-white/10 transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                        {{ editingPlan ? 'Update Plan' : 'Save Plan' }}
                    </button>
                </div>
            </form>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                <div class="overflow-hidden rounded-[1.5rem] border border-white/10">
                    <table class="w-full divide-y divide-white/10 text-left text-sm">
                        <thead class="bg-slate-950/60 text-slate-300">
                            <tr>
                                <th class="px-4 py-3 font-medium">Plan</th>
                                <th class="px-4 py-3 font-medium">Cycle / Trial</th>
                                <th class="px-4 py-3 font-medium">Price</th>
                                <th class="px-4 py-3 font-medium">Limits</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="plan in plans" :key="plan.id">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold">{{ plan.name }}</p>
                                        <span v-if="plan.is_trial" class="rounded-full bg-amber-500/15 px-2 py-0.5 text-xs font-semibold uppercase tracking-[0.15em] text-amber-300">Trial</span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-400">{{ plan.description }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <span v-if="plan.is_trial" class="text-amber-300">{{ plan.trial_days }} days</span>
                                    <span v-else>{{ plan.billing_cycle }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span v-if="plan.is_trial" class="text-slate-400">Free</span>
                                    <span v-else>{{ formatCurrency(plan.price_paise) }}</span>
                                </td>
                                <td class="px-4 py-4 text-xs text-slate-300">
                                    <template v-if="plan.is_trial">
                                        <span class="text-slate-400">—</span>
                                    </template>
                                    <template v-else>
                                        Members: {{ plan.max_members || 'Unlimited' }}<br>
                                        Branches: {{ plan.max_branches || 'Unlimited' }}<br>
                                        Staff: {{ plan.max_staff_accounts || 'Unlimited' }}
                                    </template>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs uppercase tracking-[0.2em]" :class="plan.status === 'active' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-slate-500/15 text-slate-300'">
                                        {{ plan.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <button @click="startEdit(plan)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400 hover:bg-amber-500/20" :class="editingPlan?.id === plan.id ? 'ring-2 ring-amber-400' : ''" title="Edit">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg>
                                        </button>
                                        <button @click="deletePlan(plan.id, plan.name)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20" title="Delete">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Custom Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-[2rem] border p-6 shadow-2xl" :class="theme === 'dark' ? 'border-white/10 bg-slate-950' : 'border-slate-200 bg-white'">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-red-500/20 text-xl text-red-300">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold" :class="theme === 'dark' ? 'text-white' : 'text-slate-900'">Delete Plan</h3>
                        <p class="mt-2 text-sm" :class="theme === 'dark' ? 'text-slate-400' : 'text-slate-600'">
                            Are you sure you want to delete <span class="font-semibold" :class="theme === 'dark' ? 'text-white' : 'text-slate-900'">"{{ planToDelete?.name }}"</span>? This action cannot be undone.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        @click="cancelDelete"
                        class="rounded-2xl border px-5 py-2.5 text-sm font-semibold transition hover:opacity-90"
                        :class="theme === 'dark' ? 'border-white/10 bg-white/5 text-white hover:bg-white/10' : 'border-slate-200 bg-slate-100 text-slate-900 hover:bg-slate-200'"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmDelete"
                        class="rounded-2xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"
                        :disabled="form.processing"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>