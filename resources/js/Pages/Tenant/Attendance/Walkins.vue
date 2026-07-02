<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    walkins: Object,
    plans: Array,
});

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getPurposeBadge = (purpose) => {
    const badges = {
        day_pass: 'bg-sky-500/15 text-sky-300',
        free_trial: 'bg-emerald-500/15 text-emerald-300',
        inquiry: 'bg-amber-500/15 text-amber-300',
        guest: 'bg-purple-500/15 text-purple-300',
    };
    return badges[purpose] || 'bg-slate-500/15 text-slate-300';
};

const form = useForm({
    name: '',
    phone: '',
    purpose: 'day_pass',
    plan_id: '',
    amount: '',
});

const submit = () => {
    form.post('/walkins');
};
</script>

<template>
    <AppLayout>
        <Head title="Walk-ins & Enquiries" />
        
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Gym Workspace</p>
                <h1 class="mt-2 text-3xl font-semibold">Walk-ins & Enquiries</h1>
                <p class="mt-1 text-slate-300">Manage day passes, trials, and visitor enquiries.</p>
            </div>

            <div class="grid gap-4 grid-cols-2 sm:grid-cols-1">
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.1em] text-slate-400">Today's Walk-ins</p>
                    <p class="mt-1 text-2xl font-bold">{{ stats?.today || 0 }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.1em] text-slate-400">Pending Enquiries</p>
                    <p class="mt-2 text-2xl font-bold text-amber-400">{{ stats?.enquiries || 0 }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <input type="text" placeholder="Search by name or phone..." class="rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                <select class="rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 outline-none">
                    <option value="">All Purposes</option>
                    <option value="day_pass">Day Pass</option>
                    <option value="free_trial">Free Trial</option>
                    <option value="inquiry">Inquiry</option>
                    <option value="guest">Guest</option>
                </select>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1fr_26rem] lg:grid-cols-1">
                <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
                    <div v-if="!walkins || walkins.length === 0" class="flex flex-col items-center gap-4 py-20 text-center">
                        <div class="flex h-[4.5rem] w-[4.5rem] items-center justify-center rounded-full border border-white/10 bg-slate-950/50 text-slate-400 text-2xl">🚶</div>
                        <p class="text-base font-semibold">No walk-ins today</p>
                        <p class="text-sm text-slate-400">Add a new walk-in or enquiry to get started.</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full divide-y divide-white/10 text-left text-sm">
                            <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                                <tr>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Phone</th>
                                    <th class="px-4 py-3">Purpose</th>
                                    <th class="px-4 py-3">Time</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-white/5">
                                <tr v-for="walkin in walkins" :key="walkin.id" class="hover:bg-white/5">
                                    <td class="px-4 py-3 font-medium">{{ walkin.name }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ walkin.phone }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-bold capitalize" :class="getPurposeBadge(walkin.purpose)">
                                            {{ walkin.purpose.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-400">{{ formatDate(walkin.created_at) }}</td>
                                    <td class="px-4 py-3">
                                        <span :class="walkin.status === 'converted' ? 'text-emerald-400' : 'text-slate-400'">
                                            {{ walkin.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5">
                    <div class="border-b border-white/10 p-6">
                        <h3 class="text-lg font-bold">New Walk-in / Enquiry</h3>
                    </div>
                    <form @submit.prevent="submit" class="flex flex-col gap-4 p-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Name <span class="text-red-400">*</span></label>
                            <input v-model="form.name" type="text" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Phone <span class="text-red-400">*</span></label>
                            <input v-model="form.phone" type="tel" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Purpose</label>
                            <select v-model="form.purpose" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400">
                                <option value="day_pass">Day Pass</option>
                                <option value="free_trial">Free Trial</option>
                                <option value="inquiry">Inquiry</option>
                                <option value="guest">Guest</option>
                            </select>
                        </div>
                        <div v-if="form.purpose === 'day_pass'">
                            <label class="mb-2 block text-sm font-medium">Plan</label>
                            <select v-model="form.plan_id" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400">
                                <option value="">Select a plan</option>
                                <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
                            </select>
                        </div>
                        <div v-if="form.purpose === 'day_pass'">
                            <label class="mb-2 block text-sm font-medium">Amount</label>
                            <input v-model="form.amount" type="number" placeholder="₹" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-4 py-2 text-white outline-none focus:border-orange-400">
                        </div>
                        <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="form.processing">
                            Add Walk-in
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
