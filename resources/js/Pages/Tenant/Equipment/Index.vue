<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    equipment: Object,
    summary: Object,
    types: Object,
    statuses: Object,
    filters: Object,
    canAdd: Boolean,
});
</script>

<template>
    <AppLayout>
        <Head title="Equipment" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Operations</p>
                    <h1 class="mt-2 text-3xl font-semibold">Equipment</h1>
                    <p class="mt-1 text-slate-300">Track gym equipment, status, and maintenance history.</p>
                </div>
                <Link v-if="canAdd" href="/tenant/equipment/create" class="inline-flex items-center gap-2 rounded-full bg-orange-500 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                    Add Equipment
                </Link>
            </div>

            <div class="grid gap-4 grid-cols-2 sm:grid-cols-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Total Equipment</p>
                    <p class="mt-1 text-3xl font-bold">{{ summary?.total || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Operational</p>
                    <p class="mt-1 text-3xl font-bold text-emerald-400">{{ summary?.operational || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Maintenance</p>
                    <p class="mt-1 text-3xl font-bold text-amber-400">{{ summary?.maintenance || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Broken</p>
                    <p class="mt-1 text-3xl font-bold text-red-400">{{ summary?.broken || 0 }}</p>
                </div>
            </div>

            <form method="GET" class="flex flex-wrap items-center gap-3">
                <div class="flex min-w-[200px] flex-1 items-center gap-2 rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" name="search" :value="filters?.search" placeholder="Search name, brand, model…" class="w-full bg-transparent text-sm text-slate-300 outline-none">
                </div>
                <select name="type" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                    <option value="">All Types</option>
                    <option v-for="(label, value) in types" :key="value" :value="value">{{ label }}</option>
                </select>
                <select name="status" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 text-sm text-slate-300 outline-none focus:border-orange-400">
                    <option value="">All Status</option>
                    <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
                </select>
                <Link v-if="filters?.search || filters?.type || filters?.status" href="/tenant/equipment" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 text-sm font-medium text-slate-400 hover:bg-white/5">Clear</Link>
            </form>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div v-if="!equipment || equipment.length === 0" class="p-6 text-center text-sm text-slate-400">No equipment found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Equipment</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Brand</th>
                                <th class="px-4 py-3">Model</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Purchase Date</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="item in equipment" :key="item.id" class="hover:bg-white/5">
                                <td class="px-4 py-3 font-semibold">{{ item.name }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ types[item.type] || item.type }}</td>
                                <td class="px-4 py-3">{{ item.brand || '—' }}</td>
                                <td class="px-4 py-3">{{ item.model || '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="{
                                        'bg-emerald-500/10 text-emerald-400': item.status === 'operational',
                                        'bg-amber-500/10 text-amber-400': item.status === 'maintenance',
                                        'bg-red-500/10 text-red-400': item.status === 'broken'
                                    }">
                                        {{ statuses[item.status] || item.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400">{{ item.purchase_date || '—' }}</td>
                                <td class="px-4 py-3">
                                    <Link :href="`/tenant/equipment/${item.id}/edit`" class="text-orange-400 hover:text-orange-300 text-sm">Edit</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>