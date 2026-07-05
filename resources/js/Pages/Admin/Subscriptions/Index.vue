<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    subscriptions: Object,
});

const getStatusClass = (status) => {
    const classes = {
        trial: 'bg-sky-500/15 text-sky-300',
        trial_ended: 'bg-amber-500/15 text-amber-300',
        expired: 'bg-red-500/15 text-red-300',
        cancelled: 'bg-slate-500/15 text-slate-300',
    };
    return classes[status] || 'bg-emerald-500/15 text-emerald-300';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const formatCurrency = (paise) => {
    return 'Rs. ' + (paise / 100).toFixed(2);
};
</script>

<template>
    <AppLayout>
        <Head title="Subscriptions" />
        
        <div class="flex flex-col gap-6">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Module 3</p>
                <h1 class="mt-2 text-3xl font-semibold">Subscriptions</h1>
                <p class="mt-1 text-slate-300">Track plan assignments, trial status, renewal dates, and admin ownership of subscription creation.</p>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
                <table class="w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-slate-950/60 text-slate-300">
                        <tr>
                            <th class="px-4 py-3 font-medium">Gym</th>
                            <th class="px-4 py-3 font-medium">Plan</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Start</th>
                            <th class="px-4 py-3 font-medium">End / Trial</th>
                            <th class="px-4 py-3 font-medium">Price</th>
                            <th class="px-4 py-3 font-medium">Created by</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 bg-white/5">
                        <tr v-if="subscriptions.data && subscriptions.data.length > 0" v-for="subscription in subscriptions.data" :key="subscription.id">
                            <td class="px-4 py-4">{{ subscription.tenant?.gym_name }}</td>
                            <td class="px-4 py-4">{{ subscription.plan?.name }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full px-3 py-1 text-xs uppercase tracking-[0.2em]" :class="getStatusClass(subscription.status)">
                                    {{ subscription.status?.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-4 py-4">{{ formatDate(subscription.start_date) }}</td>
                            <td class="px-4 py-4">
                                <span v-if="subscription.trial_end_date">Trial until {{ formatDate(subscription.trial_end_date) }}</span>
                                <span v-else>{{ formatDate(subscription.end_date) || 'Ongoing' }}</span>
                            </td>
                            <td class="px-4 py-4">{{ formatCurrency(subscription.price_paise) }}</td>
                            <td class="px-4 py-4">{{ subscription.creator?.name || 'System' }}</td>
                        </tr>
                        <tr v-else>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">No subscriptions found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="subscriptions.links" class="flex items-center gap-2">
                <Link v-for="link in subscriptions.links" :key="link.label" :href="link.url || '#'" :class="['rounded-lg px-3 py-2 text-sm', link.active ? 'bg-orange-500 text-slate-950' : 'bg-white/5 text-slate-300 hover:bg-white/10']" v-html="link.label"></Link>
            </div>
        </div>
    </AppLayout>
</template>
