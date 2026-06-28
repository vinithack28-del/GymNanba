<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    member: Object,
    forecast: Object,
    canAdd: Boolean,
});

const form = useForm({
    member_id: props.member?.id || '',
    target_weight_kg: '',
    current_weight_kg: '',
    weekly_weight_loss_pct: 0.5,
    target_body_fat_pct: '',
    weeks_to_goal: 12,
});

const submit = () => {
    form.get('/tenant/assess/goal-forecasting');
};
</script>

<template>
    <AppLayout>
        <Head title="Goal Forecasting" />
        
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Assessments</p>
                <h1 class="mt-2 text-3xl font-semibold">Goal Forecasting</h1>
                <p class="mt-1 text-slate-300">Estimate goal duration and plan your workout & nutrition strategy week by week.</p>
            </div>

            <div v-if="!member" class="rounded-2xl border border-white/10 bg-white/5 p-6 text-center text-sm text-slate-400">
                Select a client above to begin goal forecasting.
            </div>

            <div v-else class="flex flex-col gap-6">
                <form @submit.prevent="submit" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <input type="hidden" v-model="form.member_id">
                    <h3 class="text-lg font-bold mb-4">Goal Parameters</h3>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Target Weight (kg)</label>
                            <input v-model="form.target_weight_kg" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Current Weight (kg)</label>
                            <input v-model="form.current_weight_kg" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Weekly Weight Loss %</label>
                            <input v-model="form.weekly_weight_loss_pct" type="number" step="0.1" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Target Body Fat %</label>
                            <input v-model="form.target_body_fat_pct" type="number" step="0.1" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Weeks to Goal</label>
                            <input v-model="form.weeks_to_goal" type="number" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                    </div>
                    <button type="submit" class="mt-4 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                        Generate Forecast
                    </button>
                </form>

                <div v-if="forecast" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h3 class="text-lg font-bold mb-4">Forecast Results</h3>
                    <div class="grid gap-4 grid-cols-4">
                        <div class="rounded-xl border border-white/10 bg-slate-950/50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Target Weight</p>
                            <p class="mt-2 text-2xl font-bold">{{ forecast.target_weight }}</p>
                            <p class="mt-1 text-xs text-slate-400">kg</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-slate-950/50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Estimated Weeks</p>
                            <p class="mt-2 text-2xl font-bold">{{ forecast.weeks }}</p>
                            <p class="mt-1 text-xs text-slate-400">to reach goal</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-slate-950/50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Weekly Loss</p>
                            <p class="mt-2 text-2xl font-bold">{{ forecast.weekly_loss }}</p>
                            <p class="mt-1 text-xs text-slate-400">kg/week</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-slate-950/50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Target Date</p>
                            <p class="mt-2 text-2xl font-bold">{{ forecast.target_date }}</p>
                            <p class="mt-1 text-xs text-slate-400">estimated</p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-xl border border-orange-400/30 bg-orange-500/5 p-4">
                        <h4 class="text-sm font-bold text-orange-400 mb-2">AI Insight</h4>
                        <p class="text-sm text-slate-300">{{ forecast.insight || 'No insight generated yet.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>