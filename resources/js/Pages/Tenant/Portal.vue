<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    tenant: Object,
    branch: Object,
    stats: Array,
    revenueChart: Object,
    checkinChart: Object,
    recentPayments: Object,
    birthdays: Object,
    expiredMembers: Object,
    upcomingRenewals: Object,
    renewalTabs: Object,
    canViewRevenue: Boolean,
    canViewAttendance: Boolean,
    canViewRenewals: Boolean,
});

const branchLabel = props.branch?.name || 'All Branches';
const activeRenewalTab = ref(Object.keys(props.renewalTabs || {})[0] || '');
const expiredSearch = ref('');
const renewalSearch = ref('');

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatCurrency = (paise) => {
    if (!paise) return '₹0';
    return '₹' + (paise / 100).toFixed(2);
};

const filteredExpiredMembers = ref(props.expiredMembers || []);
const filteredUpcomingRenewals = ref(props.upcomingRenewals?.[activeRenewalTab.value] || []);

const filterExpired = () => {
    const query = expiredSearch.value.toLowerCase();
    filteredExpiredMembers.value = (props.expiredMembers || []).filter(m => 
        (m.name + ' ' + (m.plan?.name || m.plan_name || '')).toLowerCase().includes(query)
    );
};

const filterRenewals = () => {
    const query = renewalSearch.value.toLowerCase();
    filteredUpcomingRenewals.value = (props.upcomingRenewals?.[activeRenewalTab.value] || []).filter(m => 
        (m.name + ' ' + (m.plan?.name || m.plan_name || '')).toLowerCase().includes(query)
    );
};

const setRenewalTab = (key) => {
    activeRenewalTab.value = key;
    renewalSearch.value = '';
    filterRenewals();
};

onMounted(() => {
    if (props.revenueChart) {
        initRevenueChart();
    }
    if (props.checkinChart) {
        initCheckinChart();
    }
});

const initRevenueChart = () => {
    const ctx = document.getElementById('dashRevenueChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: props.revenueChart.labels,
            datasets: [{
                label: 'Revenue',
                data: props.revenueChart.values,
                borderColor: '#0f766e',
                backgroundColor: 'rgba(15,118,110,0.16)',
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointHoverRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { callback: (value) => '₹' + (value / 100).toFixed(0) } },
            }
        }
    });
};

const initCheckinChart = () => {
    const ctx = document.getElementById('dashCheckinChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: props.checkinChart.labels,
            datasets: [{
                label: 'Check-ins',
                data: props.checkinChart.values,
                borderRadius: 10,
                backgroundColor: '#2563eb',
                maxBarThickness: 34,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
        }
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />
        
        <div class="flex flex-col gap-5">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-lg font-bold">Overview</p>
                        <p class="text-sm text-slate-400">Showing metrics for {{ branchLabel }}{{ branch ? '' : ' across all active branches' }}.</p>
                    </div>
                    <span class="rounded-full border border-white/10 bg-slate-950/50 px-3 py-1 text-xs font-semibold text-slate-400">
                        {{ new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                    </span>
                </div>
            </div>

            <div class="grid gap-4 grid-cols-4">
                <a v-for="card in stats" :key="card.label" :href="card.route" class="rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:-translate-y-0.5 hover:border-orange-400/50 hover:shadow-lg">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ card.label }}</p>
                    <p class="mt-2 text-2xl font-bold">{{ card.value }}</p>
                    <p class="mt-2 text-sm text-slate-400">{{ card.sub }}</p>
                </a>
            </div>

            <div v-if="canViewRevenue || canViewAttendance" class="grid gap-4 grid-cols-2">
                <div v-if="canViewRevenue" class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-lg font-bold">Monthly Revenue</p>
                            <p class="text-sm text-slate-400">Revenue trend for the last 6 months.</p>
                        </div>
                        <Link href="/tenant/reports/revenue" class="text-sm font-bold text-orange-400 hover:underline">View Report</Link>
                    </div>
                    <div class="h-72"><canvas id="dashRevenueChart"></canvas></div>
                </div>

                <div v-if="canViewAttendance" class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-lg font-bold">Weekly Check-ins</p>
                            <p class="text-sm text-slate-400">Daily member check-ins over the last 7 days.</p>
                        </div>
                        <Link href="/tenant/attendance/checkins" class="text-sm font-bold text-orange-400 hover:underline">View Attendance</Link>
                    </div>
                    <div class="h-72"><canvas id="dashCheckinChart"></canvas></div>
                </div>
            </div>

            <div class="grid gap-4 grid-cols-2">
                <div v-if="canViewRevenue" class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-lg font-bold">Recent Payments</p>
                            <p class="text-sm text-slate-400">Latest 5 payments recorded for this branch scope.</p>
                        </div>
                        <Link href="/tenant/payments/history" class="text-sm font-bold text-orange-400 hover:underline">View All</Link>
                    </div>
                    <div v-if="!recentPayments || recentPayments.length === 0" class="py-5 text-center text-sm text-slate-400">No recent payments found.</div>
                    <table v-else class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="px-2 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-400">Client</th>
                                <th class="px-2 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-400">Plan</th>
                                <th class="px-2 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-400">Amount</th>
                                <th class="px-2 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-400">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payment in recentPayments" :key="payment.id" class="border-b border-white/5">
                                <td class="px-2 py-3">{{ payment.member?.name ?? 'Walk-in' }}</td>
                                <td class="px-2 py-3 text-slate-400">{{ payment.plan?.name ?? '—' }}</td>
                                <td class="px-2 py-3 text-right">{{ formatCurrency(payment.total_paise) }}</td>
                                <td class="px-2 py-3 text-slate-400">{{ formatDate(payment.payment_date) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-lg font-bold">Upcoming Birthdays</p>
                            <p class="text-sm text-slate-400">Members with birthdays today or within this week.</p>
                        </div>
                    </div>
                    <div v-if="!birthdays || birthdays.length === 0" class="py-5 text-center text-sm text-slate-400">No birthdays today or this week.</div>
                    <div v-else class="flex flex-col gap-3">
                        <div v-for="member in birthdays" :key="member.id" class="rounded-lg border border-white/10 bg-slate-950/50 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-bold">{{ member.name }}</p>
                                    <p class="text-sm text-slate-400">
                                        {{ new Date(member.next_birthday).toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' }) }}
                                        •
                                        {{ member.birthday_bucket === 'today' ? 'Today' : 'This Week' }}
                                    </p>
                                </div>
                                <a v-if="member.phone" :href="`https://wa.me/${member.phone.replace(/\D/g, '')}?text=${encodeURIComponent('Happy Birthday ' + member.name + '!')}`" target="_blank" class="rounded-lg border border-white/10 bg-slate-950 px-3 py-1.5 text-xs font-bold text-slate-300 hover:border-orange-400 hover:text-orange-400">WhatsApp</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="canViewRenewals" class="grid gap-4 grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-lg font-bold">Expired – Not Renewed</p>
                            <p class="text-sm text-slate-400">Expired plans with no renewal recorded yet.</p>
                        </div>
                        <Link href="/tenant/renewals?tab=expired" class="text-sm font-bold text-orange-400 hover:underline">View All</Link>
                    </div>
                    <input v-model="expiredSearch" @input="filterExpired" type="text" placeholder="Search by client or plan" class="mb-4 w-full rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                    <div v-if="filteredExpiredMembers.length === 0" class="py-5 text-center text-sm text-slate-400">No expired memberships pending renewal.</div>
                    <div v-else class="flex flex-col gap-3">
                        <div v-for="member in filteredExpiredMembers" :key="member.id" class="rounded-lg border border-white/10 bg-slate-950/50 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-bold">{{ member.name }}</p>
                                    <p class="text-sm text-slate-400">{{ member.plan?.name ?? member.plan_name ?? '—' }} • Expired {{ formatDate(member.expiry_date) }}</p>
                                </div>
                                <Link :href="`/tenant/payments/collect?member_id=${member.id}`" class="rounded-lg border border-white/10 bg-slate-950 px-3 py-1.5 text-xs font-bold text-slate-300 hover:border-orange-400 hover:text-orange-400">Add Revenue</Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-lg font-bold">Upcoming Renewals</p>
                            <p class="text-sm text-slate-400">Expiring soon by renewal window.</p>
                        </div>
                        <Link href="/tenant/renewals" class="text-sm font-bold text-orange-400 hover:underline">View All</Link>
                    </div>
                    <div class="mb-4 flex flex-wrap gap-2">
                        <button v-for="(tab, key) in renewalTabs" :key="key" @click="setRenewalTab(key)" class="rounded-full border border-white/10 px-3 py-1.5 text-xs font-bold transition" :class="activeRenewalTab === key ? 'border-orange-400 bg-orange-500/10 text-orange-400' : 'text-slate-400 hover:bg-white/5'">
                            {{ tab.label }}
                        </button>
                    </div>
                    <input v-model="renewalSearch" @input="filterRenewals" type="text" placeholder="Search by client or plan" class="mb-4 w-full rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                    <div v-if="filteredUpcomingRenewals.length === 0" class="py-5 text-center text-sm text-slate-400">No renewals in this window.</div>
                    <div v-else class="flex flex-col gap-3">
                        <div v-for="member in filteredUpcomingRenewals" :key="member.id" class="rounded-lg border border-white/10 bg-slate-950/50 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-bold">{{ member.name }}</p>
                                    <p class="text-sm text-slate-400">{{ member.plan?.name ?? member.plan_name ?? '—' }} • Expires {{ formatDate(member.expiry_date) }}</p>
                                </div>
                                <Link :href="`/tenant/payments/collect?member_id=${member.id}`" class="rounded-lg border border-white/10 bg-slate-950 px-3 py-1.5 text-xs font-bold text-slate-300 hover:border-orange-400 hover:text-orange-400">Add Revenue</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>