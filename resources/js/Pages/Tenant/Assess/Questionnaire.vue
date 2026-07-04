<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    questionnaires: Object,
    canAdd: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
});

const formatDate = (date) => {
    if (!date) return 'â€”';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const getRiskBadgeClass = (risk) => {
    const classes = {
        low: 'bg-emerald-500/15 text-emerald-300',
        moderate: 'bg-amber-500/15 text-amber-300',
        high: 'bg-red-500/15 text-red-300',
    };
    return classes[risk] || classes.low;
};

const getYesNoBadgeClass = (value) => {
    return value ? 'bg-red-500/15 text-red-300' : 'bg-emerald-500/15 text-emerald-300';
};
</script>

<template>
    <AppLayout>
        <Head title="Questionnaire" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Assessments</p>
                    <h1 class="mt-2 text-3xl font-semibold">Questionnaire</h1>
                    <p class="mt-1 text-slate-300">Manage PAR-Q+ and Physician Clearance questionnaires</p>
                </div>
                <Link v-if="canAdd" href="/tenant/assess/questionnaire/create" class="flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    <span>+</span> Add PAR-Q+
                </Link>
            </div>

            <div class="flex flex-wrap gap-2 border-b border-white/10 pb-4">
                <Link href="/tenant/assess/questionnaire?tab=par_q" class="rounded-lg px-4 py-2 text-sm font-semibold text-orange-400">
                    PAR-Q+
                </Link>
                <Link href="/tenant/assess/questionnaire?tab=physician" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-400 hover:bg-white/5">
                    Physician Clearance
                </Link>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" placeholder="Search..." class="bg-transparent text-sm text-slate-300 outline-none">
                </div>
                <select class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none">
                    <option value="">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                </select>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div v-if="!questionnaires || questionnaires.length === 0" class="flex flex-col items-center gap-4 p-12 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-orange-500/10 text-2xl">ðŸ“‹</div>
                    <p class="text-lg font-bold">No questionnaires found</p>
                    <p class="text-sm text-slate-400">Create your first questionnaire to get started.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Member</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Risk Level</th>
                                <th class="px-4 py-3">Physician Clearance</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="q in questionnaires" :key="q.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">{{ q.member?.name }}</td>
                                <td class="px-4 py-3">{{ formatDate(q.assessment_date) }}</td>
                                <td class="px-4 py-3">{{ q.type }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="getRiskBadgeClass(q.risk_level)">
                                        {{ q.risk_level }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="getYesNoBadgeClass(q.requires_clearance)">
                                        {{ q.requires_clearance ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="q.status === 'completed' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-amber-500/15 text-amber-300'">
                                        {{ q.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Link v-if="canEdit" :href="`/tenant/assess/questionnaire/${q.id}`" class="text-orange-400 hover:text-orange-300 text-sm">View</Link>
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
