<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    canRevenue: Boolean,
    canMembers: Boolean,
    canAttendance: Boolean,
    canStaff: Boolean,
});
</script>

<template>
    <AppLayout>
        <Head title="Reports" />
        
        <div class="flex flex-col gap-6">
            <div>
                <h1 class="text-xl font-semibold">Reports</h1>
                <p class="mt-0.5 text-sm text-slate-400">View analytics and insights for your gym.</p>
            </div>

            <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 max-w-3xl">
                <div class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-white/5 p-6">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-500/15 text-orange-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M16 12h.01"/><path d="M3 9h18"/></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold">Revenue</h2>
                            <p class="mt-0.5 text-xs text-slate-400">Income, payments, and financial performance.</p>
                        </div>
                    </div>
                    <Link v-if="canRevenue" href="/tenant/reports/revenue" class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-orange-400">
                        Open →
                    </Link>
                    <span v-else class="mt-auto text-xs text-slate-400">No access</span>
                </div>

                <div class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-white/5 p-6">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-500/15 text-orange-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="3"/><path d="M20 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 4.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold">Members</h2>
                            <p class="mt-0.5 text-xs text-slate-400">Membership growth and demographics.</p>
                        </div>
                    </div>
                    <Link v-if="canMembers" href="/tenant/reports/members" class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-orange-400">
                        Open →
                    </Link>
                    <span v-else class="mt-auto text-xs text-slate-400">No access</span>
                </div>

                <div class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-white/5 p-6">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-500/15 text-orange-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7V5a1 1 0 0 1 1-1h2"/><path d="M17 4h2a1 1 0 0 1 1 1v2"/><path d="M20 17v2a1 1 0 0 1-1 1h-2"/><path d="M7 20H5a1 1 0 0 1-1-1v-2"/><path d="M7 12h10"/></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold">Attendance</h2>
                            <p class="mt-0.5 text-xs text-slate-400">Check-in trends and peak hours.</p>
                        </div>
                    </div>
                    <Link v-if="canAttendance" href="/tenant/reports/attendance" class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-orange-400">
                        Open →
                    </Link>
                    <span v-else class="mt-auto text-xs text-slate-400">No access</span>
                </div>

                <div class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-white/5 p-6">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-500/15 text-orange-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="7" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M14 20a5 5 0 0 1 7 0"/></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold">Staff</h2>
                            <p class="mt-0.5 text-xs text-slate-400">Staff performance and attendance.</p>
                        </div>
                    </div>
                    <Link v-if="canStaff" href="/tenant/reports/staff" class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-orange-400">
                        Open →
                    </Link>
                    <span v-else class="mt-auto text-xs text-slate-400">No access</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>