<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    sourceMember: Object,
    eligibleTargets: Array,
    plan: Object,
});

const form = useForm({
    source_member_id: props.sourceMember.id,
    target_member_id: '',
    notes: '',
});

const submit = () => {
    form.post('/transfers');
};

const formatAmount = (paise) => `Rs. ${Number((paise || 0) / 100).toFixed(2)}`;

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const calculateRemainingDays = () => {
    if (!props.sourceMember.expiry_date) return 0;
    const exp = new Date(props.sourceMember.expiry_date);
    const today = new Date();
    return Math.ceil((exp - today) / 86400000);
};
</script>

<template>
    <AppLayout>
        <Head title="Transfer Plan" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-semibold">Transfer Plan</h1>
                    <p class="mt-1 text-slate-300">Transfer membership plan from {{ sourceMember.name }} to another member</p>
                </div>
                <Link href="/members" class="flex items-center gap-2 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <span><-</span> Back to Members
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Source Member Info -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-lg font-semibold">Source Member</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Name</span>
                            <span class="text-sm font-medium">{{ sourceMember.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Member Code</span>
                            <span class="text-sm font-mono">{{ sourceMember.member_code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Current Plan</span>
                            <span class="text-sm font-medium">{{ plan.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Start Date</span>
                            <span class="text-sm">{{ formatDate(sourceMember.start_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Expiry Date</span>
                            <span class="text-sm">{{ formatDate(sourceMember.expiry_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Remaining Days</span>
                            <span class="text-sm font-medium">{{ calculateRemainingDays() }} days</span>
                        </div>
                        <div v-if="plan.has_transfer_fee" class="flex justify-between">
                            <span class="text-sm text-slate-400">Transfer Fee</span>
                            <span class="text-sm font-medium text-orange-400">{{ formatAmount(plan.transfer_fee_amount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Transfer Form -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-lg font-semibold">Transfer Details</h2>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Target Member <span class="text-red-400">*</span></label>
                            <select v-model="form.target_member_id" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                                <option value="">Select a member</option>
                                <option v-for="member in eligibleTargets" :key="member.id" :value="member.id">
                                    {{ member.name }} ({{ member.member_code }})
                                </option>
                            </select>
                            <p v-if="form.errors.target_member_id" class="mt-1 text-xs font-semibold text-red-400">{{ form.errors.target_member_id }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Notes</label>
                            <textarea v-model="form.notes" rows="3" placeholder="Optional notes for this transfer..." class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" maxlength="500"></textarea>
                            <p v-if="form.errors.notes" class="mt-1 text-xs font-semibold text-red-400">{{ form.errors.notes }}</p>
                        </div>

                        <div v-if="plan.has_transfer_fee" class="rounded-lg border border-orange-400/20 bg-orange-500/10 p-4">
                            <p class="text-sm text-orange-300">
                                <strong>Transfer Fee Required:</strong> {{ formatAmount(plan.transfer_fee_amount) }}
                                <span v-if="plan.transfer_fee_gst_applicable"> + GST</span>
                            </p>
                            <p class="mt-1 text-xs text-orange-300/70">Payment will be generated upon transfer initiation.</p>
                        </div>

                        <button type="submit" class="w-full rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                            {{ form.processing ? 'Processing...' : 'Initiate Transfer' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
