<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    weekStart: String,
    weekEnd: String,
    prevWeek: String,
    nextWeek: String,
    branchId: String,
    view: String,
    branches: Object,
    classes: Object,
});

const formatDate = (date) => {
    if (!date) return 'â€”';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const getStatusColor = (status) => {
    const colors = {
        scheduled: 'bg-emerald-500/15 text-emerald-300 border-l-3 border-emerald-500',
        full: 'bg-sky-500/15 text-sky-300 border-l-3 border-sky-500',
        cancelled: 'bg-amber-500/15 text-amber-300 border-l-3 border-amber-500',
        completed: 'bg-slate-500/15 text-slate-300 border-l-3 border-slate-500',
    };
    return colors[status] || colors.scheduled;
};

const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
const hours = ['6 AM', '7 AM', '8 AM', '9 AM', '10 AM', '11 AM', '12 PM', '1 PM', '2 PM', '3 PM', '4 PM', '5 PM', '6 PM', '7 PM', '8 PM'];
</script>

<template>
    <AppLayout>
        <Head title="Timetable" />
        
        <div class="flex flex-col gap-5">
            <div>
                <h1 class="mt-2 text-3xl font-semibold">Classes</h1>
                <p class="mt-1 text-slate-300">Manage gym classes and schedules.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <Link :href="`/tenant/classes/timetable?week=${prevWeek}&branch_id=${branchId}&view=${view}`" class="rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">
                    â† Previous Week
                </Link>
                <span class="text-sm font-bold">
                    Week of {{ formatDate(weekStart) }} â€“ {{ formatDate(weekEnd) }}
                </span>
                <Link :href="`/tenant/classes/timetable?week=${nextWeek}&branch_id=${branchId}&view=${view}`" class="rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">
                    Next Week â†’
                </Link>
                <Link :href="`/tenant/classes/timetable?branch_id=${branchId}&view=${view}`" class="rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">
                    Today
                </Link>
                <select v-if="branches && branches.length > 0" class="rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 outline-none">
                    <option value="">All Branches</option>
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                </select>
                <Link href="/tenant/classes/create" class="ml-auto rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    + Add Class
                </Link>
            </div>

            <div class="flex flex-wrap gap-4 mb-4">
                <div class="flex items-center gap-2">
                    <div class="h-3 w-3 rounded-full bg-emerald-500"></div>
                    <span class="text-xs text-slate-400">Scheduled</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-3 w-3 rounded-full bg-sky-500"></div>
                    <span class="text-xs text-slate-400">Full</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-3 w-3 rounded-full bg-amber-500"></div>
                    <span class="text-xs text-slate-400">Cancelled</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-3 w-3 rounded-full bg-slate-500"></div>
                    <span class="text-xs text-slate-400">Completed</span>
                </div>
            </div>

            <div class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/5">
                <div class="grid grid-cols-[3.5rem_repeat(7,1fr)]">
                    <div></div>
                    <div v-for="day in days" :key="day" class="px-2 py-3 text-center text-xs font-bold uppercase tracking-[0.05em] text-slate-400 bg-slate-950/60 border-b border-white/10">
                        {{ day }}
                    </div>
                    <template v-for="hour in hours" :key="hour">
                        <div class="px-2 py-4 text-right text-xs text-slate-400 border-r border-white/10">{{ hour }}</div>
                        <div v-for="day in days" :key="`${hour}-${day}`" class="relative border-r border-white/10 min-h-16 border-b border-white/10/40">
                            <div class="absolute left-1 right-1 top-1 bottom-1 rounded-md p-1 text-xs cursor-pointer bg-emerald-500/15 text-emerald-800 border-l-3 border-emerald-500">
                                <p class="font-bold truncate">Sample Class</p>
                                <p class="opacity-80 truncate">Trainer Name</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

