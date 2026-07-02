<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    summary: Object,
    branches: Object,
    staffOptions: Object,
    attendance: Object,
    filters: Object,
});

const attendanceForm = useForm({
    staff_id: '',
    date: '',
    check_in: '',
    check_out: '',
    notes: '',
});

const submitAttendance = () => {
    attendanceForm.post('/tenant/staff/attendance');
};
</script>

<template>
    <AppLayout>
        <Head title="Staff Attendance" />
        
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Gym Workspace</p>
                <h1 class="mt-2 text-3xl font-semibold">Staff Attendance</h1>
                <p class="mt-1 text-slate-300">Track staff attendance and working hours.</p>
            </div>

            <div class="grid gap-4 grid-cols-1 md:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Days Present</p>
                    <p class="mt-2 text-2xl font-semibold">{{ summary?.days_present || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Hours Worked</p>
                    <p class="mt-2 text-2xl font-semibold">{{ summary?.hours_worked || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">Leaves Marked</p>
                    <p class="mt-2 text-2xl font-semibold">{{ summary?.leaves_marked || 0 }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <form method="GET" action="/tenant/staff/attendance" class="flex flex-wrap items-center gap-2">
                    <input type="date" name="from" :value="filters?.from" class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                    <input type="date" name="to" :value="filters?.to" class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">

                    <select name="branch_id" class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <option value="">All Branches</option>
                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                    </select>

                    <select name="staff_id" class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                        <option value="">All Staff</option>
                        <option v-for="staff in staffOptions" :key="staff.id" :value="staff.id">{{ staff.name }}</option>
                    </select>

                    <button type="submit" class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm font-semibold text-slate-300 hover:bg-white/5">Apply</button>

                    <Link href="/tenant/staff/attendance?export=csv" class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm font-semibold text-slate-300 hover:bg-white/5">Export</Link>
                </form>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h3 class="mb-5 text-base font-semibold">Add Attendance</h3>
                <form @submit.prevent="submitAttendance" class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Staff Member</label>
                        <select v-model="attendanceForm.staff_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                            <option value="">Select Staff</option>
                            <option v-for="staff in staffOptions" :key="staff.id" :value="staff.id">{{ staff.name }} · {{ staff.role_label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Date</label>
                        <input v-model="attendanceForm.date" type="date" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Check In</label>
                        <input v-model="attendanceForm.check_in" type="time" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Check Out</label>
                        <input v-model="attendanceForm.check_out" type="time" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Notes</label>
                        <input v-model="attendanceForm.notes" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="attendanceForm.processing">Mark Attendance</button>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div v-if="!attendance || attendance.length === 0" class="p-6 text-center text-sm text-slate-400">No attendance records found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Staff</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Check In</th>
                                <th class="px-4 py-3">Check Out</th>
                                <th class="px-4 py-3">Hours</th>
                                <th class="px-4 py-3">Branch</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="record in attendance" :key="record.id" class="hover:bg-white/5">
                                <td class="px-4 py-3 font-semibold">{{ record.staff_name }}</td>
                                <td class="px-4 py-3">{{ record.date }}</td>
                                <td class="px-4 py-3">{{ record.check_in }}</td>
                                <td class="px-4 py-3">{{ record.check_out || '—' }}</td>
                                <td class="px-4 py-3">{{ record.hours_worked || '—' }}</td>
                                <td class="px-4 py-3">{{ record.branch_name || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>