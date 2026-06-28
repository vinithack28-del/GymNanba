<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    member: Object,
});

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const getStatusColor = (status) => {
    const colors = {
        active: 'bg-emerald-100 text-emerald-800',
        inactive: 'bg-slate-100 text-slate-800',
        expired: 'bg-red-100 text-red-800',
        frozen: 'bg-sky-100 text-sky-800',
    };
    return colors[status] || colors.inactive;
};

const getExpiryColor = (date) => {
    if (!date) return '';
    const exp = new Date(date);
    const now = new Date();
    const days = Math.ceil((exp - now) / (1000 * 60 * 60 * 24));
    if (days < 0) return 'text-red-800';
    if (days <= 7) return 'text-amber-800';
    return 'text-emerald-800';
};
</script>

<template>
    <AppLayout>
        <Head :title="member.name" />
        
        <div class="flex flex-col gap-5">
            <Link href="/tenant/members" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-orange-400">
                <span>←</span> Back to Members
            </Link>

            <div class="grid gap-6 grid-cols-[320px_1fr] lg:grid-cols-1">
                <div class="rounded-[1.5rem] border border-white/10 bg-white/5 overflow-hidden">
                    <div class="flex flex-col items-center gap-3 p-6 border-b border-white/10">
                        <div class="flex h-[4.5rem] w-[4.5rem] items-center justify-center rounded-full bg-orange-500/20 text-orange-300 text-2xl font-bold">
                            {{ member.initials }}
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold">{{ member.name }}</p>
                            <p class="text-xs font-mono text-slate-400">{{ member.member_code }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold uppercase" :class="getStatusColor(member.effective_status)">
                            {{ member.status_label }}
                        </span>
                    </div>

                    <div class="flex flex-col gap-4 p-6">
                        <div class="flex items-start gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center text-slate-400">📞</span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.06em] text-slate-400">Phone</p>
                                <p class="mt-1 text-sm">{{ member.phone }}</p>
                            </div>
                        </div>
                        <div v-if="member.email" class="flex items-start gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center text-slate-400">✉️</span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.06em] text-slate-400">Email</p>
                                <p class="mt-1 text-sm">{{ member.email }}</p>
                            </div>
                        </div>
                        <div v-if="member.gender || member.dob" class="flex items-start gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center text-slate-400">👤</span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.06em] text-slate-400">Gender / DOB</p>
                                <p class="mt-1 text-sm">{{ member.gender ? member.gender.charAt(0).toUpperCase() + member.gender.slice(1) : '—' }} {{ member.dob ? '· ' + formatDate(member.dob) : '' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center text-slate-400">📍</span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.06em] text-slate-400">Address</p>
                                <p class="mt-1 text-sm">{{ member.address || '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 p-6 border-t border-white/10">
                        <Link :href="`/tenant/members/${member.id}/edit`" class="flex items-center gap-2 rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm font-semibold text-slate-400 hover:bg-white/5">
                            ✏️ Edit Member
                        </Link>
                        <Link :href="`/tenant/payments/collect?member_id=${member.id}`" class="flex items-center gap-2 rounded-lg bg-orange-500 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                            💰 Collect Fee
                        </Link>
                    </div>
                </div>

                <div class="flex flex-col gap-6">
                    <div class="rounded-[1.5rem] border border-white/10 bg-white/5 overflow-hidden">
                        <div class="flex items-center justify-between p-6 border-b border-white/10">
                            <h3 class="text-lg font-bold">Payment History</h3>
                            <span class="rounded-full border border-white/10 bg-slate-950/50 px-3 py-1 text-xs font-semibold text-slate-400">0 payments</span>
                        </div>
                        <div class="p-6 text-center text-sm text-slate-400">
                            No payment history available.
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6">
                        <h3 class="text-lg font-bold mb-4">Membership Details</h3>
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center gap-3 rounded-lg border border-white/10 bg-slate-950/50 p-3">
                                <span class="text-sm font-semibold">{{ member.plan_name || 'No Plan' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">Joined</span>
                                <span>{{ formatDate(member.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">Expires</span>
                                <span :class="getExpiryColor(member.expiry_date)">{{ formatDate(member.expiry_date) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">Balance</span>
                                <span :class="member.balance_paise < 0 ? 'text-red-400 font-semibold' : ''">{{ member.balance_rupees || '₹0.00' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>