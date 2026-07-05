<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tenant: Object,
    branch: Object,
    stats: Array,
    canViewRevenue: Boolean,
    canViewAttendance: Boolean,
    canViewRenewals: Boolean,
    recentPayments: Object,
    birthdays: Object,
    expiredMembers: Object,
    upcomingRenewals: Object,
    renewalTabs: Object,
});

const branchLabel = props.branch?.name || 'All Branches';

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const formatTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
};

const formatCurrency = (paise) => {
    return 'Rs. ' + (paise / 100).toFixed(2);
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />
        
        <div class="flex flex-col gap-5">
            <div>
                <h1 class="mt-2 text-3xl font-semibold">{{ tenant?.gym_name || 'Gym Dashboard' }}</h1>
                <p class="mt-1 text-slate-300">Operational snapshot for {{ branchLabel }}.</p>
            </div>

            <div class="rounded-[1.6rem] border border-white/10 bg-white/5 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-lg font-semibold">Overview</div>
                        <div class="mt-1 text-sm text-slate-400">Showing metrics for {{ branchLabel }}{{ branch ? '' : ' across all active branches' }}.</div>
                    </div>
                    <span class="rounded-full border border-white/10 bg-slate-950/50 px-3 py-1 text-xs font-semibold text-slate-400">{{ new Date().toLocaleDateString('en-GB').replaceAll('/', '-') }}</span>
                </div>
            </div>

            <div class="grid gap-4 grid-cols-4 xl:grid-cols-4 lg:grid-cols-2 md:grid-cols-1">
                <Link v-for="card in stats" :key="card.label" :href="card.route" class="block rounded-[1.4rem] border border-white/10 bg-gradient-to-b from-white/[8%] to-white/5 p-4 transition hover:-translate-y-0.5 hover:border-orange-400/35 hover:shadow-lg">
                    <div class="text-xs font-bold uppercase tracking-[0.11em] text-slate-400">{{ card.label }}</div>
                    <div class="mt-2 text-2xl font-bold">{{ card.value }}</div>
                    <div class="mt-1 text-sm text-slate-400">{{ card.sub }}</div>
                </Link>
            </div>

            <div v-if="canViewRevenue || canViewAttendance" class="grid gap-4 grid-cols-[1.2fr_0.8fr] lg:grid-cols-1">
                <div v-if="canViewRevenue" class="rounded-[1.6rem] border border-white/10 bg-white/5 p-5">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <div class="text-lg font-semibold">Monthly Revenue</div>
                            <div class="mt-1 text-sm text-slate-400">Revenue trend for the last 6 months.</div>
                        </div>
                        <Link href="/tenant/reports/revenue" class="text-sm font-bold text-orange-400 hover:underline">View Report</Link>
                    </div>
                    <div class="h-[280px] flex items-center justify-center text-slate-400 text-sm">
                        Revenue chart placeholder
                    </div>
                </div>

                <div v-if="canViewAttendance" class="rounded-[1.6rem] border border-white/10 bg-white/5 p-5">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <div class="text-lg font-semibold">Weekly Check-ins</div>
                            <div class="mt-1 text-sm text-slate-400">Daily member check-ins over the last 7 days.</div>
                        </div>
                        <Link href="/tenant/attendance/checkins" class="text-sm font-bold text-orange-400 hover:underline">View Attendance</Link>
                    </div>
                    <div class="h-[280px] flex items-center justify-center text-slate-400 text-sm">
                        Check-in chart placeholder
                    </div>
                </div>
            </div>

            <div class="grid gap-4 grid-cols-[1.1fr_0.9fr] lg:grid-cols-1">
                <div v-if="canViewRevenue" class="rounded-[1.6rem] border border-white/10 bg-white/5 p-5">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <div class="text-lg font-semibold">Recent Payments</div>
                            <div class="mt-1 text-sm text-slate-400">Latest 5 payments recorded for this branch scope.</div>
                        </div>
                        <Link href="/tenant/payments/history" class="text-sm font-bold text-orange-400 hover:underline">View All</Link>
                    </div>
                    <div v-if="!recentPayments || recentPayments.length === 0" class="py-5 text-center text-slate-400 text-sm">
                        No recent payments found.
                    </div>
                    <table v-else class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="pb-3 text-xs font-bold uppercase tracking-[0.1em] text-slate-400">Client</th>
                                <th class="pb-3 text-xs font-bold uppercase tracking-[0.1em] text-slate-400">Plan</th>
                                <th class="pb-3 text-xs font-bold uppercase tracking-[0.1em] text-slate-400 text-right">Amount</th>
                                <th class="pb-3 text-xs font-bold uppercase tracking-[0.1em] text-slate-400">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payment in recentPayments" :key="payment.id" class="border-b border-white/10/65">
                                <td class="py-3">{{ payment.member?.name || 'Walk-in' }}</td>
                                <td class="py-3 text-slate-400">{{ payment.plan?.name || '-' }}</td>
                                <td class="py-3 text-right">{{ formatCurrency(payment.total_paise) }}</td>
                                <td class="py-3 text-slate-400">{{ formatDate(payment.payment_date) }} {{ formatTime(payment.payment_date) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded-[1.6rem] border border-white/10 bg-white/5 p-5">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <div class="text-lg font-semibold">Upcoming Birthdays</div>
                            <div class="mt-1 text-sm text-slate-400">Members with birthdays today or within this week.</div>
                        </div>
                    </div>
                    <div v-if="!birthdays || birthdays.length === 0" class="py-5 text-center text-slate-400 text-sm">
                        No birthdays today or this week.
                    </div>
                    <div v-else class="flex flex-col gap-3">
                        <div v-for="member in birthdays" :key="member.id" class="rounded-[1rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-bold">{{ member.name }}</div>
                                    <div class="mt-1 text-xs text-slate-400">
                                        {{ new Date(member.next_birthday).toLocaleDateString('en-GB').replaceAll('/', '-') }} * {{ member.birthday_bucket === 'today' ? 'Today' : 'This Week' }}
                                    </div>
                                </div>
                                <a v-if="member.phone" :href="`https://wa.me/${member.phone.replace(/\D/g, '')}`" target="_blank" rel="noopener" class="rounded-[0.7rem] border border-white/10 px-3 py-2 text-xs font-bold text-slate-300 hover:border-orange-400 hover:text-orange-400">WhatsApp</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="canViewRenewals" class="grid gap-4 grid-cols-2 lg:grid-cols-1">
                <div class="rounded-[1.6rem] border border-white/10 bg-white/5 p-5">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <div class="text-lg font-semibold">Expired - Not Renewed</div>
                            <div class="mt-1 text-sm text-slate-400">Expired plans with no renewal recorded yet.</div>
                        </div>
                        <Link href="/tenant/renewals?tab=expired" class="text-sm font-bold text-orange-400 hover:underline">View All</Link>
                    </div>
                    <input type="text" placeholder="Search by client or plan" class="w-full rounded-[0.75rem] border border-white/10 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                    <div class="mt-4 flex flex-col gap-3">
                        <div v-if="!expiredMembers || expiredMembers.length === 0" class="py-5 text-center text-slate-400 text-sm">
                            No expired memberships pending renewal.
                        </div>
                        <div v-else class="rounded-[1rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-bold">{{ expiredMembers[0].name }}</div>
                                    <div class="mt-1 text-xs text-slate-400">{{ expiredMembers[0].plan?.name || '-' }} * Expired {{ formatDate(expiredMembers[0].expiry_date) }}</div>
                                </div>
                                <Link :href="`/payments/collect?member_id=${expiredMembers[0].id}`" class="rounded-[0.7rem] border border-white/10 px-3 py-2 text-xs font-bold text-slate-300 hover:border-orange-400 hover:text-orange-400">Add Revenue</Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[1.6rem] border border-white/10 bg-white/5 p-5">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <div class="text-lg font-semibold">Upcoming Renewals</div>
                            <div class="mt-1 text-sm text-slate-400">Expiring soon by renewal window.</div>
                        </div>
                        <Link href="/tenant/renewals" class="text-sm font-bold text-orange-400 hover:underline">View All</Link>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <button v-for="(tab, key) in renewalTabs" :key="key" class="rounded-full border border-white/10 bg-transparent px-3 py-1.5 text-xs font-bold text-slate-400 cursor-pointer hover:border-orange-400/35">
                            {{ tab.label }}
                        </button>
                    </div>
                    <input type="text" placeholder="Search by client or plan" class="w-full rounded-[0.75rem] border border-white/10 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                    <div class="mt-4 flex flex-col gap-3">
                        <div v-if="!upcomingRenewals || Object.keys(upcomingRenewals).length === 0" class="py-5 text-center text-slate-400 text-sm">
                            No renewals in this window.
                        </div>
                        <div v-else class="rounded-[1rem] border border-white/10 bg-slate-950/50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-bold">Sample Member</div>
                                    <div class="mt-1 text-xs text-slate-400">Sample Plan * Expires {{ formatDate(new Date()) }}</div>
                                </div>
                                <Link href="/payments/collect" class="rounded-[0.7rem] border border-white/10 px-3 py-2 text-xs font-bold text-slate-300 hover:border-orange-400 hover:text-orange-400">Add Revenue</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

