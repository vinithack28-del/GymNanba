<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    members: Object,
    stats: Object,
    selectedBranch: Object,
    registrationUrl: String,
    pendingRegistrationCount: Number,
});

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const getStatusColor = (status) => {
    const colors = {
        active: 'bg-emerald-500/15 text-emerald-300',
        inactive: 'bg-slate-500/15 text-slate-300',
        expired: 'bg-red-500/15 text-red-300',
        frozen: 'bg-sky-500/15 text-sky-300',
    };
    return colors[status] || colors.inactive;
};

const toggleStatus = (member) => {
    useForm({}).patch(`/tenant/members/${member.id}/toggle-status`);
};
</script>

<template>
    <AppLayout>
        <Head title="Members" />
        
        <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Gym Workspace</p>
                    <h1 class="mt-2 text-3xl font-semibold">Members</h1>
                </div>
                <div class="flex items-center gap-2">
                    <Link v-if="registrationUrl" href="/tenant/members/registrations" class="flex items-center gap-2 rounded-full border border-white/10 bg-slate-950/50 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/5">
                        Registrations
                        <span v-if="pendingRegistrationCount > 0" class="flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-orange-500 px-1.5 text-xs font-bold text-slate-950">{{ pendingRegistrationCount }}</span>
                    </Link>
                    <Link href="/tenant/members/create" class="flex items-center gap-2 rounded-full bg-orange-500 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                        <span>+</span> Add Member
                    </Link>
                </div>
            </div>

            <div v-if="selectedBranch" class="flex items-center gap-2">
                <span class="flex items-center gap-1.5 rounded-full border border-orange-400/30 bg-orange-500/10 px-3 py-1 text-xs font-semibold text-orange-300">
                    🏠 {{ selectedBranch.name }}
                </span>
                <span class="text-xs text-slate-400">Showing branch</span>
            </div>

            <div class="grid gap-3 grid-cols-2 sm:grid-cols-4">
                <Link href="/tenant/members" class="rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:bg-white/10">
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">Total</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-200">{{ stats.total }}</p>
                </Link>
                <Link href="/tenant/members?status=active" class="rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:bg-white/10">
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">Active</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-400">{{ stats.active }}</p>
                </Link>
                <Link href="/tenant/members?status=inactive" class="rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:bg-white/10">
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">Inactive</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-400">{{ stats.inactive }}</p>
                </Link>
                <Link href="/tenant/members?status=expired" class="rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:bg-white/10">
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">Expired</p>
                    <p class="mt-2 text-2xl font-semibold text-red-400">{{ stats.expired }}</p>
                </Link>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                <form class="flex flex-wrap items-center gap-2">
                    <div class="flex flex-1 items-center gap-2 rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2">
                        <span class="text-slate-400">🔍</span>
                        <input type="text" name="search" placeholder="Search members..." class="flex-1 bg-transparent text-sm text-slate-300 outline-none placeholder:text-slate-500">
                    </div>
                    <select name="status" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="expired">Expired</option>
                        <option value="frozen">Frozen</option>
                    </select>
                    <select name="gender" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none">
                        <option value="">All Genders</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </form>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
                <div v-if="!members || members.length === 0" class="flex flex-col items-center gap-4 py-20 text-center">
                    <div class="flex h-[4.5rem] w-[4.5rem] items-center justify-center rounded-full border border-white/10 bg-slate-950/50 text-slate-400">
                        👥
                    </div>
                    <p class="text-base font-semibold">No members found</p>
                    <p class="text-sm text-slate-400">Get started by adding your first member</p>
                    <Link href="/tenant/members/create" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">Add Member</Link>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-slate-300">
                            <tr>
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-[0.08em]">ID</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-[0.08em]">Member</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-[0.08em]">Phone</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-[0.08em]">Plan</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-[0.08em]">Joined</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-[0.08em]">Expires</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-[0.08em]">Status</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-[0.08em]">Balance</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-[0.08em]"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="member in members" :key="member.id" class="hover:bg-white/5">
                                <td class="px-4 py-3 font-mono text-xs text-slate-400">{{ member.member_code }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-500/20 text-orange-300 text-xs font-bold">{{ member.initials }}</span>
                                        <div>
                                            <p class="font-medium">{{ member.name }}</p>
                                            <p v-if="member.email" class="text-xs text-slate-400">{{ member.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-400">{{ member.phone }}</td>
                                <td class="px-4 py-3">{{ member.plan_name || '—' }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ formatDate(member.created_at) }}</td>
                                <td class="px-4 py-3">
                                    <span v-if="member.expiry_date" :class="member.expiry_date < new Date() ? 'text-red-400' : ''">{{ formatDate(member.expiry_date) }}</span>
                                    <span v-else class="text-slate-400">—</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold uppercase" :class="getStatusColor(member.effective_status)">
                                        {{ member.status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="member.balance_paise < 0" class="font-semibold text-red-400">{{ member.balance_rupees }}</span>
                                    <span v-else class="text-slate-400">₹0.00</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="`/tenant/members/${member.id}`" class="text-orange-400 hover:text-orange-300">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="members && members.length > 0" class="flex flex-col items-center justify-between gap-3 border-t border-white/10 px-5 py-3 sm:flex-row">
                    <p class="text-xs text-slate-400">Showing 1 to {{ members.length }} of {{ members.length }} records</p>
                    <div class="flex items-center gap-2">
                        <select class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1 text-xs text-slate-300 outline-none">
                            <option>10 / page</option>
                            <option>25 / page</option>
                            <option>50 / page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>