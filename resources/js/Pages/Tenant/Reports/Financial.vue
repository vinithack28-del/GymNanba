<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    kpis: Object,
    chartData: Object,
    byPlan: Object,
    byBranch: Object,
    topMembers: Object,
});

const formatCurrency = (paise) => {
    if (!paise) return 'â‚¹0';
    return 'â‚¹' + (paise / 100).toFixed(0);
};
</script>

<template>
    <AppLayout>
        <Head title="Revenue Report" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Revenue Report</h1>
                    <p class="mt-0.5 text-sm text-slate-400">Income, payments, and financial performance.</p>
                </div>
                <Link href="/tenant/reports" class="text-sm text-slate-400">â† Reports</Link>
            </div>

            <div class="grid gap-4 grid-cols-2 lg:grid-cols-5">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Total Revenue</p>
                    <p class="text-xl font-bold">{{ formatCurrency(kpis?.total) }}</p>
                    <p class="mt-0.5 text-xs" :class="(kpis?.vsChange ?? 0) >= 0 ? 'text-emerald-400' : 'text-red-400'">
                        {{ (kpis?.vsChange ?? 0) >= 0 ? '+' : '' }}{{ kpis?.vsChange || 0 }}% vs prev
                    </p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Transactions</p>
                    <p class="text-xl font-bold">{{ kpis?.count || 0 }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">payments</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Average</p>
                    <p class="text-xl font-bold">{{ formatCurrency(kpis?.avg) }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">per txn</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">GST Collected</p>
                    <p class="text-xl font-bold">{{ formatCurrency(kpis?.gst) }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Outstanding</p>
                    <p class="text-xl font-bold text-red-400">{{ formatCurrency(kpis?.pendingDues) }}</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <h3 class="mb-3 text-sm font-semibold">Revenue Trend</h3>
                    <div class="h-32 flex items-center justify-center text-sm text-slate-400">Chart placeholder</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <h3 class="mb-3 text-sm font-semibold">By Method</h3>
                    <div class="h-44 flex items-center justify-center text-sm text-slate-400">Chart placeholder</div>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <h3 class="mb-3 text-sm font-semibold">By Plan</h3>
                    <div class="h-36 flex items-center justify-center text-sm text-slate-400">Chart placeholder</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <h3 class="mb-3 text-sm font-semibold">By Branch</h3>
                    <div class="h-36 flex items-center justify-center text-sm text-slate-400">Chart placeholder</div>
                </div>
            </div>

            <div v-if="topMembers && topMembers.length > 0" class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div class="border-b border-white/10 px-4 py-3">
                    <h3 class="text-sm font-semibold">Top 10 Members</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-2">#</th>
                                <th class="px-4 py-2">Member</th>
                                <th class="px-4 py-2">Plan</th>
                                <th class="px-4 py-2 text-right">Payments</th>
                                <th class="px-4 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="(member, index) in topMembers" :key="member.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">{{ index + 1 }}</td>
                                <td class="px-4 py-3 font-semibold">{{ member.name }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ member.plan_name }}</td>
                                <td class="px-4 py-3 text-right">{{ member.payments_count }}</td>
                                <td class="px-4 py-3 text-right font-bold">{{ formatCurrency(member.total_paise) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
