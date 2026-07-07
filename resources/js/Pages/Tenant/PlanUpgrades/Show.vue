<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    upgrade: Object,
});

const cancelForm = useForm({
    reason: '',
});

const initiateCancel = () => {
    document.getElementById('cancel-modal').classList.remove('hidden');
};

const submitCancel = () => {
    cancelForm.post(`/upgrades/${props.upgrade.id}/cancel`, {
        onSuccess: () => {
            document.getElementById('cancel-modal').classList.add('hidden');
        },
    });
};

const formatAmount = (paise) => `Rs. ${Number((paise || 0) / 100).toFixed(2)}`;

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const statusClass = computed(() => {
    const styles = {
        pending_payment: 'bg-yellow-100 text-yellow-800',
        completed: 'bg-emerald-100 text-emerald-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return styles[props.upgrade?.status] || 'bg-slate-100 text-slate-600';
});
</script>

<template>
    <AppLayout>
        <Head title="Upgrade Details" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-semibold">Upgrade Details</h1>
                    <p class="mt-1 text-slate-300">View plan upgrade information</p>
                </div>
                <Link href="/upgrades" class="flex items-center gap-2 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <span><-</span> Back to Upgrades
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Upgrade Info -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Upgrade Information</h2>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase" :class="statusClass">
                            {{ upgrade.status_label }}
                        </span>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Upgrade Date</span>
                            <span class="text-sm">{{ formatDate(upgrade.upgrade_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Member</span>
                            <span class="text-sm font-medium">{{ upgrade.member.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Old Plan</span>
                            <span class="text-sm">{{ upgrade.old_plan?.name || '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">New Plan</span>
                            <span class="text-sm font-medium">{{ upgrade.new_plan.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Old Plan Price</span>
                            <span class="text-sm">{{ formatAmount(upgrade.old_plan_price_paise) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">New Plan Price</span>
                            <span class="text-sm">{{ formatAmount(upgrade.new_plan_price_paise) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Upgrade Amount</span>
                            <span class="text-sm font-bold text-orange-400">{{ formatAmount(upgrade.upgrade_amount_paise) }}</span>
                        </div>
                        <div v-if="upgrade.notes" class="flex justify-between">
                            <span class="text-sm text-slate-400">Notes</span>
                            <span class="text-sm">{{ upgrade.notes }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Info -->
                <div v-if="upgrade.payment" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-lg font-semibold">Payment Information</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Receipt Number</span>
                            <Link :href="`/payments/${upgrade.payment.id}/receipt`" class="text-sm font-mono text-orange-400 hover:underline">
                                {{ upgrade.payment.receipt_number }}
                            </Link>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Amount</span>
                            <span class="text-sm">{{ formatAmount(upgrade.payment.total_paise) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Status</span>
                            <span class="text-sm">{{ upgrade.payment.status }}</span>
                        </div>
                        <div v-if="upgrade.payment.due_paise > 0" class="flex justify-between">
                            <span class="text-sm text-slate-400">Due Amount</span>
                            <span class="text-sm font-medium text-orange-400">{{ formatAmount(upgrade.payment.due_paise) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-lg font-semibold">Actions</h2>
                    <div class="space-y-3">
                        <button
                            v-if="upgrade.status === 'pending_payment' && upgrade.payment?.due_paise > 0"
                            @click="completeUpgrade"
                            class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500"
                        >
                            Complete Upgrade (Payment Received)
                        </button>
                        <button
                            v-if="upgrade.status === 'pending_payment'"
                            @click="initiateCancel"
                            class="w-full rounded-lg border border-red-400/20 bg-red-500/10 px-4 py-2.5 text-sm font-semibold text-red-400 hover:bg-red-500/20"
                        >
                            Cancel Upgrade
                        </button>
                        <Link
                            v-if="upgrade.invoice"
                            :href="`/invoices/${upgrade.invoice.id}`"
                            class="block w-full rounded-lg border border-white/10 px-4 py-2.5 text-center text-sm font-semibold text-slate-300 hover:bg-white/5"
                        >
                            View Invoice
                        </Link>
                        <Link
                            :href="`/members/${upgrade.member.id}`"
                            class="block w-full rounded-lg border border-white/10 px-4 py-2.5 text-center text-sm font-semibold text-slate-300 hover:bg-white/5"
                        >
                            View Member Profile
                        </Link>
                    </div>
                </div>

                <!-- Plan Comparison -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-lg font-semibold">Plan Comparison</h2>
                    <div class="space-y-3">
                        <div class="rounded-lg border border-white/10 bg-slate-950/50 p-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Old Plan</p>
                            <p class="text-sm font-medium">{{ upgrade.old_plan?.name || '-' }}</p>
                            <p class="text-xs text-slate-400">{{ upgrade.old_plan?.duration_label || '-' }}</p>
                            <p class="mt-1 text-sm">{{ formatAmount(upgrade.old_plan_price_paise) }}</p>
                        </div>
                        <div class="rounded-lg border border-white/10 bg-slate-950/50 p-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">New Plan</p>
                            <p class="text-sm font-medium">{{ upgrade.new_plan.name }}</p>
                            <p class="text-xs text-slate-400">{{ upgrade.new_plan.duration_label }}</p>
                            <p class="mt-1 text-sm">{{ formatAmount(upgrade.new_plan_price_paise) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div id="cancel-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-2xl border border-white/10 bg-slate-900 p-6">
                <h3 class="mb-4 text-lg font-semibold">Cancel Upgrade</h3>
                <p class="mb-4 text-sm text-slate-400">Please provide a reason for cancelling this upgrade.</p>
                <form @submit.prevent="submitCancel">
                    <textarea
                        v-model="cancelForm.reason"
                        rows="3"
                        placeholder="Reason for cancellation..."
                        class="mb-4 w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400"
                        required
                        maxlength="500"
                    ></textarea>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="document.getElementById('cancel-modal').classList.add('hidden')"
                            class="flex-1 rounded-lg border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500"
                            :disabled="cancelForm.processing"
                        >
                            {{ cancelForm.processing ? 'Cancelling...' : 'Confirm Cancel' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
