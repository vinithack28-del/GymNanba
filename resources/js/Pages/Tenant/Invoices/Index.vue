<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    invoices: Object,
    branches: Object,
    canAdd: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
});

const invoiceRows = computed(() => props.invoices?.data || []);

const formatCurrency = (paise) => {
    if (!paise) return 'â‚¹0';
    return 'â‚¹' + (paise / 100).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return 'â€”';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const getStatusColor = (status) => {
    const colors = {
        draft: { bg: 'rgba(136,135,128,0.12)', fg: '#888780' },
        sent: { bg: 'rgba(59,130,246,0.12)', fg: '#3B82F6' },
        paid: { bg: 'rgba(29,158,117,0.12)', fg: '#1D9E75' },
        overdue: { bg: 'rgba(239,68,68,0.12)', fg: '#EF4444' },
        void: { bg: 'rgba(226,75,74,0.10)', fg: '#E24B4A' },
    };
    return colors[status] || colors.draft;
};
</script>

<template>
    <AppLayout>
        <Head title="Invoices" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Invoices</h1>
                    <p class="mt-0.5 text-sm text-slate-400">Manage gym invoices and billing</p>
                </div>
                <Link v-if="canAdd" href="/tenant/invoices/create" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    + Create Invoice
                </Link>
            </div>

            <div class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="mb-1 block text-xs text-slate-400">Search</label>
                    <input type="text" placeholder="Search invoices..." class="w-52 rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-400">Status</label>
                    <select class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                        <option value="void">Void</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-400">Branch</label>
                  <select class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <option value="">All Branches</option>
                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-400">Date From</label>
                    <input type="date" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-400">Date To</label>
                    <input type="date" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                </div>
                <button class="rounded-lg bg-orange-500 px-4 py-1.5 text-sm font-medium text-slate-950 hover:bg-orange-400">Filter</button>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div v-if="invoiceRows.length === 0" class="p-6 text-center text-sm text-slate-400">No invoices found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-2.5">Number</th>
                                <th class="px-4 py-2.5">Date</th>
                                <th class="px-4 py-2.5">Member</th>
                                <th class="px-4 py-2.5">Description</th>
                                <th class="px-4 py-2.5 text-right">Subtotal</th>
                                <th class="px-4 py-2.5 text-right">GST</th>
                                <th class="px-4 py-2.5 text-right">Total</th>
                                <th class="px-4 py-2.5">Status</th>
                                <th class="px-4 py-2.5">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="invoice in invoiceRows" :key="invoice.id" class="hover:bg-white/5">
                                <td class="px-4 py-3 font-mono font-bold text-orange-400">{{ invoice.invoice_number }}</td>
                                <td class="px-4 py-3">{{ formatDate(invoice.invoice_date) }}</td>
                                <td class="px-4 py-3">{{ invoice.member?.name || 'â€”' }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ invoice.description || 'â€”' }}</td>
                                <td class="px-4 py-3 text-right">{{ formatCurrency(invoice.subtotal_paise) }}</td>
                                <td class="px-4 py-3 text-right">{{ formatCurrency(invoice.gst_paise) }}</td>
                                <td class="px-4 py-3 text-right font-bold">{{ formatCurrency(invoice.total_paise) }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-bold" :style="{ background: getStatusColor(invoice.status).bg, color: getStatusColor(invoice.status).fg }">
                                        {{ invoice.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Link :href="`/tenant/invoices/${invoice.id}`" class="text-orange-400 hover:text-orange-300 text-sm">View</Link>
                                        <Link v-if="canEdit && invoice.status !== 'void'" :href="`/tenant/invoices/${invoice.id}/edit`" class="text-slate-400 hover:text-slate-300 text-sm">Edit</Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

