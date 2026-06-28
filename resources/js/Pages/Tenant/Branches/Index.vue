<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    branches: Object,
    activeCount: Number,
    planLimit: Number,
    planName: String,
    credentials: Object,
});

const atLimit = props.planLimit > 0 && props.activeCount >= props.planLimit;
const amenityIcons = {
    pool: '🏊',
    steam: '💨',
    parking: '🅿',
    locker: '🔒',
    cafeteria: '☕',
    ac: '❄',
    wifi: '📶',
};
</script>

<template>
    <AppLayout>
        <Head title="Branches" />
        
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Gym Workspace</p>
                <h1 class="mt-2 text-3xl font-semibold">Branches</h1>
                <p class="mt-1 text-slate-300">Manage gym locations and branches.</p>
            </div>

            <div v-if="atLimit" class="flex items-center gap-3 rounded-2xl border border-orange-500/30 bg-orange-500/10 px-4 py-3">
                <svg class="h-4 w-4 shrink-0 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <p class="text-sm text-orange-300">You've reached your plan limit of {{ planLimit }} branches on the {{ planName }} plan.</p>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="rounded-full border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-semibold">
                        {{ planLimit > 0 ? `${activeCount} of ${planLimit}` : `${activeCount} branches` }}
                    </span>
                    <span v-if="branches.filter(b => b.status === 'inactive').length" class="rounded-full border border-white/10 bg-slate-950/50 px-4 py-2 text-sm text-slate-400">
                        {{ branches.filter(b => b.status === 'inactive').length }} inactive
                    </span>
                </div>
                <Link v-if="!atLimit" href="/tenant/branches/create" class="inline-flex items-center gap-2 rounded-2xl bg-orange-500 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Branch
                </Link>
                <span v-else class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-2.5 text-sm font-semibold text-slate-400 opacity-50 cursor-not-allowed">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Branch
                </span>
            </div>

            <div v-if="credentials" class="flex items-center gap-3 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3">
                <svg class="h-5 w-5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold mb-1">Branch Admin Credentials</p>
                    <p class="text-xs text-slate-400 mb-2">Save these credentials for the new branch admin login.</p>
                    <div class="flex flex-wrap gap-4">
                        <div>
                            <span class="text-xs text-slate-400">Email:</span>
                            <code class="ml-2 text-sm font-mono">{{ credentials.email }}</code>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400">Password:</span>
                            <code class="ml-2 text-sm font-mono">{{ credentials.password }}</code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-4">
                <div v-for="branch in branches" :key="branch.id" class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold">{{ branch.name }}</h3>
                                <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="branch.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-400/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20'">
                                    {{ branch.status.charAt(0).toUpperCase() + branch.status.slice(1) }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-400 mb-3">{{ branch.address1 }}{{ branch.address2 ? ', ' + branch.address2 : '' }}, {{ branch.city }} - {{ branch.pin }}</p>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span v-for="amenity in (branch.amenities || [])" :key="amenity" class="rounded-lg border border-white/10 bg-slate-950/50 px-2 py-1 text-xs">
                                    {{ amenityIcons[amenity] || '' }} {{ amenity.charAt(0).toUpperCase() + amenity.slice(1) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400">Manager: {{ branch.manager_name }} · {{ branch.phone }}</p>
                        </div>
                        <div class="flex gap-2">
                            <Link :href="`/tenant/branches/${branch.id}/edit`" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-sm font-medium text-slate-300 hover:bg-white/5">Edit</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>