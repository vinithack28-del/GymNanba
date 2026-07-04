<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    member: Object,
    record: Object,
    questions: Object,
    followups: Array,
    canAdd: Boolean,
    canEdit: Boolean,
});

const isEdit = !!props.record;

const form = useForm({
    member_id: props.member?.id || '',
    section1: props.record?.payload?.section1 || {},
    section2: props.record?.payload?.section2 || {},
});

const submit = () => {
    form.post('/tenant/assess/questionnaire/save');
};
</script>

<template>
    <AppLayout>
        <Head :title="isEdit ? 'Edit PAR-Q+' : 'Add PAR-Q+'" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-start justify-between">
                <div>
                    <Link href="/tenant/assess/questionnaire" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-orange-400">
                        <span>â†</span> Back to Records
                    </Link>
                    <h1 class="mt-2 text-3xl font-semibold">{{ isEdit ? 'Edit PAR-Q+' : 'Add PAR-Q+' }}</h1>
                    <p class="mt-1 text-slate-300">
                        {{ isEdit ? `Update the PAR-Q+ questionnaire for ${record?.member?.name}` : 'Record a new PAR-Q+ questionnaire for a client.' }}
                    </p>
                </div>
                <span v-if="record" class="rounded-full px-3 py-1 text-xs font-bold" :class="record.status === 'cleared' ? 'bg-emerald-500/15 text-emerald-300' : (record.status === 'conditional' ? 'bg-amber-500/15 text-amber-300' : 'bg-red-500/15 text-red-300')">
                    {{ record.status?.replace('_', ' ')?.replace(/\b\w/g, l => l.toUpperCase()) }}
                </span>
            </div>

            <div v-if="!member" class="rounded-2xl border border-white/10 bg-white/5 p-6 text-center text-sm text-slate-400">
                Select a client to open the PAR-Q+ form.
            </div>

            <form v-else-if="canAdd || (record && canEdit)" @submit.prevent="submit" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <input type="hidden" v-model="form.member_id">

                <div class="mb-6">
                    <h3 class="text-lg font-bold mb-4">Section 1 â€” General Health</h3>
                    <div class="flex flex-col gap-4">
                        <div v-for="(label, id) in questions" :key="id" class="rounded-lg border border-white/10 bg-slate-950/50 p-4">
                            <p class="font-medium">{{ id }}. {{ label }}</p>
                            <div class="mt-3 flex gap-4">
                                <label class="flex items-center gap-2">
                                    <input type="radio" :name="`section1[${id}]`" value="1" v-model="form.section1[id]" class="text-orange-400">
                                    <span>Yes</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" :name="`section1[${id}]`" value="0" v-model="form.section1[id]" class="text-orange-400">
                                    <span>No</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-lg font-bold mb-4">Section 2 â€” Follow-up</h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div v-for="row in followups" :key="row.key" class="rounded-lg border border-white/10 bg-slate-950/50 p-4">
                            <p class="font-medium">{{ row.label }}</p>
                            <p class="text-xs text-slate-400 mt-1">Shown when Q{{ row.trigger }} is Yes.</p>
                            <div class="mt-3 flex gap-4">
                                <label class="flex items-center gap-2">
                                    <input type="radio" :name="`section2[${row.key}]`" value="1" v-model="form.section2[row.key]" class="text-orange-400">
                                    <span>Yes</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" :name="`section2[${row.key}]`" value="0" v-model="form.section2[row.key]" class="text-orange-400">
                                    <span>No</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                    {{ isEdit ? 'Update PAR-Q+' : 'Save PAR-Q+' }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>
