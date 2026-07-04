<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    roles: Object,
    staffCounts: Object,
});

const roleColors = {
    receptionist: { color: '#06b6d4', light: 'rgba(6,182,212,0.12)', border: 'rgba(6,182,212,0.3)' },
    trainer: { color: '#a855f7', light: 'rgba(168,85,247,0.12)', border: 'rgba(168,85,247,0.3)' },
    accountant: { color: '#f59e0b', light: 'rgba(245,158,11,0.12)', border: 'rgba(245,158,11,0.3)' },
    pos: { color: '#3b82f6', light: 'rgba(59,130,246,0.12)', border: 'rgba(59,130,246,0.3)' },
    branch_manager: { color: '#10b981', light: 'rgba(16,185,129,0.12)', border: 'rgba(16,185,129,0.3)' },
};

const getRoleStyle = (role) => {
    return roleColors[role] || { color: 'var(--app-brand)', light: 'color-mix(in srgb,var(--app-brand) 12%,transparent)', border: 'color-mix(in srgb,var(--app-brand) 30%,transparent)' };
};

const totalRoles = roles?.length || 0;
const sysCount = roles?.filter(r => r.is_system).length || 0;
const custCount = totalRoles - sysCount;
</script>

<template>
    <AppLayout>
        <Head title="Staff Roles" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="mt-2 text-3xl font-semibold">Staff Roles</h1>
                    <p class="mt-1 text-slate-300">Manage staff roles and permissions.</p>
                </div>
                <Link href="/tenant/staff/roles/create" class="inline-flex items-center gap-2 rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    Add Role
                </Link>
            </div>

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
                <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5">
                    <span class="text-xs font-medium uppercase tracking-[0.14em] text-slate-400">Total Roles</span>
                    <span class="text-base font-bold">{{ totalRoles }}</span>
                </div>
                <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5">
                    <span class="text-xs font-medium uppercase tracking-[0.14em] text-slate-400">System</span>
                    <span class="text-base font-bold text-slate-400">{{ sysCount }}</span>
                </div>
                <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5">
                    <span class="text-xs font-medium uppercase tracking-[0.14em] text-slate-400">Custom</span>
                    <span class="text-base font-bold text-orange-400">{{ custCount }}</span>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div v-for="role in roles" :key="role.id" class="overflow-hidden rounded-2xl border border-white/10 bg-white/5 flex flex-col">
                    <div class="h-1 w-full" :style="{ background: getRoleStyle(role.role).color }"></div>
                    <div class="p-5 flex flex-col flex-1 gap-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="h-11 w-11 shrink-0 rounded-xl flex items-center justify-center font-bold text-base border" :style="{ background: getRoleStyle(role.role).light, color: getRoleStyle(role.role).color, borderColor: getRoleStyle(role.role).border }">
                                    {{ role.role.charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-sm truncate">{{ role.display_name || role.role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</p>
                                    <p class="text-xs font-mono mt-0.5 truncate text-slate-400">{{ role.role }}</p>
                                </div>
                            </div>
                            <span class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-full" :class="role.is_system ? 'bg-slate-500/10 text-slate-400 border border-slate-500/20' : 'bg-orange-500/10 text-orange-400 border border-orange-400/20'">
                                {{ role.is_system ? 'System' : 'Custom' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 text-xs text-slate-400">
                            <span>{{ Object.keys(role.permissions || {}).length }} permissions</span>
                            <span>{{ staffCounts[role.role] || 0 }} staff</span>
                        </div>
                        <div class="flex gap-2 mt-auto">
                            <Link :href="`/tenant/staff/roles/${role.id}/edit`" class="flex-1 rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-center text-sm font-medium text-slate-300 hover:bg-white/5">Edit</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

