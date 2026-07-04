<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    kpis: Object,
    chartData: Object,
    heatmap: Object,
    heatmapMax: Number,
});
</script>

<template>
    <AppLayout>
        <Head title="Attendance Report" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Attendance Report</h1>
                    <p class="mt-0.5 text-sm text-slate-400">Analyze gym attendance patterns and trends.</p>
                </div>
                <Link href="/tenant/reports" class="text-sm text-slate-400">â† Reports</Link>
            </div>

            <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Total Check-ins</p>
                    <p class="text-2xl font-bold">{{ kpis?.total || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Unique Members</p>
                    <p class="text-2xl font-bold">{{ kpis?.unique || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Walk-ins</p>
                    <p class="text-2xl font-bold">{{ kpis?.walkins || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Avg per Member</p>
                    <p class="text-2xl font-bold">{{ kpis?.avgPerMember || 0 }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">visits</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <h3 class="mb-3 text-sm font-semibold">Attendance Trend</h3>
                    <div class="h-32 flex items-center justify-center text-sm text-slate-400">Chart placeholder</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <h3 class="mb-3 text-sm font-semibold">By Method</h3>
                    <div class="h-44 flex items-center justify-center text-sm text-slate-400">Chart placeholder</div>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <h3 class="mb-4 text-sm font-semibold">Weekly Heatmap</h3>
                <div class="overflow-x-auto">
                    <div class="grid gap-1" style="grid-template-columns: 3rem repeat(24, 1fr); min-width: 680px">
                        <div></div>
                        <div v-for="h in 24" :key="h" class="text-center text-xs text-slate-400">{{ h }}</div>
                        <template v-for="day in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" :key="day">
                            <div class="text-right text-xs text-slate-400 pr-2 self-center">{{ day }}</div>
                            <div v-for="h in 24" :key="`${day}-${h}`" class="h-6 rounded-sm bg-slate-800"></div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
