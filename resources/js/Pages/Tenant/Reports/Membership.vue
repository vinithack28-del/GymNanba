<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    kpis: Object,
    chartData: Object,
    byBranch: Object,
    branches: Object,
});
</script>

<template>
    <AppLayout>
        <Head title="Membership Report" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Membership Report</h1>
                    <p class="mt-0.5 text-sm text-slate-400">Membership growth and demographics.</p>
                </div>
                <Link href="/tenant/reports" class="text-sm text-slate-400">â† Reports</Link>
            </div>

            <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">New Members</p>
                    <p class="text-2xl font-bold">{{ kpis?.new || 0 }}</p>
                    <p class="mt-0.5 text-xs" :class="(kpis?.vsNew ?? 0) >= 0 ? 'text-emerald-400' : 'text-red-400'">
                        {{ (kpis?.vsNew ?? 0) >= 0 ? '+' : '' }}{{ kpis?.vsNew || 0 }}% vs prev
                    </p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Churned</p>
                    <p class="text-2xl font-bold text-red-400">{{ kpis?.churned || 0 }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ kpis?.churnRate || 0 }}% churn rate</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Retention</p>
                    <p class="text-2xl font-bold text-emerald-400">{{ kpis?.retentionRate || 0 }}%</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Net Growth</p>
                    <p class="text-2xl font-bold" :class="(kpis?.netGrowth ?? 0) >= 0 ? 'text-emerald-400' : 'text-red-400'">
                        {{ (kpis?.netGrowth ?? 0) >= 0 ? '+' : '' }}{{ kpis?.netGrowth || 0 }}
                    </p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <h3 class="mb-3 text-sm font-semibold">Membership Trend</h3>
                    <div class="h-32 flex items-center justify-center text-sm text-slate-400">Chart placeholder</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <h3 class="mb-3 text-sm font-semibold">Gender Distribution</h3>
                    <div class="h-44 flex items-center justify-center text-sm text-slate-400">Chart placeholder</div>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <h3 class="mb-3 text-sm font-semibold">By Plan</h3>
                    <div class="h-36 flex items-center justify-center text-sm text-slate-400">Chart placeholder</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <h3 class="mb-3 text-sm font-semibold">Age Groups</h3>
                    <div class="h-36 flex items-center justify-center text-sm text-slate-400">Chart placeholder</div>
                </div>
            </div>

            <div v-if="byBranch && byBranch.length > 0 && branches && branches.length > 1" class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div class="border-b border-white/10 px-4 py-3">
                    <h3 class="text-sm font-semibold">By Branch</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-2">Branch</th>
                                <th class="px-4 py-2 text-right">Count</th>
                                <th class="px-4 py-2 text-right">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="item in byBranch" :key="item.branch_id" class="hover:bg-white/5">
                                <td class="px-4 py-3">{{ item.branch_name }}</td>
                                <td class="px-4 py-3 text-right">{{ item.count }}</td>
                                <td class="px-4 py-3 text-right">{{ item.percentage }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
