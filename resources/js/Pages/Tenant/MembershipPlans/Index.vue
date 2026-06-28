<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    plans: Object,
    counts: Object,
    canAdd: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
});

const formatCurrency = (paise) => {
    if (!paise) return '₹0';
    return '₹' + (paise / 100).toFixed(2);
};

const getStatusColor = (status) => {
    const colors = {
        active: { bg: 'rgba(29,158,117,0.12)', fg: '#1D9E75' },
        inactive: { bg: 'rgba(136,135,128,0.12)', fg: '#888780' },
        archived: { bg: 'rgba(226,75,74,0.10)', fg: '#E24B4A' },
    };
    return colors[status] || colors.inactive;
};
</script>

<template>
    <AppLayout>
        <Head title="Gym Plans" />
        
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Gym Workspace</p>
                <h1 class="mt-2 text-3xl font-semibold">Gym Plans</h1>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap gap-2">
                    <Link :href="`/tenant/plans?status=`" class="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold" :class="!$page.props.url.split('?')[1]?.includes('status=') ? 'border-orange-400 bg-orange-500/10 text-orange-400' : 'text-slate-400 hover:bg-white/5'">
                        All <span class="ml-1 rounded-full bg-slate-950/50 px-2 py-0.5 text-xs">{{ counts?.all || 0 }}</span>
                    </Link>
                    <Link :href="`/tenant/plans?status=active`" class="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold" :class="$page.props.url.includes('status=active') ? 'border-orange-400 bg-orange-500/10 text-orange-400' : 'text-slate-400 hover:bg-white/5'">
                        Active <span class="ml-1 rounded-full bg-slate-950/50 px-2 py-0.5 text-xs">{{ counts?.active || 0 }}</span>
                    </Link>
                    <Link :href="`/tenant/plans?status=inactive`" class="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold" :class="$page.props.url.includes('status=inactive') ? 'border-orange-400 bg-orange-500/10 text-orange-400' : 'text-slate-400 hover:bg-white/5'">
                        Inactive <span class="ml-1 rounded-full bg-slate-950/50 px-2 py-0.5 text-xs">{{ counts?.inactive || 0 }}</span>
                    </Link>
                    <Link :href="`/tenant/plans?status=archived`" class="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold" :class="$page.props.url.includes('status=archived') ? 'border-orange-400 bg-orange-500/10 text-orange-400' : 'text-slate-400 hover:bg-white/5'">
                        Archived <span class="ml-1 rounded-full bg-slate-950/50 px-2 py-0.5 text-xs">{{ counts?.archived || 0 }}</span>
                    </Link>
                </div>
                <div class="flex items-center gap-2">
                    <Link v-if="canAdd" href="/tenant/plans/create" class="flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                        <span>+</span> Create Plan
                    </Link>
                </div>
            </div>

            <div v-if="!plans || plans.length === 0" class="flex flex-col items-center gap-4 rounded-2xl border border-white/10 bg-white/5 py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-orange-500/10 text-2xl">📋</div>
                <p class="text-lg font-bold">No plans found</p>
                <p class="text-sm text-slate-400">Get started by creating your first membership plan.</p>
                <Link v-if="canAdd" href="/tenant/plans/create" class="mt-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">Create Plan</Link>
            </div>

            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="plan in plans" :key="plan.id" class="rounded-2xl border border-white/10 bg-white/5 p-5" :class="plan.status === 'archived' ? 'opacity-50' : ''">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-950/50">
                            <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 7h6"/><path d="M9 11h6"/><path d="M9 15h4"/></svg>
                        </div>
                        <span class="rounded-full px-2 py-1 text-xs font-bold" :style="{ background: getStatusColor(plan.status).bg, color: getStatusColor(plan.status).fg }">
                            {{ plan.status }}
                        </span>
                    </div>
                    <h3 class="text-lg font-bold" :class="plan.status === 'archived' ? 'line-through opacity-50' : ''">{{ plan.name }}</h3>
                    <p class="mt-1 text-2xl font-bold">{{ formatCurrency(plan.total_price_paise) }}</p>
                    <p v-if="plan.description" class="mt-2 text-sm text-slate-400">{{ plan.description }}</p>
                    <div class="mt-4 flex items-center gap-2">
                        <Link v-if="canEdit" :href="`/tenant/plans/${plan.id}/edit`" class="text-sm text-orange-400 hover:text-orange-300">Edit</Link>
                        <span v-if="plan.status === 'active' && canDelete" class="text-sm text-slate-400">•</span>
                        <button v-if="plan.status === 'active' && canDelete" class="text-sm text-red-400 hover:text-red-300">Archive</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>