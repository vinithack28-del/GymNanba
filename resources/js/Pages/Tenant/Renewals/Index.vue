<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    members: Object,
    stats: Object,
    plans: Array,
    tab: String,
    from: String,
    to: String,
    today: String,
    planId: [String, Number, null],
});

const tabs = {
    all: 'All',
    expired: 'Expired',
    today: 'Today',
    '3days': '3 Days',
    '7days': '7 Days',
    '30days': '30 Days',
    custom: 'Custom',
};

const statCards = [
    { label: 'Expired', value: props.stats?.expired || 0, tab: 'expired', color: '#E24B4A' },
    { label: 'Today', value: props.stats?.today || 0, tab: 'today', color: '#f97316' },
    { label: '7 Days', value: props.stats?.seven_days || 0, tab: '7days', color: '#EAB308' },
    { label: '30 Days', value: props.stats?.thirty_days || 0, tab: '30days', color: 'var(--app-text)' },
];

const selectedPlanId = ref(props.planId ? String(props.planId) : '');
const renewDrawerOpen = ref(false);
const selectedMemberId = ref(null);
const selectedMemberName = ref('');
const selectedPlanPrice = ref('');

const renewalForm = useForm({
    plan_id: '',
    start_date: '',
    payment_amount: '',
    payment_method: '',
    notes: '',
});

const selectedPlan = computed(() => {
    if (!renewalForm.plan_id) return null;
    return props.plans.find((plan) => String(plan.id) === String(renewalForm.plan_id)) || null;
});

const expiryPreview = computed(() => {
    if (!selectedPlan.value || !renewalForm.start_date) return 'â€”';

    if (Number(selectedPlan.value.session_limit || 0) > 0) {
        const sessions = Number(selectedPlan.value.session_limit);
        return `${sessions} ${sessions === 1 ? 'session' : 'sessions'}`;
    }

    const start = new Date(`${renewalForm.start_date}T00:00:00`);

    if (selectedPlan.value.duration_type === 'months') {
        start.setMonth(start.getMonth() + Number(selectedPlan.value.duration_value || 0));
    } else {
        start.setDate(start.getDate() + Number(selectedPlan.value.duration_value || selectedPlan.value.duration_days || 0));
    }

    return start.toLocaleDateString('en-GB').replaceAll('/', '-');
});

const formatDate = (date) => {
    if (!date) return 'â€”';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const getDaysRemaining = (expiryDate) => {
    if (!expiryDate) return null;
    const today = new Date(new Date().toDateString());
    const expiry = new Date(new Date(expiryDate).toDateString());
    return Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
};

const getDaysText = (days) => {
    if (days === null) return 'â€”';
    if (days < 0) return `${Math.abs(days)}d overdue`;
    if (days === 0) return 'Today';
    return `${days}d left`;
};

const getDaysColor = (days) => {
    if (days === null) return 'text-slate-400';
    if (days < 0) return 'text-red-400';
    if (days === 0) return 'text-orange-400';
    if (days <= 7) return 'text-yellow-400';
    return 'text-emerald-400';
};

const getRenewStartDate = (member) => {
    if (Number(member.session_limit || 0) > 0) {
        return props.today;
    }

    const days = getDaysRemaining(member.expiry_date);
    if (days === null || days < 0) {
        return props.today;
    }

    const start = new Date(`${member.expiry_date}T00:00:00`);
    start.setDate(start.getDate() + 1);
    return start.toISOString().slice(0, 10);
};

const formatCurrency = (amount) => {
    return `â‚¹${Number(amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const getPlanTotal = (plan) => {
    const pricePaise = Number(plan?.price_paise || 0);
    const gstRate = Number(plan?.gst_rate || 0);

    if (plan?.gst_applicable && gstRate > 0) {
        return Math.round(pricePaise * (1 + gstRate / 100)) / 100;
    }

    return pricePaise / 100;
};

const isSessionMember = (member) => Number(member.session_limit || 0) > 0;
const sessionStatus = (member) => `${Number(member.used_sessions || 0)} / ${Number(member.session_limit || 0)} sessions`;

const applyPlanFilter = () => {
    const query = new URLSearchParams();
    query.set('tab', props.tab || '7days');
    if (props.from) query.set('from', props.from);
    if (props.to) query.set('to', props.to);
    if (selectedPlanId.value) query.set('plan_id', selectedPlanId.value);

    window.location.href = `/renewals?${query.toString()}`;
};

const openRenewDrawer = (member) => {
    renewalForm.reset();
    renewalForm.clearErrors();
    renewalForm.plan_id = member.plan_id ? String(member.plan_id) : '';
    renewalForm.start_date = getRenewStartDate(member);
    renewalForm.payment_method = 'cash';
    selectedMemberName.value = member.name;
    renewalForm.payment_amount = selectedPlan.value ? String(getPlanTotal(selectedPlan.value).toFixed(2)) : '';
    selectedPlanPrice.value = selectedPlan.value ? `Plan total: ${formatCurrency(getPlanTotal(selectedPlan.value))}` : '';
    renewDrawerOpen.value = true;
    selectedMemberId.value = member.id;
};

const closeRenewDrawer = () => {
    renewDrawerOpen.value = false;
    selectedMemberId.value = null;
};

const updateSelectedPlanMeta = () => {
    if (!selectedPlan.value) {
        selectedPlanPrice.value = '';
        renewalForm.payment_amount = '';
        return;
    }

    const total = getPlanTotal(selectedPlan.value);
    renewalForm.payment_amount = total.toFixed(2);
    selectedPlanPrice.value = `Plan total: ${formatCurrency(total)}`;
};

const submitRenewal = () => {
    if (!selectedMemberId.value) return;

    renewalForm.post(`/renewals/${selectedMemberId.value}/renew`, {
        preserveScroll: true,
        onSuccess: () => {
            closeRenewDrawer();
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Renewals" />

        <div class="flex flex-col gap-5">
            <div>
                <h1 class="text-3xl font-semibold">Renewals</h1>
                <p class="mt-1 text-slate-300">Track membership expirations and process renewals.</p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <Link
                    v-for="card in statCards"
                    :key="card.tab"
                    :href="`/renewals?tab=${card.tab}`"
                    class="rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:opacity-90"
                    :class="tab === card.tab ? 'border-orange-400 bg-orange-500/10' : ''"
                >
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">{{ card.label }}</p>
                    <p class="mt-2 text-2xl font-semibold" :style="{ color: card.color }">{{ card.value }}</p>
                </Link>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex flex-wrap gap-1">
                        <Link
                            v-for="(label, val) in tabs"
                            :key="val"
                            :href="`/renewals?tab=${val}`"
                            class="rounded-lg px-3 py-1.5 text-sm transition"
                            :class="tab === val ? 'bg-orange-500 font-semibold text-slate-950' : 'text-slate-300 hover:bg-white/5'"
                        >
                            {{ label }}
                        </Link>
                    </div>

                    <div class="ml-auto flex flex-wrap items-center gap-2">
                        <form v-if="tab === 'custom'" method="GET" action="/renewals" class="flex items-center gap-1">
                            <input type="hidden" name="tab" value="custom">
                            <input type="date" name="from" :value="from" class="rounded-lg border border-white/10 bg-slate-950/50 px-2 py-1 text-xs text-slate-300 outline-none focus:border-orange-400">
                            <span class="text-xs text-slate-400">to</span>
                            <input type="date" name="to" :value="to" class="rounded-lg border border-white/10 bg-slate-950/50 px-2 py-1 text-xs text-slate-300 outline-none focus:border-orange-400">
                            <button type="submit" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1 text-xs text-slate-300 hover:bg-white/5">Apply</button>
                        </form>

                        <select v-model="selectedPlanId" @change="applyPlanFilter" class="rounded-lg border border-white/10 bg-slate-950/50 px-2 py-1.5 text-xs text-slate-300 outline-none focus:border-orange-400">
                            <option value="">All Plans</option>
                            <option v-for="plan in plans" :key="plan.id" :value="String(plan.id)">{{ plan.name }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
                <div v-if="!members?.data || members.data.length === 0" class="p-8 text-center text-sm text-slate-400">No renewals found.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-950/60 text-xs font-bold uppercase tracking-[0.08em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Member</th>
                                <th class="px-4 py-3">Member ID</th>
                                <th class="px-4 py-3">Phone</th>
                                <th class="px-4 py-3">Plan</th>
                                <th class="px-4 py-3">Renewal Basis</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Balance</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-white/5">
                            <tr v-for="member in members.data" :key="member.id" class="hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-orange-500/10 text-xs font-bold text-orange-400">
                                            {{ member.initials }}
                                        </span>
                                        <div>
                                            <p class="font-medium">{{ member.name }}</p>
                                            <p v-if="member.email" class="text-xs text-slate-400">{{ member.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3"><span class="font-mono text-xs text-slate-400">{{ member.member_code }}</span></td>
                                <td class="px-4 py-3 text-slate-400">{{ member.phone }}</td>
                                <td class="px-4 py-3">{{ member.plan_name || 'â€”' }}</td>
                                <td class="px-4 py-3">
                                    <span v-if="isSessionMember(member)" class="rounded-full border border-sky-400/30 bg-sky-500/10 px-2 py-0.5 text-xs font-semibold text-sky-300">
                                        {{ sessionStatus(member) }}
                                    </span>
                                    <span v-else-if="getDaysRemaining(member.expiry_date) === 0" class="rounded-full border border-orange-400/30 bg-orange-500/10 px-2 py-0.5 text-xs font-semibold text-orange-400">Today</span>
                                    <span v-else>{{ formatDate(member.expiry_date) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="isSessionMember(member)" :class="Number(member.used_sessions || 0) >= Number(member.session_limit || 0) ? 'text-red-400' : 'text-emerald-400'">
                                        {{ Number(member.used_sessions || 0) >= Number(member.session_limit || 0) ? 'Sessions complete' : 'Sessions left' }}
                                    </span>
                                    <span v-else :class="getDaysColor(getDaysRemaining(member.expiry_date))">
                                        {{ getDaysText(getDaysRemaining(member.expiry_date)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="Number(member.balance_paise) < 0" class="font-semibold text-red-400">{{ member.balance_rupees }}</span>
                                    <span v-else class="text-slate-400">â‚¹0.00</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="`/payments/collect?member_id=${member.id}`" class="rounded-lg border border-orange-400/30 bg-orange-500/10 px-3 py-1.5 text-xs font-semibold text-orange-400 transition hover:bg-orange-500/20">
                                            Process Renewal
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="members?.data?.length" class="flex flex-col items-center justify-between gap-3 border-t border-white/10 px-5 py-3 sm:flex-row">
                    <p class="text-xs text-slate-400">
                        Showing {{ members.from || 0 }} to {{ members.to || 0 }} of {{ members.total || 0 }}
                    </p>
                    <div class="flex items-center gap-2">
                        <Link
                            v-for="link in members.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="link.active ? 'bg-orange-500 text-slate-950' : 'bg-white/5 text-slate-300 hover:bg-white/10'"
                            class="rounded-lg px-3 py-2 text-sm"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div v-if="renewDrawerOpen" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm" @click="closeRenewDrawer"></div>
        <aside :class="renewDrawerOpen ? 'translate-x-0' : 'translate-x-full'" class="fixed right-0 top-0 z-50 flex h-full w-[480px] max-w-full flex-col border-l border-white/10 bg-slate-950/95 transition-transform duration-300">
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold">Process Renewal</h2>
                    <p class="mt-0.5 text-xs text-slate-400">{{ selectedMemberName }}</p>
                </div>
                <button type="button" @click="closeRenewDrawer" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/10 text-slate-400 hover:bg-white/5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="submitRenewal" class="flex flex-1 flex-col overflow-y-auto">
                <div class="flex-1 px-5 py-5">
                    <p class="mb-3 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Plan</p>

                    <div class="mb-4">
                        <label class="mb-1 block text-sm text-slate-400">Plan</label>
                        <select v-model="renewalForm.plan_id" @change="updateSelectedPlanMeta" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm outline-none focus:border-orange-400" required>
                            <option value="">Select plan</option>
                            <option v-for="plan in plans" :key="plan.id" :value="String(plan.id)">{{ plan.name }} - {{ formatCurrency(getPlanTotal(plan)) }}</option>
                        </select>
                        <p v-if="selectedPlanPrice" class="mt-1 text-xs text-slate-400">{{ selectedPlanPrice }}</p>
                        <p v-if="renewalForm.errors.plan_id" class="mt-1 text-xs text-red-400">{{ renewalForm.errors.plan_id }}</p>
                    </div>

                    <div class="mb-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm text-slate-400">Start Date</label>
                            <input v-model="renewalForm.start_date" type="date" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm outline-none focus:border-orange-400" required>
                            <p v-if="renewalForm.errors.start_date" class="mt-1 text-xs text-red-400">{{ renewalForm.errors.start_date }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm text-slate-400">{{ Number(selectedPlan?.session_limit || 0) > 0 ? 'Session Preview' : 'Expiry Preview' }}</label>
                            <div class="rounded-xl border border-dashed border-orange-400/30 bg-orange-500/5 px-3 py-2.5 text-sm text-slate-300">
                                {{ expiryPreview }}
                            </div>
                        </div>
                    </div>

                    <p class="mb-3 mt-6 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Payment</p>

                    <div class="mb-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm text-slate-400">Payment Amount</label>
                            <input v-model="renewalForm.payment_amount" type="number" min="0" step="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm outline-none focus:border-orange-400" placeholder="0.00">
                            <p v-if="renewalForm.errors.payment_amount" class="mt-1 text-xs text-red-400">{{ renewalForm.errors.payment_amount }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm text-slate-400">Payment Method</label>
                            <select v-model="renewalForm.payment_method" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm outline-none focus:border-orange-400">
                                <option value="">Select method</option>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="card">Card</option>
                                <option value="bank">Bank</option>
                                <option value="cheque">Cheque</option>
                            </select>
                            <p v-if="renewalForm.errors.payment_method" class="mt-1 text-xs text-red-400">{{ renewalForm.errors.payment_method }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm text-slate-400">Notes</label>
                        <textarea v-model="renewalForm.notes" rows="3" maxlength="300" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm outline-none focus:border-orange-400" placeholder="Optional"></textarea>
                        <p v-if="renewalForm.errors.notes" class="mt-1 text-xs text-red-400">{{ renewalForm.errors.notes }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-white/10 px-5 py-4">
                    <button type="button" @click="closeRenewDrawer" class="rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-300 hover:bg-white/5">Cancel</button>
                    <button type="submit" :disabled="renewalForm.processing" class="rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400 disabled:cursor-not-allowed disabled:opacity-60">
                        {{ renewalForm.processing ? 'Processing...' : 'Process Renewal' }}
                    </button>
                </div>
            </form>
        </aside>
    </AppLayout>
</template>

