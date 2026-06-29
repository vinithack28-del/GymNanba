<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    member: Object,
    records: Object,
    editingRecord: Object,
    selectedMemberId: String,
    canAdd: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
});

const recordRows = computed(() => props.records?.data || []);

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const form = useForm({
    member_id: props.member?.id || '',
    measurement_date: new Date().toISOString().split('T')[0],
    weight_kg: '',
    height_cm: '',
    waist_cm: '',
    hip_cm: '',
    neck_cm: '',
    body_fat_pct: '',
    next_measurement_date: '',
    notes: '',
});

const submit = () => {
    if (props.editingRecord) {
        form.put(`/assess/body-metrics/${props.editingRecord.id}`);
    } else {
        form.post('/assess/body-metrics');
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Body Metrics" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Assessments</p>
                    <h1 class="mt-2 text-3xl font-semibold">Body Metrics</h1>
                    <p class="mt-1 text-slate-300">Track measurements, BMI, and next measurement dates.</p>
                </div>
                <Link :href="`/assess/body-metrics/progress?member_id=${selectedMemberId}`" class="rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">
                    Progress Tracking
                </Link>
            </div>

            <div v-if="member && (canAdd || (editingRecord && canEdit))" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-lg font-bold mb-4">{{ editingRecord ? 'Edit Metrics' : 'Add Body Metrics' }}</h3>
                <form @submit.prevent="submit" class="flex flex-col gap-4">
                    <input type="hidden" v-model="form.member_id">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Measurement Date <span class="text-red-400">*</span></label>
                            <input v-model="form.measurement_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Weight (kg) <span class="text-red-400">*</span></label>
                            <input v-model="form.weight_kg" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Height (cm) <span class="text-red-400">*</span></label>
                            <input v-model="form.height_cm" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Waist (cm)</label>
                            <input v-model="form.waist_cm" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Hip (cm)</label>
                            <input v-model="form.hip_cm" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Neck (cm)</label>
                            <input v-model="form.neck_cm" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Body Fat %</label>
                            <input v-model="form.body_fat_pct" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Next Measurement Date</label>
                            <input v-model="form.next_measurement_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-2 block text-sm font-medium">Notes</label>
                            <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                        {{ editingRecord ? 'Save Metrics' : 'Add Body Metrics' }}
                    </button>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Client</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Weight</th>
                                <th class="px-4 py-3">Height</th>
                                <th class="px-4 py-3">BMI</th>
                                <th class="px-4 py-3">Body Fat</th>
                                <th class="px-4 py-3">Next</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="record in recordRows" :key="record.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">{{ record.member?.name }}</td>
                                <td class="px-4 py-3">{{ formatDate(record.assessment_date) }}</td>
                                <td class="px-4 py-3">{{ record.payload?.weight_kg }}</td>
                                <td class="px-4 py-3">{{ record.payload?.height_cm }}</td>
                                <td class="px-4 py-3">{{ record.payload?.bmi }}</td>
                                <td class="px-4 py-3">{{ record.payload?.body_fat_pct || '—' }}</td>
                                <td class="px-4 py-3">{{ formatDate(record.next_assessment_date) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Link v-if="canEdit" :href="`/assess/body-metrics?member_id=${record.member_id}&edit=${record.id}`" class="text-orange-400 hover:text-orange-300 text-sm">Edit</Link>
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
