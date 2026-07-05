<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    expenses: {
        type: Object,
        default: () => ({}),
    },
    summary: {
        type: Object,
        default: () => ({}),
    },
    branches: {
        type: [Array, Object],
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    categories: {
        type: Object,
        default: () => ({}),
    },
    methods: {
        type: [Array, Object],
        default: () => [],
    },
    statuses: {
        type: [Array, Object],
        default: () => [],
    },
    canAdd: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
    canApprove: Boolean,
});

const expenseRows = computed(() => props.expenses?.data || []);
const paginationLinks = computed(() => (props.expenses?.links || []).filter((link) => link.url || link.active));
const branchOptions = computed(() => Object.values(props.branches || {}));
const categoryOptions = computed(() => Object.keys(props.categories || {}));
const methodOptions = computed(() => Object.values(props.methods || {}));
const statusOptions = computed(() => Object.values(props.statuses || {}));
const filterPerPage = ref(props.filters?.per_page || props.expenses?.per_page || 25);

const titleCase = (value) => String(value || '-').replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
const formatCurrency = (paise) => `Rs. ${((Number(paise) || 0) / 100).toFixed(2)}`;
const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    const date = String(value).split('T')[0];
    const parts = date.split('-');

    return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : value;
};

const pageUrl = computed(() => {
    const params = new URLSearchParams();

    Object.entries(props.filters || {}).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            params.set(key, value);
        }
    });

    params.set('per_page', filterPerPage.value);
    params.delete('page');

    return `/expenses?${params.toString()}`;
});

const exportHref = computed(() => {
    const query = typeof window !== 'undefined' ? window.location.search : '';
    return `/expenses/export${query}`;
});

const statusClass = (status) => ({
    approved: 'bg-emerald-500/10 text-emerald-400',
    pending: 'bg-amber-500/10 text-amber-400',
    rejected: 'bg-red-500/10 text-red-400',
}[status] || 'bg-slate-500/10 text-slate-400');

const approveExpense = (expense) => {
    router.post(`/expenses/${expense.id}/approve`, {}, { preserveScroll: true });
};

const rejectExpense = (expense) => {
    const reason = window.prompt('Reason for rejection');

    if (!reason) {
        return;
    }

    router.post(`/expenses/${expense.id}/reject`, { rejection_reason: reason }, { preserveScroll: true });
};

const deleteExpense = (expense) => {
    if (!window.confirm(`Delete expense "${expense.description}"?`)) {
        return;
    }

    router.delete(`/expenses/${expense.id}`, { preserveScroll: true });
};
</script>

<template>
    <AppLayout>
        <Head title="Expenses" />

        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold md:text-2xl">Expenses</h1>
                    <p class="app-muted mt-0.5 text-sm">Track and manage gym expenses.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="exportHref" class="app-panel rounded-lg border px-3 py-2 text-xs font-semibold transition hover:opacity-80">CSV</Link>
                    <Link v-if="canAdd" href="/expenses/create" class="rounded-lg bg-orange-500 px-3 py-2 text-xs font-semibold text-slate-950 transition hover:bg-orange-400">Add Expense</Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                <div class="app-panel rounded-xl border px-3 py-2.5">
                    <p class="app-muted text-[11px] font-bold uppercase tracking-[0.12em]">This Month</p>
                    <p class="mt-1 text-lg font-semibold">{{ formatCurrency(summary?.thisTotal) }}</p>
                </div>
                <div class="app-panel rounded-xl border px-3 py-2.5">
                    <p class="app-muted text-[11px] font-bold uppercase tracking-[0.12em]">Last Month</p>
                    <p class="mt-1 text-lg font-semibold">{{ formatCurrency(summary?.lastTotal) }}</p>
                </div>
                <div class="app-panel rounded-xl border px-3 py-2.5">
                    <p class="app-muted text-[11px] font-bold uppercase tracking-[0.12em]">Variance</p>
                    <p class="mt-1 text-lg font-semibold" :class="(summary?.vsLastPct || 0) > 0 ? 'text-red-400' : 'text-emerald-400'">
                        {{ summary?.vsLastPct === null ? '-' : `${summary.vsLastPct}%` }}
                    </p>
                </div>
            </div>

            <div v-if="summary?.byCategory?.length" class="app-panel rounded-xl border p-3">
                <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                    <div v-for="cat in summary.byCategory" :key="cat.category" class="app-panel-strong rounded-lg border px-3 py-2">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold">{{ titleCase(cat.category) }}</p>
                            <p class="app-muted text-xs">{{ cat.pct }}%</p>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-500/20">
                            <div class="h-full rounded-full bg-orange-500" :style="{ width: `${cat.pct}%` }"></div>
                        </div>
                        <p class="app-muted mt-1 text-xs">{{ formatCurrency(cat.total) }}</p>
                    </div>
                </div>
            </div>

            <form method="GET" action="/expenses" class="app-panel flex flex-wrap items-center gap-2 rounded-xl border p-3">
                <input name="search" :value="filters?.search" placeholder="Search expenses..." class="app-panel-strong min-w-[180px] flex-1 rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">

                <select name="category" class="app-panel-strong min-w-[150px] rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                    <option value="">All Categories</option>
                    <option v-for="category in categoryOptions" :key="category" :value="category" :selected="filters?.category === category">{{ titleCase(category) }}</option>
                </select>

                <select name="method" class="app-panel-strong min-w-[120px] rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                    <option value="">All Methods</option>
                    <option v-for="method in methodOptions" :key="method" :value="method" :selected="filters?.method === method">{{ titleCase(method) }}</option>
                </select>

                <select name="status" class="app-panel-strong min-w-[130px] rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                    <option value="">All Statuses</option>
                    <option v-for="status in statusOptions" :key="status" :value="status" :selected="filters?.status === status">{{ titleCase(status) }}</option>
                </select>

                <select name="branch_id" class="app-panel-strong min-w-[150px] rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                    <option value="">All Branches</option>
                    <option v-for="branch in branchOptions" :key="branch.id" :value="branch.id" :selected="Number(filters?.branch_id) === Number(branch.id)">{{ branch.name }}</option>
                </select>

                <input type="date" name="date_from" :value="filters?.date_from" class="app-panel-strong rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">
                <input type="date" name="date_to" :value="filters?.date_to" class="app-panel-strong rounded-lg border px-3 py-2 text-xs outline-none focus:border-orange-400">

                <button type="submit" class="rounded-lg bg-orange-500 px-3 py-2 text-xs font-semibold text-slate-950 transition hover:bg-orange-400">Apply</button>
            </form>

            <div class="app-panel overflow-hidden rounded-xl border">
                <div v-if="expenseRows.length === 0" class="p-6 text-center text-sm app-muted">No expenses found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[980px] text-left text-sm">
                        <thead class="app-table-head text-[11px] font-bold uppercase tracking-[0.08em] app-muted">
                            <tr>
                                <th class="px-3 py-2">Date</th>
                                <th class="px-3 py-2">Category</th>
                                <th class="px-3 py-2">Description</th>
                                <th class="px-3 py-2">Branch</th>
                                <th class="px-3 py-2">Method</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2 text-right">Amount</th>
                                <th class="px-3 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr v-for="expense in expenseRows" :key="expense.id" class="transition hover:bg-white/5">
                                <td class="px-3 py-2 app-muted">
                                    <p>{{ formatDate(expense.date) }}</p>
                                    <p v-if="expense.is_recurring" class="mt-1 inline-flex rounded-full bg-orange-500/10 px-2 py-0.5 text-[10px] font-semibold text-orange-400">Recurring</p>
                                </td>
                                <td class="px-3 py-2">
                                    <p class="font-semibold">{{ titleCase(expense.category) }}</p>
                                    <p class="app-muted text-xs">{{ expense.sub_category ? titleCase(expense.sub_category) : '-' }}</p>
                                </td>
                                <td class="px-3 py-2">
                                    <p class="max-w-[260px] truncate font-semibold">{{ expense.description }}</p>
                                    <p class="app-muted max-w-[260px] truncate text-xs">{{ expense.vendor || expense.reference || '-' }}</p>
                                </td>
                                <td class="px-3 py-2 app-muted">{{ expense.branch?.name || '-' }}</td>
                                <td class="px-3 py-2 app-muted">{{ titleCase(expense.method) }}</td>
                                <td class="px-3 py-2">
                                    <span class="rounded-full px-2 py-1 text-[11px] font-semibold" :class="statusClass(expense.status)">
                                        {{ titleCase(expense.status) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <p class="font-semibold">{{ formatCurrency(expense.amount_paise) }}</p>
                                    <p v-if="Number(expense.gst_paise) > 0" class="app-muted text-xs">+ {{ formatCurrency(expense.gst_paise) }} GST</p>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex justify-end gap-1.5">
                                        <Link v-if="canEdit" :href="`/expenses/${expense.id}/edit`" class="locker-icon-btn locker-icon-btn-edit" title="Edit expense" aria-label="Edit expense">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </Link>
                                        <button v-if="canApprove && expense.status === 'pending'" type="button" class="locker-icon-btn text-emerald-400" title="Approve expense" aria-label="Approve expense" @click="approveExpense(expense)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                                        </button>
                                        <button v-if="canApprove && expense.status === 'pending'" type="button" class="locker-icon-btn text-red-400" title="Reject expense" aria-label="Reject expense" @click="rejectExpense(expense)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                        </button>
                                        <button v-if="canDelete" type="button" class="locker-icon-btn text-red-400" title="Delete expense" aria-label="Delete expense" @click="deleteExpense(expense)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="expenses?.links?.length" class="app-panel flex flex-col items-center justify-between gap-3 rounded-xl border px-4 py-3 sm:flex-row">
                <p class="app-muted text-xs">
                    Showing {{ expenses.from || 0 }} to {{ expenses.to || 0 }} of {{ expenses.total || 0 }} expenses
                </p>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <select v-model="filterPerPage" @change="$inertia.visit(pageUrl, { preserveScroll: true, preserveState: true })" class="app-panel-strong rounded-lg border px-2.5 py-1.5 text-xs outline-none focus:border-orange-400">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                        <option value="100">100 / page</option>
                    </select>
                    <div v-if="paginationLinks.length > 1" class="flex flex-wrap items-center gap-1">
                        <Link
                            v-for="link in paginationLinks"
                            :key="link.label"
                            :href="link.url || '#'"
                            preserve-scroll
                            preserve-state
                            :class="[
                                'rounded-lg px-2.5 py-1.5 text-xs font-semibold transition',
                                link.active ? 'bg-orange-500 text-slate-950' : 'app-panel border hover:opacity-80',
                                !link.url && !link.active ? 'pointer-events-none opacity-40' : ''
                            ]"
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
