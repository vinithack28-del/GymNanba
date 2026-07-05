<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    expiring: Object,
    expired: Object,
    stats: Object,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const getDaysRemaining = (expiryDate) => {
    if (!expiryDate) return '-';
    const today = new Date();
    const expiry = new Date(expiryDate);
    const diff = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
    return diff;
};
</script>

<template>
    <AppLayout>
        <Head title="Expiry Report" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Expiry Report</h1>
                    <p class="mt-0.5 text-sm text-slate-400">Track membership expirations and renewal opportunities.</p>
                </div>
                <Link href="/tenant/reports" class="text-sm text-slate-400"><- Reports</Link>
            </div>

            <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Expiring in 7 Days</p>
                    <p class="text-2xl font-bold text-orange-400">{{ stats?.expiring7 || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Expiring in 30 Days</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ stats?.expiring30 || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Already Expired</p>
                    <p class="text-2xl font-bold text-red-400">{{ stats?.expired || 0 }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="mb-1 text-xs text-slate-400">Active Members</p>
                    <p class="text-2xl font-bold text-emerald-400">{{ stats?.active || 0 }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div class="border-b border-white/10 px-4 py-3">
                    <h3 class="text-sm font-semibold">Expiring Soon (Next 30 Days)</h3>
                </div>
                <div v-if="!expiring || expiring.length === 0" class="p-6 text-center text-sm text-slate-400">No memberships expiring soon.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-2">Member</th>
                                <th class="px-4 py-2">Plan</th>
                                <th class="px-4 py-2">Branch</th>
                                <th class="px-4 py-2">Expiry Date</th>
                                <th class="px-4 py-2">Days Remaining</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="item in expiring" :key="item.id" class="hover:bg-white/5">
                                <td class="px-4 py-3 font-semibold">{{ item.member_name }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ item.plan_name }}</td>
                                <td class="px-4 py-3">{{ item.branch_name || '-' }}</td>
                                <td class="px-4 py-3">{{ formatDate(item.expiry_date) }}</td>
                                <td class="px-4 py-3 text-orange-400">{{ getDaysRemaining(item.expiry_date) }} days</td>
                                <td class="px-4 py-3">
                                    <Link :href="`/tenant/members/${item.member_id}`" class="text-orange-400 hover:text-orange-300 text-sm">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div class="border-b border-white/10 px-4 py-3">
                    <h3 class="text-sm font-semibold">Expired Memberships</h3>
                </div>
                <div v-if="!expired || expired.length === 0" class="p-6 text-center text-sm text-slate-400">No expired memberships.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-2">Member</th>
                                <th class="px-4 py-2">Plan</th>
                                <th class="px-4 py-2">Branch</th>
                                <th class="px-4 py-2">Expiry Date</th>
                                <th class="px-4 py-2">Days Since</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="item in expired" :key="item.id" class="hover:bg-white/5">
                                <td class="px-4 py-3 font-semibold">{{ item.member_name }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ item.plan_name }}</td>
                                <td class="px-4 py-3">{{ item.branch_name || '-' }}</td>
                                <td class="px-4 py-3">{{ formatDate(item.expiry_date) }}</td>
                                <td class="px-4 py-3 text-red-400">{{ -getDaysRemaining(item.expiry_date) }} days</td>
                                <td class="px-4 py-3">
                                    <Link :href="`/tenant/members/${item.member_id}`" class="text-orange-400 hover:text-orange-300 text-sm">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
