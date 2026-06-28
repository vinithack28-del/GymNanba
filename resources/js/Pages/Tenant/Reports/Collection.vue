<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    kpis: Object,
    chartData: Object,
    overdue: Object,
});

const formatCurrency = (paise) => {
    if (!paise) return '₹0';
    return '₹' + (paise / 100).toFixed(0);
};
</script>

<template>
    <AppLayout>
        <Head title="Collection Report" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Collection Report</h1>
                    <p class="mt-0.5 text-sm text-slate-400">Track dues, collections, and payment recovery.</p>
                </div>
                <Link href="/tenant/reports" class="text-sm text-slate-400">← Reports</Link>
            </div>

            <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Total Dues</p>
                    <p class="text-2xl font-bold text-red-400">{{ formatCurrency(kpis?.totalDues) }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Collected</p>
                    <p class="text-2xl font-bold text-emerald-400">{{ formatCurrency(kpis?.collected) }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Pending</p>
                    <p class="text-2xl font-bold text-orange-400">{{ formatCurrency(kpis?.pending) }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Collection Rate</p>
                    <p class="text-2xl font-bold">{{ kpis?.collectionRate || 0 }}%</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <h3 class="mb-3 text-sm font-semibold">Collection Trend</h3>
                    <div class="h-36 flex items-center justify-center text-sm text-slate-400">Chart placeholder</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <h3 class="mb-3 text-sm font-semibold">By Branch</h3>
                    <div class="h-36 flex items-center justify-center text-sm text-slate-400">Chart placeholder</div>
                </div>
            </div>

            <div v-if="overdue && overdue.length > 0" class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div class="border-b border-white/10 px-4 py-3">
                    <h3 class="text-sm font-semibold">Overdue Payments</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-2">Member</th>
                                <th class="px-4 py-2">Plan</th>
                                <th class="px-4 py-2">Due Date</th>
                                <th class="px-4 py-2">Days Overdue</th>
                                <th class="px-4 py-2 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="item in overdue" :key="item.id" class="hover:bg-white/5">
                                <td class="px-4 py-3 font-semibold">{{ item.member_name }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ item.plan_name }}</td>
                                <td class="px-4 py-3">{{ item.due_date }}</td>
                                <td class="px-4 py-3 text-red-400">{{ item.days_overdue }} days</td>
                                <td class="px-4 py-3 text-right font-bold">{{ formatCurrency(item.amount_paise) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>