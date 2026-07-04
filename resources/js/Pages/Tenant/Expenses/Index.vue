<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    expenses: Object,
    summary: Object,
    branches: Object,
    canAdd: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
});

const expenseRows = computed(() => props.expenses?.data || []);
const exportHref = computed(() => {
    const query = typeof window !== 'undefined' ? window.location.search : '';
    return `/tenant/expenses/export${query}`;
});

const formatCurrency = (paise) => {
    if (!paise) return 'â‚¹0';
    return 'â‚¹' + (paise / 100).toFixed(0);
};

const formatDate = (date) => {
    if (!date) return 'â€”';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};
</script>

<template>
    <AppLayout>
        <Head title="Expenses" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Expenses</h1>
                    <p class="mt-0.5 text-sm text-slate-400">Track and manage gym expenses</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="exportHref" class="rounded-lg border border-white/10 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/5">
                        â†“ CSV
                    </Link>
                    <Link v-if="canAdd" href="/tenant/expenses/create" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                        + Add Expense
                    </Link>
                </div>
            </div>

            <div class="rounded-xl border border-white/10 bg-white/5 p-5">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">This Month</p>
                        <p class="mt-0.5 text-2xl font-bold">{{ formatCurrency(summary?.thisTotal) }}</p>
                    </div>
                    <div v-if="summary?.vsLastPct !== null" class="rounded-full px-3 py-1 text-sm font-medium" :class="summary.vsLastPct > 0 ? 'bg-red-500/15 text-red-400' : 'bg-emerald-500/15 text-emerald-400'">
                        {{ summary.vsLastPct > 0 ? 'â†‘' : 'â†“' }} {{ Math.abs(summary.vsLastPct) }}% vs last month
                    </div>
                </div>

                <div v-if="summary?.byCategory?.length" class="flex flex-col gap-2">
                    <div v-for="cat in summary.byCategory" :key="cat.category" class="flex items-center gap-3">
                        <span class="w-28 truncate text-xs text-slate-400">{{ cat.category }}</span>
                        <div class="flex-1 h-2 overflow-hidden rounded-full bg-slate-950/50">
                            <div class="h-2 rounded-full bg-orange-500" :style="{ width: cat.pct + '%' }"></div>
                        </div>
                        <span class="w-10 text-right text-xs font-medium">{{ cat.pct }}%</span>
                        <span class="w-24 text-right text-xs text-slate-400">{{ formatCurrency(cat.total) }}</span>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-400">No data available</p>
            </div>

            <div class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="mb-1 block text-xs text-slate-400">Search</label>
                    <input type="text" placeholder="Search expenses..." class="w-48 rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-400">Category</label>
                    <select class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <option value="">All Categories</option>
                        <option value="rent">Rent</option>
                        <option value="utilities">Utilities</option>
                        <option value="equipment">Equipment</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="salaries">Salaries</option>
                        <option value="marketing">Marketing</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-400">Branch</label>
                    <select class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <option value="">All Branches</option>
                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                    </select>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div v-if="expenseRows.length === 0" class="p-6 text-center text-sm text-slate-400">No expenses found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3">Branch</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="expense in expenseRows" :key="expense.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">{{ formatDate(expense.expense_date) }}</td>
                                <td class="px-4 py-3">{{ expense.category }}</td>
                                <td class="px-4 py-3">{{ expense.description }}</td>
                                <td class="px-4 py-3">{{ expense.branch?.name || 'â€”' }}</td>
                                <td class="px-4 py-3 text-right">{{ formatCurrency(expense.amount_paise) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Link v-if="canEdit" :href="`/tenant/expenses/${expense.id}/edit`" class="text-orange-400 hover:text-orange-300 text-sm">Edit</Link>
                                        <button v-if="canDelete" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
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

