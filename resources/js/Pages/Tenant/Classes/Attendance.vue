<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    classData: Object,
    bookings: Object,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const toggleAttendance = (bookingId) => {
    useForm({}).patch(`/tenant/classes/${props.classData.id}/attendance/${bookingId}`);
};

const markAllPresent = () => {
    useForm({}).post(`/tenant/classes/${props.classData.id}/attendance/mark-all`);
};
</script>

<template>
    <AppLayout>
        <Head :title="`Attendance - ${classData?.name}`" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Classes</p>
                    <h1 class="mt-2 text-3xl font-semibold">Class Attendance</h1>
                    <p class="mt-1 text-slate-300">{{ classData?.name }} Ã‚- {{ classData?.day }} at {{ classData?.time }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="`/tenant/classes/${classData?.id}`" class="flex items-center gap-2 rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/5">
                        <- Back to Class
                    </Link>
                    <button @click="markAllPresent" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                        Mark All Present
                    </button>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 overflow-hidden">
                <div class="border-b border-white/10 p-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold">Bookings ({{ bookings?.length || 0 }})</h3>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="flex items-center gap-2">
                            <div class="h-3 w-3 rounded-full bg-emerald-500"></div>
                            <span class="text-slate-400">Present: {{ bookings?.filter(b => b.checked_in).length || 0 }}</span>
                        </span>
                        <span class="flex items-center gap-2">
                            <div class="h-3 w-3 rounded-full bg-slate-500"></div>
                            <span class="text-slate-400">Absent: {{ bookings?.filter(b => !b.checked_in).length || 0 }}</span>
                        </span>
                    </div>
                </div>
                <div v-if="!bookings || bookings.length === 0" class="p-6 text-center text-sm text-slate-400">
                    No bookings for this class.
                </div>
                <div v-else class="divide-y divide-white/10">
                    <div v-for="booking in bookings" :key="booking.id" class="flex items-center gap-4 p-4 hover:bg-white/5">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-500/20 text-sm font-bold text-orange-300">
                            {{ booking.member?.name?.charAt(0) || '?' }}
                        </span>
                        <div class="flex-1">
                            <p class="font-medium">{{ booking.member?.name }}</p>
                            <p class="text-xs text-slate-400">{{ booking.member?.phone }}</p>
                        </div>
                        <button 
                            @click="toggleAttendance(booking.id)"
                            class="rounded-lg px-4 py-2 text-sm font-semibold transition"
                            :class="booking.checked_in ? 'bg-emerald-500/15 text-emerald-300 hover:bg-emerald-500/25' : 'bg-slate-500/15 text-slate-300 hover:bg-slate-500/25'"
                        >
                            {{ booking.checked_in ? 'Present' : 'Mark Present' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
