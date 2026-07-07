<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    transfer: Object,
});

const cancelForm = useForm({
    reason: '',
});

const initiateCancel = () => {
    document.getElementById('cancel-modal').classList.remove('hidden');
};

const submitCancel = () => {
    cancelForm.post(`/transfers/${props.transfer.id}/cancel`, {
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
    return styles[props.transfer?.status] || 'bg-slate-100 text-slate-600';
});
</script>

<template>
    <AppLayout>
        <Head title="Transfer Details" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-semibold">Transfer Details</h1>
                    <p class="mt-1 text-slate-300">View plan transfer information</p>
                </div>
                <Link href="/transfers" class="flex items-center gap-2 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <span><-</span> Back to Transfers
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Transfer Info -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Transfer Information</h2>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase" :class="statusClass">
                            {{ transfer.status_label }}
                        </span>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Transfer Date</span>
                            <span class="text-sm">{{ formatDate(transfer.transfer_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Plan</span>
                            <span class="text-sm font-medium">{{ transfer.membership_plan.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Remaining Days</span>
                            <span class="text-sm">{{ transfer.remaining_days }} days</span>
                        </div>
                        <div v-if="transfer.transfer_fee_amount" class="flex justify-between">
                            <span class="text-sm text-slate-400">Transfer Fee</span>
                            <span class="text-sm font-medium text-orange-400">{{ formatAmount(transfer.transfer_fee_amount) }}</span>
                        </div>
                        <div v-if="transfer.notes" class="flex justify-between">
                            <span class="text-sm text-slate-400">Notes</span>
                            <span class="text-sm">{{ transfer.notes }}</span>
                        </div>
                    </div>
                </div>

                <!-- Members Info -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-lg font-semibold">Members</h2>
                    <div class="space-y-4">
                        <div class="rounded-lg border border-white/10 bg-slate-950/50 p-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">From</p>
                            <p class="text-sm font-medium">{{ transfer.source_member.name }}</p>
                            <p class="text-xs text-slate-400">{{ transfer.source_member.member_code }}</p>
                            <div class="mt-2 text-xs">
                                <span class="text-slate-400">Old Period:</span>
                                <span class="ml-1">{{ formatDate(transfer.old_start_date) }} -> {{ formatDate(transfer.old_expiry_date) }}</span>
                            </div>
                        </div>
                        <div class="rounded-lg border border-white/10 bg-slate-950/50 p-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">To</p>
                            <p class="text-sm font-medium">{{ transfer.target_member.name }}</p>
                            <p class="text-xs text-slate-400">{{ transfer.target_member.member_code }}</p>
                            <div class="mt-2 text-xs">
                                <span class="text-slate-400">New Period:</span>
                                <span class="ml-1">{{ formatDate(transfer.new_start_date) }} -> {{ formatDate(transfer.new_expiry_date) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Info -->
                <div v-if="transfer.payment" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-lg font-semibold">Payment Information</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Receipt Number</span>
                            <Link :href="`/payments/${transfer.payment.id}/receipt`" class="text-sm font-mono text-orange-400 hover:underline">
                                {{ transfer.payment.receipt_number }}
                            </Link>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Amount</span>
                            <span class="text-sm">{{ formatAmount(transfer.payment.total_paise) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-400">Status</span>
                            <span class="text-sm">{{ transfer.payment.status }}</span>
                        </div>
                        <div v-if="transfer.payment.due_paise > 0" class="flex justify-between">
                            <span class="text-sm text-slate-400">Due Amount</span>
                            <span class="text-sm font-medium text-orange-400">{{ formatAmount(transfer.payment.due_paise) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="mb-4 text-lg font-semibold">Actions</h2>
                    <div class="space-y-3">
                        <button
                            v-if="transfer.status === 'pending_payment' && transfer.payment?.due_paise > 0"
                            @click="completeTransfer"
                            class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500"
                        >
                            Complete Transfer (Payment Received)
                        </button>
                        <button
                            v-if="transfer.status === 'pending_payment'"
                            @click="initiateCancel"
                            class="w-full rounded-lg border border-red-400/20 bg-red-500/10 px-4 py-2.5 text-sm font-semibold text-red-400 hover:bg-red-500/20"
                        >
                            Cancel Transfer
                        </button>
                        <Link
                            v-if="transfer.invoice"
                            :href="`/invoices/${transfer.invoice.id}`"
                            class="block w-full rounded-lg border border-white/10 px-4 py-2.5 text-center text-sm font-semibold text-slate-300 hover:bg-white/5"
                        >
                            View Invoice
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div id="cancel-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-2xl border border-white/10 bg-slate-900 p-6">
                <h3 class="mb-4 text-lg font-semibold">Cancel Transfer</h3>
                <p class="mb-4 text-sm text-slate-400">Please provide a reason for cancelling this transfer.</p>
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
