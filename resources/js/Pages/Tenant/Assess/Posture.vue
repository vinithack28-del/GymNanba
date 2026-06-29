<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    records: Object,
    editingRecord: Object,
    summary: Object,
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
    member_id: props.editingRecord?.member_id || '',
    assessment_date: new Date().toISOString().split('T')[0],
    status: 'reviewed',
    head_alignment: '',
    shoulder_alignment: '',
    spine_curvature: '',
    hip_tilt: '',
    knee_alignment: '',
    foot_position: '',
    notes: '',
});

const submit = () => {
    if (props.editingRecord) {
        form.put(`/assess/posture/${props.editingRecord.id}`);
    } else {
        form.post('/assess/posture');
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Posture" />
        
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Assessments</p>
                <h1 class="mt-2 text-3xl font-semibold">Posture</h1>
                <p class="mt-1 text-slate-300">View posture history and maintain posture records.</p>
            </div>

            <div class="grid gap-4 grid-cols-3">
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total</p>
                    <p class="mt-1 text-2xl font-bold">{{ summary?.total || 0 }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">This Month</p>
                    <p class="mt-1 text-2xl font-bold">{{ summary?.this_month || 0 }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Last Month</p>
                    <p class="mt-1 text-2xl font-bold">{{ summary?.last_month || 0 }}</p>
                </div>
            </div>

            <div v-if="canAdd || (editingRecord && canEdit)" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-lg font-bold mb-4">{{ editingRecord ? 'Save Assessment' : 'Add Assessment' }}</h3>
                <form @submit.prevent="submit" class="flex flex-col gap-4">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Client ID <span class="text-red-400">*</span></label>
                            <input v-model="form.member_id" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Assessment Date <span class="text-red-400">*</span></label>
                            <input v-model="form.assessment_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Status</label>
                            <select v-model="form.status" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                <option value="reviewed">Reviewed</option>
                                <option value="pending_review">Pending Review</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Head Alignment</label>
                            <input v-model="form.head_alignment" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Shoulder Alignment</label>
                            <input v-model="form.shoulder_alignment" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Spine Curvature</label>
                            <input v-model="form.spine_curvature" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Hip Tilt</label>
                            <input v-model="form.hip_tilt" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Knee Alignment</label>
                            <input v-model="form.knee_alignment" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Foot Position</label>
                            <input v-model="form.foot_position" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-2 block text-sm font-medium">Notes</label>
                            <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                        {{ editingRecord ? 'Save Assessment' : 'Add Assessment' }}
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
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Summary</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-if="recordRows.length === 0">
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-400">No posture assessments found.</td>
                            </tr>
                            <tr v-for="record in recordRows" :key="record.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">{{ record.member?.name }}</td>
                                <td class="px-4 py-3">{{ formatDate(record.assessment_date) }}</td>
                                <td class="px-4 py-3">{{ record.status?.replace('_', ' ')?.replace(/\b\w/g, l => l.toUpperCase()) }}</td>
                                <td class="px-4 py-3 text-xs text-slate-400">
                                    {{ Object.entries(record.payload || {}).filter(([k,v]) => v).slice(0, 2).map(([k,v]) => `${k.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}: ${v}`).join(', ') || '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Link v-if="canEdit" :href="`/assess/posture?edit=${record.id}`" class="text-orange-400 hover:text-orange-300 text-sm">Edit</Link>
                                        <button class="text-slate-400 hover:text-slate-300 text-sm">Print</button>
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
