<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    member: Object,
    records: Object,
    editingRecord: Object,
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
    measurement_date: new Date().toISOString().split('T')[0],
    hr_bpm: '',
    next_check_date: '',
    bp_systolic: '',
    bp_diastolic: '',
    notes: '',
});

const submit = () => {
    if (props.editingRecord) {
        form.put(`/tenant/assess/vitals/${props.editingRecord.id}`);
    } else {
        form.post('/tenant/assess/vitals');
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Vitals" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Assessments</p>
                    <h1 class="mt-2 text-3xl font-semibold">Vitals</h1>
                    <p class="mt-1 text-slate-300">Resting heart rate and blood pressure history.</p>
                </div>
                <button class="rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">
                    Refresh
                </button>
            </div>

            <div v-if="member && (canAdd || (editingRecord && canEdit))" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-lg font-bold mb-4">{{ editingRecord ? 'Edit Record' : 'New Record' }}</h3>
                <form @submit.prevent="submit" class="flex flex-col gap-4">
                    <input type="hidden" v-model="form.member_id">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Measurement Date <span class="text-red-400">*</span></label>
                            <input v-model="form.measurement_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">HR (bpm) <span class="text-red-400">*</span></label>
                            <input v-model="form.hr_bpm" type="number" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Next Check Date</label>
                            <input v-model="form.next_check_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div class="md:col-span-1">
                            <label class="mb-2 block text-sm font-medium">BP Systolic <span class="text-red-400">*</span></label>
                            <input v-model="form.bp_systolic" type="number" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div class="md:col-span-1">
                            <label class="mb-2 block text-sm font-medium">BP Diastolic <span class="text-red-400">*</span></label>
                            <input v-model="form.bp_diastolic" type="number" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-2 block text-sm font-medium">Notes</label>
                            <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                        {{ editingRecord ? 'Save Record' : 'New Record' }}
                    </button>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div v-if="!member" class="p-6 text-center text-sm text-slate-400">
                    Select a client to view vitals history.
                </div>
                <div v-else-if="!records || records.length === 0" class="p-6 text-center text-sm text-slate-400">
                    No vitals records yet.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">HR</th>
                                <th class="px-4 py-3">BP</th>
                                <th class="px-4 py-3">Next Check</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="record in records" :key="record.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">{{ formatDate(record.assessment_date) }}</td>
                                <td class="px-4 py-3">{{ record.payload?.hr_bpm }}</td>
                                <td class="px-4 py-3">{{ record.payload?.bp_systolic }}/{{ record.payload?.bp_diastolic }}</td>
                                <td class="px-4 py-3">{{ formatDate(record.next_assessment_date) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Link v-if="canEdit" :href="`/tenant/assess/vitals?member_id=${member.id}&edit=${record.id}`" class="text-orange-400 hover:text-orange-300 text-sm">Edit</Link>
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