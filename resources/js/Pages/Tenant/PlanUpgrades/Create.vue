<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    member: Object,
    currentPlan: Object,
    availablePlans: Array,
});

const form = useForm({
    member_id: props.member.id,
    new_plan_id: '',
    upgrade_charge_type: 'difference_amount',
    notes: '',
});

const selectedNewPlan = computed(() => {
    return props.availablePlans.find(p => p.id === form.new_plan_id);
});

const calculateUpgradeAmount = () => {
    if (!selectedNewPlan.value || !props.currentPlan) return 0;
    
    const chargeType = form.upgrade_charge_type || selectedNewPlan.value.upgrade_charge_type;
    
    switch (chargeType) {
        case 'full_new_plan':
            return selectedNewPlan.value.total_price_paise / 100;
        case 'difference_amount':
            return Math.max(0, (selectedNewPlan.value.total_price_paise - props.currentPlan.total_price_paise) / 100);
        case 'custom_amount':
            return (selectedNewPlan.value.upgrade_custom_amount || 0) / 100;
        default:
            return 0;
    }
};

const submit = () => {
    form.post('/upgrades');
};

const formatAmount = (paise) => `Rs. ${Number((paise || 0) / 100).toFixed(2)}`;

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const calculateRemainingDays = () => {
    if (!props.member.expiry_date) return 0;
    const exp = new Date(props.member.expiry_date);
    const today = new Date();
    return Math.ceil((exp - today) / 86400000);
};
</script>

<template>
    <AppLayout>
        <Head title="Upgrade Plan" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-semibold">Upgrade Plan</h1>
                    <p class="mt-1 text-slate-300">Upgrade {{ member.name }}'s membership to a higher plan</p>
                </div>
                <Link href="/members" class="flex items-center gap-2 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <span><-</span> Back to Members
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Current Plan Info -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-lg font-semibold">Current Plan</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Member</span>
                            <span class="text-sm font-medium">{{ member.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Current Plan</span>
                            <span class="text-sm font-medium">{{ currentPlan.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Current Price</span>
                            <span class="text-sm">{{ formatAmount(currentPlan.price_paise) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Validity</span>
                            <span class="text-sm">{{ currentPlan.duration_label }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Start Date</span>
                            <span class="text-sm">{{ formatDate(member.start_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Expiry Date</span>
                            <span class="text-sm">{{ formatDate(member.expiry_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Remaining Days</span>
                            <span class="text-sm font-medium">{{ calculateRemainingDays() }} days</span>
                        </div>
                    </div>
                </div>

                <!-- Upgrade Form -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-lg font-semibold">Upgrade Details</h2>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Select New Plan <span class="text-red-400">*</span></label>
                            <select v-model="form.new_plan_id" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required @change="form.upgrade_charge_type = ''">
                                <option value="">Select a plan</option>
                                <option v-for="plan in availablePlans" :key="plan.id" :value="plan.id">
                                    {{ plan.name }} - {{ formatAmount(plan.price_paise) }} ({{ plan.duration_label }})
                                </option>
                            </select>
                            <p v-if="form.errors.new_plan_id" class="mt-1 text-xs font-semibold text-red-400">{{ form.errors.new_plan_id }}</p>
                        </div>

                        <div v-if="selectedNewPlan && selectedNewPlan.has_upgrade_charge">
                            <label class="mb-2 block text-sm font-medium">Upgrade Charge Type</label>
                            <select v-model="form.upgrade_charge_type" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                <option value="full_new_plan">Full New Plan Amount</option>
                                <option value="difference_amount">Difference Amount Only</option>
                                <option v-if="selectedNewPlan.upgrade_charge_type === 'custom_amount'" value="custom_amount">Custom Amount</option>
                            </select>
                            <p v-if="form.errors.upgrade_charge_type" class="mt-1 text-xs font-semibold text-red-400">{{ form.errors.upgrade_charge_type }}</p>
                        </div>

                        <div v-if="selectedNewPlan" class="rounded-lg border border-white/10 bg-slate-950/50 p-4">
                            <h3 class="mb-3 text-sm font-semibold">Upgrade Summary</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-400">Current Plan</span>
                                    <span>{{ currentPlan.name }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-400">Current Price</span>
                                    <span>{{ formatAmount(currentPlan.price_paise) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-400">New Plan</span>
                                    <span>{{ selectedNewPlan.name }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-400">New Plan Price</span>
                                    <span>{{ formatAmount(selectedNewPlan.price_paise) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-400">New Validity</span>
                                    <span>{{ selectedNewPlan.duration_label }}</span>
                                </div>
                                <div class="mt-3 flex justify-between border-t border-white/10 pt-3">
                                    <span class="text-sm font-medium">Upgrade Amount</span>
                                    <span class="text-sm font-bold text-orange-400">{{ formatAmount(calculateUpgradeAmount() * 100) }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Notes</label>
                            <textarea v-model="form.notes" rows="3" placeholder="Optional notes for this upgrade..." class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" maxlength="500"></textarea>
                            <p v-if="form.errors.notes" class="mt-1 text-xs font-semibold text-red-400">{{ form.errors.notes }}</p>
                        </div>

                        <button type="submit" class="w-full rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing || !form.new_plan_id">
                            {{ form.processing ? 'Processing...' : 'Initiate Upgrade' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
