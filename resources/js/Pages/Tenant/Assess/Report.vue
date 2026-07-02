<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    member: Object,
    summary: Object,
    records: Object,
});

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const printReport = () => {
    window.print();
};
</script>

<template>
    <AppLayout>
        <Head title="Assessment Report" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Assessments</p>
                    <h1 class="mt-2 text-3xl font-semibold">Assessment Report</h1>
                    <p class="mt-1 text-slate-300">Latest assessment summary for the selected client.</p>
                </div>
                <button @click="printReport" :disabled="!summary || summary.sections_completed === 0" class="rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/50" :class="!summary || summary.sections_completed === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                    Download / Print
                </button>
            </div>

            <div v-if="!member" class="rounded-2xl border border-white/10 bg-white/5 p-6 text-center text-sm text-slate-400">
                Select a client to load the assessment report.
            </div>

            <div v-else class="flex flex-col gap-6">
                <div class="grid gap-4 grid-cols-4">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Overall Score</p>
                        <p class="mt-1 text-2xl font-bold">{{ summary?.overall_score || 0 }}%</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Sections Completed</p>
                        <p class="mt-1 text-2xl font-bold">{{ summary?.sections_completed || 0 }} / 9</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Last Updated</p>
                        <p class="mt-1 text-base font-bold">{{ formatDate(summary?.last_updated) }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Risk Flags</p>
                        <p class="mt-1 text-base font-bold">{{ summary?.risk_flags?.length ? summary.risk_flags.join(', ') : 'None' }}</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div v-for="(record, label) in records" :key="label" class="rounded-lg border border-white/10 bg-slate-950/50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">{{ label }}</p>
                        <div v-if="record">
                            <p class="text-xs text-slate-400">Date</p>
                            <p class="font-medium">{{ formatDate(record.assessment_date) }}</p>
                            <p v-if="record.status" class="mt-2 text-xs text-slate-400">Status: {{ record.status?.replace('_', ' ')?.replace(/\b\w/g, l => l.toUpperCase()) }}</p>
                            <p v-if="record.type === 'body_metrics'" class="mt-2 text-xs text-slate-400">BMI {{ record.payload?.bmi || '—' }} · {{ record.payload?.bmi_category || '—' }}</p>
                            <p v-else-if="record.type === 'vitals'" class="mt-2 text-xs text-slate-400">HR {{ record.payload?.hr_bpm || '—' }} bpm · BP {{ record.payload?.bp_systolic || '—' }}/{{ record.payload?.bp_diastolic || '—' }}</p>
                            <p v-else-if="record.type?.startsWith('fitness_')" class="mt-2 text-xs text-slate-400">{{ record.title }}</p>
                        </div>
                        <div v-else class="text-xs text-slate-400">
                            No data available
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>