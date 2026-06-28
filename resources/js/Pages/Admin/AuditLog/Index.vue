<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    logs: Object,
});

const formatDateTime = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AppLayout>
        <Head title="Audit Log" />
        
        <div class="flex flex-col gap-6">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Module 4</p>
                <h1 class="mt-2 text-3xl font-semibold">Audit Log</h1>
                <p class="mt-1 text-slate-300">Immutable event history for admin actions, tenant changes, subscriptions, and settings updates.</p>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
                <table class="w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-slate-950/60 text-slate-300">
                        <tr>
                            <th class="px-4 py-3 font-medium">Timestamp</th>
                            <th class="px-4 py-3 font-medium">Actor</th>
                            <th class="px-4 py-3 font-medium">Action</th>
                            <th class="px-4 py-3 font-medium">Target</th>
                            <th class="px-4 py-3 font-medium">Difference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 bg-white/5">
                        <tr v-if="logs.data && logs.data.length > 0" v-for="log in logs.data" :key="log.id">
                            <td class="px-4 py-4 text-slate-300">{{ formatDateTime(log.created_at) }}</td>
                            <td class="px-4 py-4">
                                <p>{{ log.actor_name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ log.actor_ip }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-sky-500/15 px-3 py-1 text-xs uppercase tracking-[0.2em] text-sky-300">
                                    {{ log.action_type }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <p>{{ log.target_name || 'Platform event' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ log.target_type }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <pre class="whitespace-pre-wrap break-words text-xs text-slate-300">{{ JSON.stringify(log.difference, null, 2) }}</pre>
                            </td>
                        </tr>
                        <tr v-else>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">No audit entries available yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="logs.links" class="flex items-center gap-2">
                <Link v-for="link in logs.links" :key="link.label" :href="link.url || '#'" :class="['rounded-lg px-3 py-2 text-sm', link.active ? 'bg-orange-500 text-slate-950' : 'bg-white/5 text-slate-300 hover:bg-white/10']" v-html="link.label"></Link>
            </div>
        </div>
    </AppLayout>
</template>