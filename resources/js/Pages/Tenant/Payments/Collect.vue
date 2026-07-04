<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    member: Object,
    preselectedMember: Object,
    plans: {
        type: Array,
        default: () => [],
    },
    branches: {
        type: Array,
        default: () => [],
    },
    selectedBranchId: [Number, String],
    prefillAmount: Number,
});

const selectedMember = ref(props.preselectedMember || props.member || null);
const search = ref(selectedMember.value ? `${selectedMember.value.name} ${selectedMember.value.phone || ''}`.trim() : '');
const searchResults = ref([]);
const isSearching = ref(false);
const splits = ref([{ amount: '', method: 'cash', reference: '' }]);

const form = useForm({
    member_id: selectedMember.value?.id || '',
    branch_id: selectedMember.value?.branch_id || props.selectedBranchId || '',
    plan_id: '',
    amount: props.prefillAmount || '',
    payment_date: new Date().toISOString().split('T')[0],
    notes: '',
    splits: splits.value,
    is_partial: false,
    due_date: '',
});

const money = (paise) => `Rs. ${(Number(paise || 0) / 100).toFixed(0)}`;
const rupees = (paise) => (Number(paise || 0) / 100).toFixed(2);

const selectedPlan = computed(() => props.plans.find((plan) => Number(plan.id) === Number(form.plan_id)) || null);
const pendingDuePaise = computed(() => Number(selectedMember.value?.pending_due_paise || Math.max(0, -(selectedMember.value?.balance_paise || 0))));
const hasPendingDue = computed(() => pendingDuePaise.value > 0);
const canChoosePlan = computed(() => !hasPendingDue.value);
const planTotalPaise = computed(() => {
    if (!selectedPlan.value) return 0;
    return Number(selectedPlan.value.total_price_paise || selectedPlan.value.price_paise || selectedPlan.value.amount_paise || 0);
});
const collectionTargetPaise = computed(() => hasPendingDue.value ? pendingDuePaise.value : planTotalPaise.value);
const splitTotal = computed(() => splits.value.reduce((total, split) => total + Number(split.amount || 0), 0));
const splitTotalPaise = computed(() => Math.round(splitTotal.value * 100));
const balanceToCollectPaise = computed(() => Math.max(0, collectionTargetPaise.value - splitTotalPaise.value));
const overLimit = computed(() => collectionTargetPaise.value > 0 && splitTotalPaise.value > collectionTargetPaise.value);
const currentPlanName = computed(() => selectedMember.value?.plan_name || selectedPlan.value?.name || 'No active plan');
const needsDueDate = computed(() => splitTotalPaise.value > 0 && balanceToCollectPaise.value > 0);

const isTrialPlanForMember = (member) => {
    if (!member?.plan_id) return false;

    const plan = props.plans.find((item) => Number(item.id) === Number(member.plan_id));
    const planName = String(plan?.name || member.plan_name || '').toLowerCase();
    const planTotal = Number(plan?.total_price_paise || plan?.price_paise || plan?.amount_paise || 0);

    return planName.includes('trial') || planTotal === 0;
};

const syncAmountFromTarget = () => {
    const amount = collectionTargetPaise.value ? rupees(collectionTargetPaise.value) : '';
    form.amount = amount;
    if (splits.value.length === 1) {
        splits.value[0].amount = amount;
    }
};

const selectMember = (member) => {
    selectedMember.value = member;
    search.value = `${member.name} ${member.phone || ''}`.trim();
    searchResults.value = [];
    form.member_id = member.id;
    form.branch_id = member.branch_id || props.selectedBranchId || '';

    if (Number(member.pending_due_paise || 0) > 0 || isTrialPlanForMember(member)) {
        form.plan_id = '';
    } else {
        form.plan_id = member.plan_id || '';
    }

    syncAmountFromTarget();
};

const searchMembers = async () => {
    const term = search.value.trim();
    if (term.length < 2) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;
    try {
        const response = await fetch(`/payments/member-search?q=${encodeURIComponent(term)}`, {
            headers: { Accept: 'application/json' },
        });
        searchResults.value = response.ok ? await response.json() : [];
    } finally {
        isSearching.value = false;
    }
};

const addSplit = () => {
    splits.value.push({ amount: '', method: 'cash', reference: '' });
};

const removeSplit = (index) => {
    splits.value.splice(index, 1);
    if (splits.value.length === 0) {
        addSplit();
    }
};

const submit = () => {
    form.splits = splits.value;
    form.amount = splitTotal.value.toFixed(2);
    form.is_partial = needsDueDate.value;
    form.post('/payments/collect');
};

watch(() => form.plan_id, syncAmountFromTarget);

if (selectedMember.value) {
    if (hasPendingDue.value || isTrialPlanForMember(selectedMember.value)) {
        form.plan_id = '';
    } else {
        form.plan_id = selectedMember.value.plan_id || '';
    }
    syncAmountFromTarget();
}
</script>

<template>
    <AppLayout>
        <Head title="Collect Fee" />

        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold md:text-2xl">Collect Fee</h1>
                    <p class="mt-0.5 text-xs text-slate-400 md:text-sm">Record member payments and pending dues.</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/payments/history?tab=history" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5">
                        History
                    </Link>
                    <Link href="/payments/history?tab=dues" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-white/5">
                        Dues
                    </Link>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">
                <div class="flex flex-col gap-4">
                    <section class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_260px]">
                            <div class="relative">
                                <label class="mb-1 block text-xs font-semibold text-slate-400">Member</label>
                                <input
                                    v-model="search"
                                    @input="searchMembers"
                                    type="text"
                                    placeholder="Search by name, phone or code"
                                    class="w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-200 outline-none focus:border-orange-400"
                                    :class="{ 'border-red-400 focus:border-red-400': form.errors.member_id }"
                                >
                                <div v-if="searchResults.length" class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-lg border app-panel-strong shadow-xl">
                                    <button
                                        v-for="result in searchResults"
                                        :key="result.id"
                                        type="button"
                                        @click="selectMember(result)"
                                        class="flex w-full items-center justify-between gap-3 border-b border-white/5 px-3 py-2 text-left text-sm hover:bg-white/5"
                                    >
                                        <span>
                                            <span class="block font-semibold">{{ result.name }}</span>
                                            <span class="block text-xs text-slate-400">{{ result.phone || result.member_code }}</span>
                                        </span>
                                        <span v-if="result.pending_due_paise > 0" class="rounded-full bg-red-500/15 px-2 py-0.5 text-xs font-semibold text-red-300">
                                            Due {{ money(result.pending_due_paise) }}
                                        </span>
                                    </button>
                                </div>
                                <p v-if="isSearching" class="mt-1 text-xs text-slate-500">Searching...</p>
                                <p v-if="form.errors.member_id" class="mt-1 text-xs font-semibold text-red-400">{{ form.errors.member_id }}</p>
                            </div>

                            <div v-if="selectedMember" class="rounded-lg border border-white/10 bg-slate-950/50 p-3">
                                <p class="text-sm font-semibold text-slate-100">{{ selectedMember.name }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ selectedMember.phone || selectedMember.member_code }}</p>
                                <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                    <span class="rounded-full bg-slate-800 px-2 py-0.5 text-slate-300">{{ currentPlanName }}</span>
                                    <span :class="hasPendingDue ? 'bg-red-500/15 text-red-300' : 'bg-emerald-500/15 text-emerald-300'" class="rounded-full px-2 py-0.5 font-semibold">
                                        {{ hasPendingDue ? `Due ${money(pendingDuePaise)}` : 'No dues' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h2 class="text-sm font-semibold">Payment Details</h2>
                            <p v-if="hasPendingDue" class="rounded-full bg-red-500/15 px-2 py-1 text-xs font-semibold text-red-300">
                                Clear pending due before choosing another plan
                            </p>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-400">Plan</label>
                                <select
                                    v-model="form.plan_id"
                                    :disabled="!canChoosePlan"
                                    class="w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-200 outline-none focus:border-orange-400 disabled:cursor-not-allowed disabled:opacity-60"
                                    :class="{ 'border-red-400 focus:border-red-400': form.errors.plan_id }"
                                >
                                    <option value="">{{ hasPendingDue ? 'Pending due collection' : 'Select a plan' }}</option>
                                    <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                        {{ plan.name }} ({{ money(plan.total_price_paise || plan.price_paise || plan.amount_paise) }})
                                    </option>
                                </select>
                                <p v-if="form.errors.plan_id" class="mt-1 text-xs font-semibold text-red-400">{{ form.errors.plan_id }}</p>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-400">Payment Date</label>
                                <input v-model="form.payment_date" type="date" class="w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-200 outline-none focus:border-orange-400">
                                <p v-if="form.errors.payment_date" class="mt-1 text-xs font-semibold text-red-400">{{ form.errors.payment_date }}</p>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-400">Branch</label>
                                <select
                                    v-model="form.branch_id"
                                    class="w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-200 outline-none focus:border-orange-400"
                                    :class="{ 'border-red-400 focus:border-red-400': form.errors.branch_id }"
                                >
                                    <option value="">Select branch</option>
                                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                                </select>
                                <p v-if="form.errors.branch_id" class="mt-1 text-xs font-semibold text-red-400">{{ form.errors.branch_id }}</p>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-400">Notes</label>
                                <input v-model="form.notes" type="text" placeholder="Optional note" class="w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-200 outline-none focus:border-orange-400">
                            </div>

                        </div>
                    </section>

                    <section class="rounded-xl border border-orange-400/25 bg-orange-500/5 p-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h2 class="text-sm font-semibold text-orange-300">Split Collection</h2>
                            <button type="button" @click="addSplit" class="rounded-lg border border-dashed border-orange-400/50 px-3 py-1.5 text-xs font-semibold text-orange-300 hover:bg-orange-500/10">
                                + Add Split
                            </button>
                        </div>

                        <div class="flex flex-col gap-2">
                            <div v-for="(split, index) in splits" :key="index" class="grid gap-2 md:grid-cols-[1fr_1fr_1.2fr_auto] md:items-end">
                                <div>
                                    <label class="mb-1 block text-xs text-slate-400">Amount</label>
                                    <input
                                        v-model="split.amount"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-200 outline-none focus:border-orange-400"
                                        :class="{ 'border-red-400 focus:border-red-400': form.errors.amount || overLimit }"
                                    >
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-400">Method</label>
                                    <select v-model="split.method" class="w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-200 outline-none focus:border-orange-400">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="upi">UPI</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-slate-400">Reference</label>
                                    <input v-model="split.reference" type="text" placeholder="Transaction ID" class="w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-200 outline-none focus:border-orange-400">
                                </div>
                                <button v-if="splits.length > 1" type="button" @click="removeSplit(index)" class="h-9 rounded-lg border border-white/10 px-3 text-sm font-semibold text-slate-400 hover:border-red-400/50 hover:text-red-300">
                                    x
                                </button>
                            </div>
                        </div>
                        <p v-if="form.errors.amount" class="mt-2 text-xs font-semibold text-red-400">{{ form.errors.amount }}</p>
                        <p v-if="overLimit" class="mt-2 text-xs font-semibold text-red-400">Collected amount cannot be more than {{ money(collectionTargetPaise) }}.</p>
                    </section>
                </div>

                <aside class="rounded-xl border border-white/10 bg-white/5 p-4 xl:sticky xl:top-20 xl:self-start">
                    <h2 class="text-sm font-semibold">Summary</h2>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-400">Plan amount</span>
                            <span class="font-semibold">{{ money(planTotalPaise) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-400">Pending amount</span>
                            <span class="font-semibold" :class="hasPendingDue ? 'text-red-300' : 'text-slate-100'">{{ money(pendingDuePaise) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-400">To collect</span>
                            <span class="font-semibold text-orange-300">{{ money(collectionTargetPaise) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-400">Collected now</span>
                            <span class="font-semibold">{{ money(splitTotalPaise) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-white/10 pt-2">
                            <span class="text-slate-400">Balance left</span>
                            <span class="font-semibold">{{ money(balanceToCollectPaise) }}</span>
                        </div>
                        <div v-if="needsDueDate" class="rounded-lg border border-red-400/25 bg-red-500/10 p-3">
                            <label class="mb-1 block text-xs font-semibold text-red-400">Due Date</label>
                            <input
                                v-model="form.due_date"
                                type="date"
                                :min="form.payment_date || new Date().toISOString().split('T')[0]"
                                class="w-full rounded-lg border border-red-400 bg-slate-950/60 px-3 py-2 text-sm text-slate-200 outline-none focus:border-red-400"
                                :class="{ 'field-invalid': form.errors.due_date }"
                            >
                            <p class="mt-1 text-xs font-semibold text-red-400">Required for {{ money(balanceToCollectPaise) }} balance.</p>
                            <p v-if="form.errors.due_date" class="mt-1 text-xs font-semibold text-red-400">{{ form.errors.due_date }}</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing || !form.member_id || !collectionTargetPaise || overLimit || (needsDueDate && !form.due_date)"
                        class="mt-4 w-full rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ form.processing ? 'Recording...' : 'Record Payment' }}
                    </button>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

