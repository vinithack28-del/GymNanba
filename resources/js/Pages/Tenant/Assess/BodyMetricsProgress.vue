<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    member: Object,
    records: Object,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};
</script>

<template>
    <AppLayout>
        <Head title="Body Metrics Progress" />
        
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Assessments</p>
                <h1 class="mt-2 text-3xl font-semibold">Body Metrics Progress</h1>
                <p class="mt-1 text-slate-300">Weight, BMI, and body fat history for the selected client.</p>
            </div>

            <div v-if="!member" class="rounded-2xl border border-white/10 bg-white/5 p-6 text-center text-sm text-slate-400">
                Select a client to view progress.
            </div>

            <div v-else class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <div class="grid gap-4 md:grid-cols-3">
                    <div v-for="record in records?.slice(0, 3)" :key="record.id" class="rounded-lg border border-white/10 bg-slate-950/50 p-4">
                        <p class="text-xs font-semibold text-slate-400">{{ formatDate(record.assessment_date) }}</p>
                        <p class="mt-1 text-lg font-bold">Weight {{ record.payload?.weight_kg }} kg</p>
                        <p class="text-xs text-slate-400">BMI {{ record.payload?.bmi }} Ã‚- Body fat {{ record.payload?.body_fat_pct || '-' }}</p>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-lg border border-white/10">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Weight</th>
                                    <th class="px-4 py-3">BMI</th>
                                    <th class="px-4 py-3">Body Fat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-white/5">
                                <tr v-for="record in records" :key="record.id" class="hover:bg-white/5">
                                    <td class="px-4 py-3">{{ formatDate(record.assessment_date) }}</td>
                                    <td class="px-4 py-3">{{ record.payload?.weight_kg }}</td>
                                    <td class="px-4 py-3">{{ record.payload?.bmi }}</td>
                                    <td class="px-4 py-3">{{ record.payload?.body_fat_pct || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
