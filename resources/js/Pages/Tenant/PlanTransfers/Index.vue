<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    transfers: Object,
});

const formatAmount = (paise) => `Rs. ${Number((paise || 0) / 100).toFixed(2)}`;

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const statusClass = (status) => {
    const styles = {
        pending_payment: 'bg-yellow-100 text-yellow-800',
        completed: 'bg-emerald-100 text-emerald-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return styles[status] || 'bg-slate-100 text-slate-600';
};

const statusLabel = (status) => {
    const labels = {
        pending_payment: 'Pending Payment',
        completed: 'Completed',
        cancelled: 'Cancelled',
    };
    return labels[status] || status;
};
</script>

<template>
    <AppLayout>
        <Head title="Plan Transfers" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-semibold">Plan Transfers</h1>
                    <p class="mt-1 text-slate-300">View and manage plan transfer history</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h3 class="text-[15px] font-semibold text-slate-900">Transfer History</h3>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500">
                        {{ transfers.total }} {{ transfers.total === 1 ? 'record' : 'records' }}
                    </span>
                </div>

                <div v-if="transfers.data.length === 0" class="px-6 py-14 text-center text-sm text-slate-500">
                    No transfer records found.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Date</th>
                                <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">From</th>
                                <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">To</th>
                                <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Plan</th>
                                <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Fee</th>
                                <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Status</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="transfer in transfers.data" :key="transfer.id" class="border-t border-slate-100">
                                <td class="px-6 py-3">
                                    <div class="text-sm text-slate-900">{{ formatDate(transfer.transfer_date) }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-slate-900">{{ transfer.source_member?.name || '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ transfer.source_member?.member_code || '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-slate-900">{{ transfer.target_member?.name || '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ transfer.target_member?.member_code || '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    {{ transfer.membership_plan?.name || '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    {{ transfer.transfer_fee_amount ? formatAmount(transfer.transfer_fee_amount) : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="statusClass(transfer.status)">
                                        {{ statusLabel(transfer.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="`/transfers/${transfer.id}`" class="inline-flex rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                                        View Details
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="transfers.links && transfers.links.length > 3" class="flex items-center justify-between border-t border-slate-200 px-6 py-4">
                    <div class="text-sm text-slate-500">
                        Showing {{ transfers.from }} to {{ transfers.to }} of {{ transfers.total }} results
                    </div>
                    <div class="flex gap-2">
                        <template v-for="(link, index) in transfers.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="rounded-lg border px-3 py-1.5 text-xs font-medium transition"
                                :class="link.active ? 'border-orange-400 bg-orange-50 text-orange-700' : 'border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-400"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
