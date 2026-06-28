<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    plans: Array,
});

const isTrial = ref(false);
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

const submit = () => {
    form.post('/admin/plans', {
        onSuccess: () => {
            form.reset();
            isTrial.value = false;
        },
    });
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
                    <input v-model="form.name" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none" required>
                </div>

                <div v-if="isTrial" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-200">Trial Duration (days)</label>
                        <input type="number" v-model="form.trial_days" min="1" max="14" placeholder="Max 14 days" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none">
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
                    <select v-model="form.status" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none" required>
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>

                <button type="submit" class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    Save Plan
                </button>
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
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>