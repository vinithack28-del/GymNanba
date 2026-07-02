<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    tab: String,
    renewalsDue: Array,
    tenants: Array,
    plans: Array,
    payments: Object,
});

const currentTab = computed(() => props.tab || 'renewal_due');
const tenantSearch = ref('');
const selectedTenantId = ref('');
const tenantDropdownOpen = ref(false);

const renewalsDueMap = computed(() => {
    const map = new Map();
    (props.renewalsDue || []).forEach((tenant) => {
        map.set(String(tenant.id), tenant);
    });
    return map;
});

const tenantOptions = computed(() =>
    (props.tenants || []).map((tenant) => {
        const dueTenant = renewalsDueMap.value.get(String(tenant.id));
        return {
            ...tenant,
            _sub: dueTenant?._sub || null,
            _balance_paise: dueTenant?._balance_paise || 0,
        };
    })
);

const renewalForm = useForm({
    tenant_id: '',
    plan_id: '',
    paid_at: new Date().toISOString().slice(0, 10),
    notes: '',
    splits: [
        { method: 'Cash', amount: '', reference: '' },
    ],
});

const partPaymentForm = useForm({
    subscription_id: '',
    paid_at: new Date().toISOString().slice(0, 10),
    notes: '',
    splits: [
        { method: 'Cash', amount: '', reference: '' },
    ],
});

const filteredTenants = computed(() => {
    const q = tenantSearch.value.trim().toLowerCase();
    if (!q) return tenantOptions.value;
    return tenantOptions.value.filter((tenant) => tenant.gym_name.toLowerCase().includes(q));
});

const selectedTenantDue = computed(() =>
    renewalsDueMap.value.get(String(selectedTenantId.value)) || null
);

const selectedTenant = computed(() =>
    tenantOptions.value.find((tenant) => String(tenant.id) === String(selectedTenantId.value)) || null
);

const hasPendingBalance = computed(() =>
    selectedTenant.value && selectedTenant.value._balance_paise > 0
);

const selectedPlan = computed(() =>
    props.plans.find((plan) => String(plan.id) === String(renewalForm.plan_id)) || null
);

const partialSubscriptions = computed(() => {
    const map = new Map();

    (props.payments?.data || []).forEach((payment) => {
        const subscription = payment.subscription;
        if (!subscription || subscription.status !== 'partial' || map.has(subscription.id)) {
            return;
        }

        const paidPaise = (props.payments.data || [])
            .filter((row) => row.subscription_id === subscription.id)
            .reduce((sum, row) => sum + Number(row.amount_paise || 0), 0);

        map.set(subscription.id, {
            id: subscription.id,
            tenantName: payment.tenant?.gym_name || 'Tenant',
            planName: subscription.plan?.name || 'Plan',
            duePaise: Math.max(0, Number(subscription.price_paise || 0) - paidPaise),
        });
    });

    return Array.from(map.values());
});

const renewalAmountPaise = computed(() =>
    renewalForm.splits.reduce((sum, split) => sum + Math.round(Number(split.amount || 0) * 100), 0)
);

const effectiveDuePaise = computed(() => {
    if (!selectedPlan.value) return 0;
    const tenant = selectedTenant.value;
    if (tenant && tenant._balance_paise > 0 && String(tenant._sub?.plan_id) === String(renewalForm.plan_id)) {
        return tenant._balance_paise;
    }
    return Number(selectedPlan.value.price_paise || 0);
});

const renewalBalancePaise = computed(() => {
    if (!selectedPlan.value) return 0;
    return Math.max(0, effectiveDuePaise.value - renewalAmountPaise.value);
});

const renewalExcessPaise = computed(() => {
    if (!selectedPlan.value) return 0;
    return Math.max(0, renewalAmountPaise.value - effectiveDuePaise.value);
});

const renewalShortPaise = computed(() => {
    if (!selectedPlan.value) return 0;
    return Math.max(0, effectiveDuePaise.value - renewalAmountPaise.value);
});

const renewalStatusTone = computed(() => {
    if (!selectedPlan.value) return 'text-slate-400';
    if (renewalExcessPaise.value > 0) return 'text-red-400';
    if (renewalShortPaise.value > 0) return 'text-amber-400';
    return 'text-emerald-400';
});

const renewalStatusMessage = computed(() => {
    if (!selectedPlan.value) return '';
    if (renewalAmountPaise.value <= 0) return 'Enter a payment amount to process renewal.';
    if (renewalExcessPaise.value > 0) return `Excess payment of ${formatCurrency(renewalExcessPaise.value)} is not allowed.`;
    if (renewalShortPaise.value > 0) return `Part payment - subscription will be marked partial. Balance due ${formatCurrency(renewalShortPaise.value)}.`;
    return 'Full payment - subscription will be activated.';
});

const canSubmitRenewal = computed(() =>
    !!renewalForm.tenant_id
    && !!renewalForm.plan_id
    && renewalAmountPaise.value > 0
    && renewalExcessPaise.value === 0
);

const partPaymentAmountPaise = computed(() =>
    partPaymentForm.splits.reduce((sum, split) => sum + Math.round(Number(split.amount || 0) * 100), 0)
);

const formatCurrency = (paise) => `Rs. ${(Number(paise || 0) / 100).toFixed(2)}`;

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const getDaysLeft = (expDate) => {
    if (!expDate) return null;
    const now = new Date();
    const exp = new Date(expDate);
    return Math.ceil((exp - now) / (1000 * 60 * 60 * 24));
};

const getDueBadgeClass = (days) => {
    if (days === null) return 'bg-slate-500/15 text-slate-300';
    if (days <= 0) return 'bg-red-500/15 text-red-300';
    if (days <= 7) return 'bg-amber-500/15 text-amber-300';
    return 'bg-sky-500/15 text-sky-300';
};

const getPaymentTypeClass = (type) => {
    const classes = {
        new: 'bg-sky-500/15 text-sky-300',
        renewal: 'bg-emerald-500/15 text-emerald-300',
        part_payment: 'bg-amber-500/15 text-amber-300',
        manual: 'bg-slate-500/15 text-slate-300',
    };
    return classes[type] || 'bg-slate-500/15 text-slate-300';
};

const selectTenant = (tenant) => {
    selectedTenantId.value = String(tenant.id);
    tenantSearch.value = tenant.gym_name;
    renewalForm.tenant_id = tenant.id;
    renewalForm.plan_id = tenant._sub?.plan_id ? String(tenant._sub.plan_id) : '';
    tenantDropdownOpen.value = false;

    const tenantPlan = props.plans.find((plan) => String(plan.id) === String(tenant._sub?.plan_id));
    if (tenantPlan) {
        // Use the remaining balance for tenants with partial payment, otherwise full plan price
        const amountPaise = tenant._balance_paise > 0 ? tenant._balance_paise : Number(tenantPlan.price_paise || 0);
        renewalForm.splits[0].amount = (amountPaise / 100).toFixed(2);
    }
};

const addRenewalSplit = () => {
    renewalForm.splits.push({ method: 'Cash', amount: '', reference: '' });
};

const removeRenewalSplit = (index) => {
    if (renewalForm.splits.length === 1) return;
    renewalForm.splits.splice(index, 1);
};

const addPartSplit = () => {
    partPaymentForm.splits.push({ method: 'Cash', amount: '', reference: '' });
};

const removePartSplit = (index) => {
    if (partPaymentForm.splits.length === 1) return;
    partPaymentForm.splits.splice(index, 1);
};

const onPlanChange = () => {
    if (!selectedPlan.value) return;
    const tenant = selectedTenant.value;
    // If switching back to the same plan that has a balance, use the balance amount
    if (tenant && tenant._balance_paise > 0 && String(tenant._sub?.plan_id) === String(renewalForm.plan_id)) {
        renewalForm.splits[0].amount = (tenant._balance_paise / 100).toFixed(2);
    } else {
        renewalForm.splits[0].amount = (Number(selectedPlan.value.price_paise || 0) / 100).toFixed(2);
    }
};

const submitRenewal = () => {
    if (!canSubmitRenewal.value) {
        return;
    }

    renewalForm.post('/admin/invoices/renewals', {
        preserveScroll: true,
    });
};

const submitPartPayment = () => {
    partPaymentForm.post('/admin/invoices/part-payment', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Invoices & Payments" />

        <div class="flex flex-col gap-6">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">Finance</p>
                <h1 class="mt-2 text-3xl font-semibold">Invoices & Payments</h1>
                <p class="mt-1 text-slate-300">Process renewals, record part payments, and review collection history.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link
                    href="/admin/invoices?tab=renewal_due"
                    :class="['rounded-full border px-4 py-2 text-sm font-semibold transition', currentTab === 'renewal_due' ? 'border-orange-500 bg-orange-500 text-slate-950' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10']"
                >
                    Renewal Due
                    <span v-if="renewalsDue?.length" class="ml-2 rounded-full bg-black/20 px-1 py-0.5 text-xs">{{ renewalsDue.length }}</span>
                </Link>
                <Link
                    href="/admin/invoices?tab=history"
                    :class="['rounded-full border px-4 py-2 text-sm font-semibold transition', currentTab === 'history' ? 'border-orange-500 bg-orange-500 text-slate-950' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10']"
                >
                    Payment History
                </Link>
            </div>

            <div v-if="currentTab === 'renewal_due'" class="grid gap-6 xl:grid-cols-[1fr_1.4fr]">
                <form @submit.prevent="submitRenewal" class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.07em] text-slate-200">Process Renewal</p>
                        <p class="mb-4 text-sm text-slate-400">Choose a tenant, pick a plan, enter amount. Part payments allowed.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Tenant</label>
                            <div class="relative">
                                <input
                                    v-model="tenantSearch"
                                    type="text"
                                    placeholder="Type to search gym name..."
                                    class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 pr-11 text-sm text-slate-300 outline-none focus:border-orange-400"
                                    @focus="tenantDropdownOpen = true"
                                >
                                <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-sm text-slate-400">▾</span>
                                <div v-if="tenantDropdownOpen" class="absolute left-0 right-0 top-full z-20 mt-2 max-h-52 overflow-y-auto rounded-2xl border app-panel-strong shadow-xl">
                                    <button
                                        v-for="tenant in filteredTenants"
                                        :key="tenant.id"
                                        type="button"
                                        @mousedown.prevent="selectTenant(tenant)"
                                        class="flex w-full items-center justify-between px-4 py-3 text-left text-sm transition hover:bg-white/5"
                                        :class="String(selectedTenantId) === String(tenant.id) ? 'bg-orange-500/10' : ''"
                                    >
                                        <span class="font-medium">{{ tenant.gym_name }}</span>
                                        <span class="text-xs text-slate-400">{{ tenant.subdomain }}.gymos.in</span>
                                    </button>
                                    <div v-if="filteredTenants.length === 0" class="px-4 py-3 text-sm text-slate-400">
                                        No matching tenants found.
                                    </div>
                                </div>
                            </div>
                            <p v-if="renewalForm.errors.tenant_id" class="mt-1 text-xs text-red-400">{{ renewalForm.errors.tenant_id }}</p>
                        </div>

                        <div v-if="selectedTenantDue" class="rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300">
                            Expiry: {{ formatDate(selectedTenantDue._sub?.end_date || selectedTenantDue._sub?.trial_end_date) }} · Status: {{ selectedTenantDue._sub?.status || '—' }}
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Plan</label>
                            <select v-model="renewalForm.plan_id" @change="onPlanChange" :disabled="hasPendingBalance" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400 disabled:cursor-not-allowed disabled:opacity-50">
                                <option value="">Select plan...</option>
                                <option v-for="plan in plans" :key="plan.id" :value="String(plan.id)">
                                    {{ plan.name }} - {{ plan.billing_cycle }} - {{ formatCurrency(plan.price_paise) }}
                                </option>
                            </select>
                            <p v-if="hasPendingBalance" class="mt-1 text-xs text-amber-400">Plan cannot be changed while a balance is pending.</p>
                            <p v-if="renewalForm.errors.plan_id" class="mt-1 text-xs text-red-400">{{ renewalForm.errors.plan_id }}</p>
                        </div>

                        <div v-if="selectedPlan" class="rounded-2xl border border-white/10 bg-slate-950/50 p-4 text-sm">
                            <div class="flex justify-between text-slate-400">
                                <span>Plan price</span>
                                <span class="text-slate-200">{{ formatCurrency(selectedPlan.price_paise) }}</span>
                            </div>
                            <div v-if="effectiveDuePaise !== Number(selectedPlan.price_paise)" class="mt-2 flex justify-between text-amber-400">
                                <span>Remaining balance</span>
                                <span class="font-semibold">{{ formatCurrency(effectiveDuePaise) }}</span>
                            </div>
                            <div class="mt-2 flex justify-between text-slate-400">
                                <span>Paying now</span>
                                <span class="text-slate-200">{{ formatCurrency(renewalAmountPaise) }}</span>
                            </div>
                            <div class="mt-2 flex justify-between font-semibold">
                                <span class="text-slate-300">Balance due</span>
                                <span :class="renewalBalancePaise > 0 ? 'text-amber-400' : 'text-emerald-400'">{{ formatCurrency(renewalBalancePaise) }}</span>
                            </div>
                            <p class="mt-3 text-sm" :class="renewalStatusTone">
                                {{ renewalStatusMessage }}
                            </p>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label class="text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Payment</label>
                            </div>

                            <div class="space-y-2">
                                <div v-for="(split, index) in renewalForm.splits" :key="`renewal-${index}`" class="grid items-end gap-2 rounded-2xl border border-white/10 bg-slate-950/50 p-3 lg:grid-cols-[1.1fr_1fr_1.2fr_auto]">
                                    <div>
                                        <label class="mb-1 block text-xs text-slate-400">Method</label>
                                        <select v-model="split.method" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                            <option>Cash</option>
                                            <option>Bank transfer</option>
                                            <option>UPI</option>
                                            <option>Cheque</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs text-slate-400">Amount (₹)</label>
                                        <input v-model="split.amount" type="number" step="0.01" min="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs text-slate-400">Reference</label>
                                        <input v-model="split.reference" type="text" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                    </div>
                                    <button type="button" @click="removeRenewalSplit(index)" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 text-red-400 hover:bg-red-500/10">✕</button>
                                </div>
                            </div>
                            <button type="button" @click="addRenewalSplit" class="mt-3 w-full rounded-xl border border-dashed border-white/10 px-3 py-2.5 text-sm font-semibold text-slate-400 hover:border-orange-400 hover:text-orange-400">
                                + Add Payment Method
                            </button>
                            <p v-if="renewalForm.errors.splits" class="mt-1 text-xs text-red-400">{{ renewalForm.errors.splits }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Date</label>
                            <input v-model="renewalForm.paid_at" type="date" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Notes</label>
                            <input v-model="renewalForm.notes" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400" placeholder="Optional">
                        </div>

                        <button type="submit" class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400 disabled:cursor-not-allowed disabled:opacity-60" :disabled="renewalForm.processing || !canSubmitRenewal">
                            {{ renewalForm.processing ? 'Processing...' : 'Process Renewal' }}
                        </button>
                    </div>
                </form>

                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                    <div class="mb-4 flex items-center gap-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.07em] text-slate-200">Tenants Due for Renewal</p>
                        <span class="rounded-full bg-amber-500/15 px-2 py-0.5 text-xs font-semibold text-amber-300">{{ renewalsDue?.length || 0 }}</span>
                    </div>

                    <div v-if="!renewalsDue || renewalsDue.length === 0" class="py-8 text-center text-sm text-slate-400">
                        No renewals due. All subscriptions are current.
                    </div>

                    <div v-else class="flex flex-col gap-3">
                        <button
                            v-for="tenant in renewalsDue"
                            :key="tenant.id"
                            type="button"
                            @click="selectTenant(tenant)"
                            class="rounded-[1.2rem] border border-white/10 bg-slate-950/50 p-4 text-left transition hover:border-orange-400"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold">{{ tenant.gym_name }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ tenant._sub?.plan?.name || '—' }}</p>
                                </div>
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold uppercase" :class="getDueBadgeClass(getDaysLeft(tenant._sub?.end_date || tenant._sub?.trial_end_date))">
                                    {{ Math.abs(getDaysLeft(tenant._sub?.end_date || tenant._sub?.trial_end_date) || 0) }}d left
                                </span>
                            </div>
                            <div class="mt-2 flex justify-between text-xs text-slate-400">
                                <span>Expires: {{ formatDate(tenant._sub?.end_date || tenant._sub?.trial_end_date) }}</span>
                                <span v-if="tenant._balance_paise > 0" class="font-semibold text-amber-400">Balance: {{ formatCurrency(tenant._balance_paise) }}</span>
                                <span v-else>{{ formatCurrency(tenant._sub?.plan?.price_paise) }} / {{ tenant._sub?.plan?.billing_cycle }}</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="currentTab === 'history'" class="flex flex-col gap-6">
                <div v-if="partialSubscriptions.length" class="rounded-[2rem] border border-amber-500/30 bg-amber-500/5 p-6">
                    <p class="mb-4 text-xs font-semibold uppercase tracking-[0.07em] text-amber-400">Pending Part Payments</p>
                    <form @submit.prevent="submitPartPayment" class="grid gap-4">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Subscription</label>
                            <select v-model="partPaymentForm.subscription_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                                <option value="">Select subscription...</option>
                                <option v-for="subscription in partialSubscriptions" :key="subscription.id" :value="String(subscription.id)">
                                    {{ subscription.tenantName }} - {{ subscription.planName }} ({{ formatCurrency(subscription.duePaise) }} due)
                                </option>
                            </select>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label class="text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Payment</label>
                                <button type="button" @click="addPartSplit" class="rounded-xl border border-dashed border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-400 hover:border-orange-400 hover:text-orange-400">
                                    + Add Payment Method
                                </button>
                            </div>

                            <div class="space-y-2">
                                <div v-for="(split, index) in partPaymentForm.splits" :key="`part-${index}`" class="grid items-end gap-2 rounded-2xl border border-white/10 bg-slate-950/50 p-3 lg:grid-cols-[1.1fr_1fr_1.2fr_auto]">
                                    <div>
                                        <label class="mb-1 block text-xs text-slate-400">Method</label>
                                        <select v-model="split.method" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                            <option>Cash</option>
                                            <option>Bank transfer</option>
                                            <option>UPI</option>
                                            <option>Cheque</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs text-slate-400">Amount (₹)</label>
                                        <input v-model="split.amount" type="number" step="0.01" min="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs text-slate-400">Reference</label>
                                        <input v-model="split.reference" type="text" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400">
                                    </div>
                                    <button type="button" @click="removePartSplit(index)" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 text-red-400 hover:bg-red-500/10">✕</button>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Date</label>
                                <input v-model="partPaymentForm.paid_at" type="date" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Notes</label>
                                <input v-model="partPaymentForm.notes" type="text" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-300 outline-none focus:border-orange-400">
                            </div>
                        </div>

                        <button type="submit" class="rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400" :disabled="partPaymentForm.processing">
                            {{ partPaymentForm.processing ? 'Recording...' : 'Record Part Payment' }}
                        </button>
                    </form>
                </div>

                <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
                    <div class="border-b border-white/10 px-6 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.07em] text-slate-200">Payment History</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-white/10 text-left text-sm">
                            <thead class="bg-slate-950/60 text-slate-300">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Gym</th>
                                    <th class="px-4 py-3 font-medium">Plan</th>
                                    <th class="px-4 py-3 font-medium">Type</th>
                                    <th class="px-4 py-3 font-medium">Amount</th>
                                    <th class="px-4 py-3 font-medium">Method</th>
                                    <th class="px-4 py-3 font-medium">Reference</th>
                                    <th class="px-4 py-3 font-medium">Date</th>
                                    <th class="px-4 py-3 font-medium">By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-white/5">
                                <template v-if="payments?.data?.length">
                                    <template v-for="payment in payments.data" :key="payment.id">
                                        <tr>
                                            <td class="px-4 py-4 font-semibold">{{ payment.tenant?.gym_name }}</td>
                                            <td class="px-4 py-4 text-slate-400">{{ payment.subscription?.plan?.name || '—' }}</td>
                                            <td class="px-4 py-4">
                                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold uppercase" :class="getPaymentTypeClass(payment.payment_type)">
                                                    {{ payment.payment_type?.replace('_', ' ') }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 font-semibold">{{ formatCurrency(payment.amount_paise) }}</td>
                                            <td class="px-4 py-4 text-slate-400">{{ payment.payment_method }}</td>
                                            <td class="px-4 py-4 font-mono text-xs text-slate-400">{{ payment.transaction_ref || '—' }}</td>
                                            <td class="px-4 py-4 text-slate-400">{{ formatDate(payment.paid_at) }}</td>
                                            <td class="px-4 py-4 text-xs text-slate-400">{{ payment.admin?.name || 'System' }}</td>
                                        </tr>
                                        <tr v-if="payment.notes">
                                            <td colspan="8" class="px-4 py-2 text-xs text-slate-400">↳ {{ payment.notes }}</td>
                                        </tr>
                                    </template>
                                </template>
                                <tr v-else>
                                    <td colspan="8" class="px-4 py-8 text-center text-slate-400">No payments recorded yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="payments?.links?.length" class="flex items-center gap-2">
                    <Link
                        v-for="link in payments.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="['rounded-lg px-3 py-2 text-sm', link.active ? 'bg-orange-500 text-slate-950' : 'bg-white/5 text-slate-300 hover:bg-white/10']"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
