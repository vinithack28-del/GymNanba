<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    classData: Object,
    trainers: Array,
    branches: Array,
});

const form = useForm({
    name: props.classData?.name || '',
    type: props.classData?.type || 'group',
    trainer_id: props.classData?.trainer_id || '',
    day: props.classData?.day || 'monday',
    time: props.classData?.time || '',
    duration: props.classData?.duration || 60,
    capacity: props.classData?.capacity || 20,
    branch_id: props.classData?.branch_id || '',
    description: props.classData?.description || '',
});

const submit = () => {
    form.put(`/tenant/classes/${props.classData.id}`);
};
</script>

<template>
    <AppLayout>
        <Head title="Edit Class" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Classes</p>
                    <h1 class="mt-2 text-3xl font-semibold">Edit Class</h1>
                    <p class="mt-1 text-slate-300">Update class details.</p>
                </div>
                <Link href="/tenant/classes" class="flex items-center gap-2 rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/5">
                    ← Back to Classes
                </Link>
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-6">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h3 class="text-lg font-bold mb-4">Class Details</h3>
                    
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Class Name <span class="text-red-400">*</span></label>
                            <input v-model="form.name" type="text" placeholder="e.g. Morning Yoga" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Type</label>
                            <select v-model="form.type" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400">
                                <option value="group">Group Class</option>
                                <option value="personal">Personal Training</option>
                                <option value="workshop">Workshop</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Trainer <span class="text-red-400">*</span></label>
                            <select v-model="form.trainer_id" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400" required>
                                <option value="">Select a trainer</option>
                                <option v-for="trainer in trainers" :key="trainer.id" :value="trainer.id">{{ trainer.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Branch</label>
                            <select v-model="form.branch_id" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400">
                                <option value="">Select a branch</option>
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h3 class="text-lg font-bold mb-4">Schedule</h3>
                    
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Day <span class="text-red-400">*</span></label>
                            <select v-model="form.day" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400" required>
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday">Friday</option>
                                <option value="saturday">Saturday</option>
                                <option value="sunday">Sunday</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Time <span class="text-red-400">*</span></label>
                            <input v-model="form.time" type="time" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Duration (minutes)</label>
                            <input v-model="form.duration" type="number" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Capacity</label>
                            <input v-model="form.capacity" type="number" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400">
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h3 class="text-lg font-bold mb-4">Description</h3>
                    <textarea v-model="form.description" rows="3" placeholder="Class description..." class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <Link href="/tenant/classes" class="rounded-lg border border-white/10 bg-slate-950/50 px-6 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">
                        Cancel
                    </Link>
                    <button type="submit" class="rounded-lg bg-orange-500 px-6 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                        Update Class
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>