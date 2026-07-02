<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    member: Object,
    records: Object,
    editingRecord: Object,
    tab: String,
    canAdd: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
});

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const form = useForm({
    member_id: props.member?.id || '',
    tab: props.tab || 'cardio',
    measurement_date: new Date().toISOString().split('T')[0],
    next_measurement_date: '',
    test_type: 'cooper_12_min',
    test_value: '',
    hrr: '',
    test_name: '',
    unit: 'kg',
    reps: '',
    interpretation: '',
    distance_cm: '',
    notes: '',
});

const submit = () => {
    if (props.editingRecord) {
        form.put(`/tenant/assess/fitness/${props.editingRecord.id}`);
    } else {
        form.post('/tenant/assess/fitness');
    }
};

const tabs = [
    { key: 'cardio', label: 'Cardiorespiratory' },
    { key: 'strength', label: 'Muscular Strength' },
    { key: 'endurance', label: 'Muscular Endurance' },
    { key: 'flexibility', label: 'Flexibility' },
];
</script>

<template>
    <AppLayout>
        <Head title="Fitness" />
        
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Assessments</p>
                <h1 class="mt-2 text-3xl font-semibold">Fitness</h1>
                <p class="mt-1 text-slate-300">Cardiorespiratory, strength, endurance, and flexibility history.</p>
            </div>

            <div class="flex flex-wrap gap-2 border-b border-white/10 pb-4">
                <Link v-for="t in tabs" :key="t.key" :href="`/tenant/assess/fitness?tab=${t.key}`" :class="['rounded-lg px-4 py-2 text-sm font-semibold transition', tab === t.key ? 'bg-orange-500 text-slate-950' : 'text-slate-400 hover:bg-white/5']">
                    {{ t.label }}
                </Link>
            </div>

            <div v-if="member && (canAdd || (editingRecord && canEdit))" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-lg font-bold mb-4">{{ editingRecord ? 'Save Test' : 'New Test' }}</h3>
                <form @submit.prevent="submit" class="flex flex-col gap-4">
                    <input type="hidden" v-model="form.member_id">
                    <input type="hidden" v-model="form.tab">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Measurement Date <span class="text-red-400">*</span></label>
                            <input v-model="form.measurement_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Next Measurement Date</label>
                            <input v-model="form.next_measurement_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        
                        <template v-if="tab === 'cardio'">
                            <div>
                                <label class="mb-2 block text-sm font-medium">Test Type</label>
                                <select v-model="form.test_type" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                    <option value="cooper_12_min">12 min walk/run</option>
                                    <option value="run_1_5_mile">1.5 mile run</option>
                                    <option value="walk_1_mile">1 mile walk</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium">Test Value <span class="text-red-400">*</span></label>
                                <input v-model="form.test_value" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium">HRR</label>
                                <input v-model="form.hrr" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                            </div>
                        </template>

                        <template v-else-if="tab === 'strength'">
                            <div>
                                <label class="mb-2 block text-sm font-medium">Test Name <span class="text-red-400">*</span></label>
                                <input v-model="form.test_name" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium">Test Value <span class="text-red-400">*</span></label>
                                <input v-model="form.test_value" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium">Unit</label>
                                <select v-model="form.unit" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                    <option value="kg">kg</option>
                                    <option value="N">N</option>
                                    <option value="lbs">lbs</option>
                                </select>
                            </div>
                        </template>

                        <template v-else-if="tab === 'endurance'">
                            <div class="md:col-span-1">
                                <label class="mb-2 block text-sm font-medium">Test Name <span class="text-red-400">*</span></label>
                                <input v-model="form.test_name" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            </div>
                            <div class="md:col-span-1">
                                <label class="mb-2 block text-sm font-medium">Reps <span class="text-red-400">*</span></label>
                                <input v-model="form.reps" type="number" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            </div>
                        </template>

                        <template v-else-if="tab === 'flexibility'">
                            <div class="md:col-span-1">
                                <label class="mb-2 block text-sm font-medium">Test Name <span class="text-red-400">*</span></label>
                                <input v-model="form.test_name" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            </div>
                            <div class="md:col-span-1">
                                <label class="mb-2 block text-sm font-medium">Distance / Value (cm) <span class="text-red-400">*</span></label>
                                <input v-model="form.distance_cm" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            </div>
                        </template>

                        <div v-if="tab === 'endurance' || tab === 'flexibility'" class="md:col-span-3">
                            <label class="mb-2 block text-sm font-medium">Interpretation</label>
                            <input v-model="form.interpretation" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>

                        <div class="md:col-span-3">
                            <label class="mb-2 block text-sm font-medium">Notes</label>
                            <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                        {{ editingRecord ? 'Save Test' : 'New Test' }}
                    </button>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div v-if="!member" class="p-6 text-center text-sm text-slate-400">
                    Select a client to view {{ tab }} tests.
                </div>
                <div v-else-if="!records || records.length === 0" class="p-6 text-center text-sm text-slate-400">
                    No tests recorded yet.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Test</th>
                                <th class="px-4 py-3">Value</th>
                                <th class="px-4 py-3">Next</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="record in records" :key="record.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">{{ formatDate(record.assessment_date) }}</td>
                                <td class="px-4 py-3">{{ record.title }}</td>
                                <td class="px-4 py-3">{{ record.payload?.vo2max || record.payload?.test_value || record.payload?.reps || record.payload?.distance_cm || '—' }}</td>
                                <td class="px-4 py-3">{{ formatDate(record.next_assessment_date) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Link v-if="canEdit" :href="`/tenant/assess/fitness?member_id=${member.id}&tab=${tab}&edit=${record.id}`" class="text-orange-400 hover:text-orange-300 text-sm">Edit</Link>
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