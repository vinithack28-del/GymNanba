<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    classes: Object,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const getStatusColor = (status) => {
    const colors = {
        scheduled: 'bg-emerald-500/15 text-emerald-300',
        full: 'bg-sky-500/15 text-sky-300',
        cancelled: 'bg-amber-500/15 text-amber-300',
        completed: 'bg-slate-500/15 text-slate-300',
    };
    return colors[status] || colors.scheduled;
};
</script>

<template>
    <AppLayout>
        <Head title="Classes" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="mt-2 text-3xl font-semibold">Classes</h1>
                    <p class="mt-1 text-slate-300">Manage gym classes and schedules.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link href="/tenant/classes/timetable" class="flex items-center gap-2 rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/5">
                        Ã°Å¸â€œ... Timetable
                    </Link>
                    <Link href="/tenant/classes/trainers" class="flex items-center gap-2 rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/5">
                        Ã°Å¸â€˜Â¨Ã¢â‚¬ÂÃ°Å¸ÂÂ« Trainers
                    </Link>
                    <Link href="/tenant/classes/create" class="flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                        <span>+</span> Add Class
                    </Link>
                </div>
            </div>

            <div v-if="!classes || classes.length === 0" class="flex flex-col items-center gap-4 rounded-[2rem] border border-white/10 bg-white/5 py-20 text-center">
                <div class="flex h-[4.5rem] w-[4.5rem] items-center justify-center rounded-full border border-white/10 bg-slate-950/50 text-slate-400 text-2xl">Ã°Å¸Ââ€¹Ã¯Â¸Â</div>
                <p class="text-lg font-bold">No classes found</p>
                <p class="text-sm text-slate-400">Create your first class to get started.</p>
                <Link href="/tenant/classes/create" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">Add Class</Link>
            </div>

            <div v-else class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Class Name</th>
                                <th class="px-4 py-3">Trainer</th>
                                <th class="px-4 py-3">Schedule</th>
                                <th class="px-4 py-3">Capacity</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="cls in classes" :key="cls.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <p class="font-medium">{{ cls.name }}</p>
                                    <p class="text-xs text-slate-400">{{ cls.type }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-400">{{ cls.trainer?.name || '-' }}</td>
                                <td class="px-4 py-3 text-slate-400">
                                    {{ cls.day }} Ã‚- {{ cls.time }}
                                </td>
                                <td class="px-4 py-3 text-slate-400">
                                    {{ cls.booked_count || 0 }} / {{ cls.capacity }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold uppercase" :class="getStatusColor(cls.status)">
                                        {{ cls.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="`/tenant/classes/${cls.id}`" class="text-orange-400 hover:text-orange-300">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

