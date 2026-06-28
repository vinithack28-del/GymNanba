<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    registrations: Object,
    status: String,
    counts: Object,
});

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AppLayout>
        <Head title="Member Registrations" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Members</p>
                    <h1 class="mt-2 text-3xl font-semibold">Online Registrations</h1>
                    <p class="mt-1 text-slate-300">Review and confirm members who registered via the online form.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link href="/tenant/members" class="flex items-center gap-2 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5">
                        Members
                    </Link>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link href="/tenant/members/registrations?status=pending" :class="['rounded-full border px-4 py-2 text-sm font-semibold', status === 'pending' ? 'border-orange-400 bg-orange-500/10 text-orange-300' : 'border-white/10 bg-white/5 text-slate-300']">
                    Pending <span class="ml-2 rounded-full bg-slate-950/50 px-2 py-0.5 text-xs">{{ counts.pending }}</span>
                </Link>
                <Link href="/tenant/members/registrations?status=confirmed" :class="['rounded-full border px-4 py-2 text-sm font-semibold', status === 'confirmed' ? 'border-orange-400 bg-orange-500/10 text-orange-300' : 'border-white/10 bg-white/5 text-slate-300']">
                    Confirmed <span class="ml-2 rounded-full bg-slate-950/50 px-2 py-0.5 text-xs">{{ counts.confirmed }}</span>
                </Link>
                <Link href="/tenant/members/registrations?status=rejected" :class="['rounded-full border px-4 py-2 text-sm font-semibold', status === 'rejected' ? 'border-orange-400 bg-orange-500/10 text-orange-300' : 'border-white/10 bg-white/5 text-slate-300']">
                    Rejected <span class="ml-2 rounded-full bg-slate-950/50 px-2 py-0.5 text-xs">{{ counts.rejected }}</span>
                </Link>
            </div>

            <div v-if="!registrations || registrations.length === 0" class="flex flex-col items-center gap-3 rounded-[2rem] border border-white/10 bg-white/5 py-20 text-center">
                <div class="text-slate-400 text-4xl">👥</div>
                <p class="text-base font-semibold">No {{ status }} registrations</p>
                <p class="text-sm text-slate-400">Share the registration link to get people to sign up.</p>
            </div>

            <div v-else class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-white/10 text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            <tr>
                                <th class="px-5 py-3.5">Name</th>
                                <th class="px-5 py-3.5">Phone</th>
                                <th class="px-5 py-3.5">Email</th>
                                <th class="px-5 py-3.5">Gender / DOB</th>
                                <th class="px-5 py-3.5">Submitted</th>
                                <th v-if="status === 'pending'" class="px-5 py-3.5 text-right">Actions</th>
                                <th v-else-if="status === 'confirmed'" class="px-5 py-3.5">Confirmed By</th>
                                <th v-else class="px-5 py-3.5">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="reg in registrations" :key="reg.id" class="hover:bg-white/5">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-orange-500/20 text-xs font-bold text-orange-300">
                                            {{ reg.name.charAt(0).toUpperCase() }}
                                        </span>
                                        <span class="font-semibold">{{ reg.name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-mono text-xs">{{ reg.phone }}</td>
                                <td class="px-5 py-4 text-xs text-slate-400">{{ reg.email || '—' }}</td>
                                <td class="px-5 py-4 text-xs text-slate-400">
                                    {{ reg.gender ? reg.gender.charAt(0).toUpperCase() + reg.gender.slice(1) : '—' }}
                                    <span v-if="reg.dob"> · {{ formatDate(reg.dob) }}</span>
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-400">{{ formatDate(reg.created_at) }}</td>
                                <td v-if="status === 'pending'" class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button class="rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-3 py-1.5 text-xs font-semibold text-emerald-400 hover:bg-emerald-500/20">Confirm</button>
                                        <button class="rounded-xl bg-red-500/10 border border-red-500/20 px-3 py-1.5 text-xs font-semibold text-red-400 hover:bg-red-500/20">Reject</button>
                                    </div>
                                </td>
                                <td v-else-if="status === 'confirmed'" class="px-5 py-4 text-xs text-slate-400">{{ reg.confirmed_by || '—' }}</td>
                                <td v-else class="px-5 py-4 text-xs text-slate-400">{{ reg.rejection_reason || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
