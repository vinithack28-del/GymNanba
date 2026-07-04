<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    classData: Object,
    bookings: Object,
});

const formatDate = (date) => {
    if (!date) return 'â€”';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const formatTime = (time) => {
    return time || 'â€”';
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
        <Head :title="classData?.name || 'Class Details'" />
        
        <div class="flex flex-col gap-5">
            <Link href="/tenant/classes" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-orange-400">
                <span>â†</span> Back to Classes
            </Link>

            <div class="grid gap-6 lg:grid-cols-[1fr_26rem] lg:grid-cols-1">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h1 class="text-2xl font-bold">{{ classData?.name }}</h1>
                            <p class="mt-1 text-slate-400">{{ classData?.type }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold uppercase" :class="getStatusColor(classData?.status)">
                            {{ classData?.status }}
                        </span>
                    </div>

                    <div class="grid gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Trainer</span>
                            <span class="font-medium">{{ classData?.trainer?.name || 'â€”' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Day</span>
                            <span class="font-medium">{{ classData?.day }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Time</span>
                            <span class="font-medium">{{ formatTime(classData?.time) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Duration</span>
                            <span class="font-medium">{{ classData?.duration || 'â€”' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Capacity</span>
                            <span class="font-medium">{{ classData?.booked_count || 0 }} / {{ classData?.capacity }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Branch</span>
                            <span class="font-medium">{{ classData?.branch?.name || 'â€”' }}</span>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <Link :href="`/tenant/classes/${classData?.id}/edit`" class="flex-1 rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-center text-sm font-semibold text-slate-300 hover:bg-white/5">
                            Edit Class
                        </Link>
                        <Link :href="`/tenant/classes/${classData?.id}/attendance`" class="flex-1 rounded-lg bg-orange-500 px-4 py-2 text-center text-sm font-semibold text-slate-950 hover:bg-orange-400">
                            Take Attendance
                        </Link>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 overflow-hidden">
                    <div class="border-b border-white/10 p-4">
                        <h3 class="text-lg font-bold">Bookings ({{ bookings?.length || 0 }})</h3>
                    </div>
                    <div v-if="!bookings || bookings.length === 0" class="p-6 text-center text-sm text-slate-400">
                        No bookings yet.
                    </div>
                    <div v-else class="divide-y divide-white/10">
                        <div v-for="booking in bookings" :key="booking.id" class="flex items-center gap-3 p-4">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-500/20 text-xs font-bold text-orange-300">
                                {{ booking.member?.name?.charAt(0) || '?' }}
                            </span>
                            <div class="flex-1">
                                <p class="font-medium">{{ booking.member?.name }}</p>
                                <p class="text-xs text-slate-400">{{ booking.member?.phone }}</p>
                            </div>
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="booking.checked_in ? 'bg-emerald-500/15 text-emerald-300' : 'bg-slate-500/15 text-slate-300'">
                                {{ booking.checked_in ? 'Checked In' : 'Pending' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
