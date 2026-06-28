<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tenants: Object,
    statuses: Array,
    businessTypes: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const businessType = ref(props.filters.business_type || '');

const getBadgeClass = (status) => {
    const classes = {
        active: 'bg-emerald-500/15 text-emerald-400',
        trial: 'bg-sky-500/15 text-sky-400',
        trial_ended: 'bg-amber-500/15 text-amber-400',
        subscription_expired: 'bg-orange-500/15 text-orange-400',
        suspended: 'bg-red-500/15 text-red-400',
    };
    return classes[status] || 'bg-slate-500/15 text-slate-400';
};

const getDaysLeft = (expDate) => {
    if (!expDate) return null;
    const now = new Date();
    const exp = new Date(expDate);
    const diff = Math.ceil((exp - now) / (1000 * 60 * 60 * 24));
    return diff;
};

const getDaysLeftText = (days) => {
    if (days === null) return '—';
    if (days < 0) return Math.abs(days) + 'd ago';
    if (days === 0) return 'Today';
    return days + 'd left';
};

const getDaysLeftColor = (days) => {
    if (days === null) return 'text-slate-400';
    if (days < 0) return 'text-red-400';
    if (days <= 7) return 'text-amber-400';
    if (days <= 30) return 'text-sky-400';
    return 'text-slate-400';
};
</script>

<template>
    <AppLayout>
        <Head title="Tenants" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Tenants</p>
                    <h1 class="mt-2 text-3xl font-semibold">Manage Gym Tenants</h1>
                    <p class="mt-1 text-slate-300">View and manage all gym tenants on the platform.</p>
                </div>
                <Link
                    href="/admin/tenants/new"
                    class="inline-flex items-center gap-2 rounded-full bg-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-orange-400"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                    Add New
                </Link>
            </div>

            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
                <form method="GET" class="flex flex-wrap items-end gap-3">
                    <input type="text" name="search" v-model="search" placeholder="Search tenants..." class="flex-1 min-w-[200px] rounded-2xl border border-white/10 bg-white/5 px-4 py-3 outline-none focus:border-orange-400">
                    <select name="status" v-model="status" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 outline-none focus:border-orange-400">
                        <option value="">All Statuses</option>
                        <option v-for="s in statuses" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
                    </select>
                    <select name="business_type" v-model="businessType" class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 outline-none focus:border-orange-400">
                        <option value="">All Business Types</option>
                        <option v-for="type in businessTypes" :key="type" :value="type">{{ type }}</option>
                    </select>
                    <button type="submit" class="rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-orange-400">Apply</button>
                    <Link v-if="search || status || businessType" href="/admin/tenants" class="rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold transition hover:bg-white/10">Clear</Link>
                </form>
            </div>

            <div class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/5">
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-white/10 text-left text-sm">
                        <thead class="bg-slate-950/60 text-slate-300">
                            <tr>
                                <th class="px-4 py-3 font-medium">Gym</th>
                                <th class="px-4 py-3 font-medium">Owner</th>
                                <th class="px-4 py-3 font-medium">Subdomain</th>
                                <th class="px-4 py-3 font-medium">Plan</th>
                                <th class="px-4 py-3 font-medium">Expiry</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium">Members</th>
                                <th class="px-4 py-3 font-medium">Created</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr v-if="tenants.data && tenants.data.length > 0" v-for="tenant in tenants.data" :key="tenant.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ tenant.gym_name }}</p>
                                    <p class="text-xs text-slate-400">{{ tenant.business_type }} · {{ tenant.city }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p>{{ tenant.owner_name }}</p>
                                    <p class="text-xs text-slate-400">{{ tenant.owner_email }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-slate-950 px-2 py-1 text-xs font-mono text-slate-400">{{ tenant.subdomain }}.gymos.in</span>
                                </td>
                                <td class="px-4 py-3">
                                    <p>{{ tenant.latest_sub?.plan?.name || '—' }}</p>
                                    <p v-if="tenant.latest_sub?.status === 'partial'" class="text-xs text-amber-400">Part paid</p>
                                </td>
                                <td class="px-4 py-3">
                                    <template v-if="tenant.latest_sub?.end_date || tenant.latest_sub?.trial_end_date">
                                        <p class="text-sm">{{ new Date(tenant.latest_sub.end_date || tenant.latest_sub.trial_end_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}</p>
                                        <p class="text-xs" :class="getDaysLeftColor(getDaysLeft(tenant.latest_sub.end_date || tenant.latest_sub.trial_end_date))">{{ getDaysLeftText(getDaysLeft(tenant.latest_sub.end_date || tenant.latest_sub.trial_end_date)) }}</p>
                                    </template>
                                    <span v-else class="text-slate-400">—</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wider" :class="getBadgeClass(tenant.status)">{{ tenant.status }}</span>
                                </td>
                                <td class="px-4 py-3">{{ tenant.members_count }}</td>
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ new Date(tenant.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Link :href="`/admin/tenants/${tenant.id}`" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 text-sky-400 hover:bg-sky-500/20" title="View">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.06 12.35a1 1 0 0 1 0-.7C3.76 7.2 7.52 4 12 4s8.24 3.2 9.94 7.65a1 1 0 0 1 0 .7C20.24 16.8 16.48 20 12 20S3.76 16.8 2.06 12.35Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </Link>
                                        <Link :href="`/admin/tenants/${tenant.id}/edit`" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400 hover:bg-amber-500/20" title="Edit">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg>
                                        </Link>
                                        <Link :href="`/admin/tenants/${tenant.id}/delete`" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20" title="Delete">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-else>
                                <td colspan="9" class="px-4 py-8 text-center text-slate-400">No tenants found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="tenants.data && tenants.data.length > 0" class="flex flex-wrap items-center justify-between gap-4 rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                <p class="text-xs text-slate-400">Showing {{ tenants.from || 0 }} to {{ tenants.to || 0 }} of {{ tenants.total }} tenants</p>
                <div class="flex items-center gap-4">
                    <select class="rounded-2xl border border-white/10 bg-white/5 px-3 py-2 text-xs outline-none focus:border-orange-400" @change="window.location.href=`/admin/tenants?per_page=${$event.target.value}`">
                        <option :selected="tenants.per_page === 10" value="10">10 / page</option>
                        <option :selected="tenants.per_page === 25" value="25">25 / page</option>
                        <option :selected="tenants.per_page === 50" value="50">50 / page</option>
                        <option :selected="tenants.per_page === 100" value="100">100 / page</option>
                    </select>
                    <div class="flex items-center gap-2">
                        <Link v-for="link in tenants.links" :key="link.label" :href="link.url || '#'" :class="['rounded-lg px-3 py-2 text-sm', link.active ? 'bg-orange-500 text-slate-950' : 'bg-white/5 text-slate-300 hover:bg-white/10']" v-html="link.label"></Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>