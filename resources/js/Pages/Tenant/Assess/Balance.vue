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
    next_measurement_date: '',
    limb_length_cm: '',
    right_anterior: '',
    right_posteromedial: '',
    right_posterolateral: '',
    left_anterior: '',
    left_posteromedial: '',
    left_posterolateral: '',
    notes: '',
});

const submit = () => {
    if (props.editingRecord) {
        form.put(`/tenant/assess/balance/${props.editingRecord.id}`);
    } else {
        form.post('/tenant/assess/balance');
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Balance" />
        
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Assessments</p>
                <h1 class="mt-2 text-3xl font-semibold">Balance</h1>
                <p class="mt-1 text-slate-300">Y-Balance test history and insight generation.</p>
            </div>

            <div v-if="member && (canAdd || (editingRecord && canEdit))" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-lg font-bold mb-4">{{ editingRecord ? 'Save Test' : 'New Test' }}</h3>
                <form @submit.prevent="submit" class="flex flex-col gap-4">
                    <input type="hidden" v-model="form.member_id">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Measurement Date <span class="text-red-400">*</span></label>
                            <input v-model="form.measurement_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Next Measurement Date</label>
                            <input v-model="form.next_measurement_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Limb Length (cm) <span class="text-red-400">*</span></label>
                            <input v-model="form.limb_length_cm" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Right Anterior <span class="text-red-400">*</span></label>
                            <input v-model="form.right_anterior" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Right Posteromedial <span class="text-red-400">*</span></label>
                            <input v-model="form.right_posteromedial" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Right Posterolateral <span class="text-red-400">*</span></label>
                            <input v-model="form.right_posterolateral" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Left Anterior <span class="text-red-400">*</span></label>
                            <input v-model="form.left_anterior" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Left Posteromedial <span class="text-red-400">*</span></label>
                            <input v-model="form.left_posteromedial" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Left Posterolateral <span class="text-red-400">*</span></label>
                            <input v-model="form.left_posterolateral" type="number" step="0.01" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
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
                    Select a client to view balance tests.
                </div>
                <div v-else-if="!records || records.length === 0" class="p-6 text-center text-sm text-slate-400">
                    No balance tests yet.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Composite R</th>
                                <th class="px-4 py-3">Composite L</th>
                                <th class="px-4 py-3">Asymmetry</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="record in records" :key="record.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">{{ formatDate(record.assessment_date) }}</td>
                                <td class="px-4 py-3">{{ record.status?.replace('_', ' ')?.replace(/\b\w/g, l => l.toUpperCase()) }}</td>
                                <td class="px-4 py-3">{{ record.payload?.right?.composite_pct }}%</td>
                                <td class="px-4 py-3">{{ record.payload?.left?.composite_pct }}%</td>
                                <td class="px-4 py-3">{{ record.payload?.asymmetry_pct }}%</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Link v-if="canEdit" :href="`/tenant/assess/balance?member_id=${member.id}&edit=${record.id}`" class="text-orange-400 hover:text-orange-300 text-sm">Edit</Link>
                                        <button v-if="canDelete" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="record in records" :key="`insight-${record.id}`">
                                <td colspan="6" class="px-4 py-3 text-xs text-slate-400">
                                    {{ record.ai_insight || 'No AI insight generated yet.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>