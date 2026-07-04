<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    stats: Object,
    walkins: Object,
    plans: Array,
    logs: Object,
    branches: {
        type: Array,
        default: () => [],
    },
    dayPassPlans: Array,
    todayTotal: Number,
    todayFollowupCount: Number,
    todayFollowup: Boolean,
    followupDate: String,
    canAddMembers: Boolean,
});

const search = ref('');
const purposeFilter = ref('');
const followupModal = ref(null);
const historyModal = ref(null);
const historyLoading = ref(false);
const historyItems = ref([]);

const walkinRows = computed(() => props.logs?.data || props.walkins?.data || props.walkins || []);
const dayPassPlans = computed(() => props.dayPassPlans || props.plans || []);
const todayWalkins = computed(() => props.todayTotal ?? props.stats?.today ?? 0);
const pendingEnquiries = computed(() => props.todayFollowupCount ?? props.stats?.enquiries ?? 0);

const filteredRows = computed(() => {
    const term = search.value.trim().toLowerCase();

    return walkinRows.value.filter((row) => {
        const matchesPurpose = !purposeFilter.value || row.purpose === purposeFilter.value;
        const matchesSearch = !term
            || String(row.name || '').toLowerCase().includes(term)
            || String(row.phone || '').toLowerCase().includes(term);

        return matchesPurpose && matchesSearch;
    });
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).replace(',', '').replaceAll('/', '-');
};

const formatMoney = (paise) => Number(paise || 0) > 0 ? `Rs. ${(Number(paise) / 100).toFixed(0)}` : '-';
const labelize = (value) => String(value || '').replace(/_/g, ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());

const todayIso = new Date().toISOString().slice(0, 10);
const nextFollowup = (walkin) => (walkin.followups || []).find((item) => item.next_followup_date)?.next_followup_date || '';
const nextFollowupLabel = (value) => {
    if (!value) return '';
    const date = String(value).slice(0, 10);
    if (date === todayIso) return 'Today';
    if (date < todayIso) return `Overdue ${new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-')}`;
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
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

const getStatusBadge = (status) => {
    const badges = {
        open: 'bg-amber-500/15 text-amber-300',
        followed_up: 'bg-sky-500/15 text-sky-300',
        converted: 'bg-emerald-500/15 text-emerald-300',
        closed: 'bg-slate-500/15 text-slate-300',
    };
    return badges[status] || badges.open;
};

const form = useForm({
    name: '',
    phone: '',
    purpose: '',
    plan_id: '',
    amount: '',
    payment_method: 'cash',
    reference: '',
    notes: '',
});

const followupForm = useForm({
    outcome: '',
    notes: '',
    next_followup_date: '',
});

const selectedPlan = computed(() => dayPassPlans.value.find((plan) => Number(plan.id) === Number(form.plan_id)) || null);
const selectedPlanTotalPaise = computed(() => Number(selectedPlan.value?.total_price_paise || selectedPlan.value?.price_paise || selectedPlan.value?.amount_paise || 0));
const selectedPlanAmount = computed(() => (selectedPlanTotalPaise.value / 100).toFixed(2));

watch(
    () => form.plan_id,
    () => {
        if (form.purpose === 'day_pass') {
            form.amount = selectedPlan.value ? selectedPlanAmount.value : '';
        }
    },
);

watch(
    () => form.purpose,
    (purpose) => {
        if (purpose !== 'day_pass') {
            form.plan_id = '';
            form.amount = '';
            form.payment_method = 'cash';
            form.reference = '';
        }
    },
);

const submit = () => {
    form
        .transform((data) => {
            const payload = {
                name: data.name,
                phone: String(data.phone || '').replace(/\D/g, ''),
                purpose: data.purpose,
                notes: data.notes,
            };

            if (data.purpose === 'day_pass') {
                const method = data.payment_method || 'cash';
                payload.plan_id = data.plan_id;
                payload.fee_paise = selectedPlanTotalPaise.value;
                payload.payment_methods = [method];
                payload.amounts = { [method]: data.amount };
                payload.references = { [method]: data.reference };
            }

            return payload;
        })
        .post('/walkins', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                form.purpose = '';
                form.payment_method = 'cash';
            },
            onFinish: () => form.transform((data) => data),
        });
};

const openFollowup = (walkin) => {
    followupModal.value = walkin;
    followupForm.reset();
    followupForm.clearErrors();
};

const closeFollowup = () => {
    followupModal.value = null;
    followupForm.reset();
    followupForm.clearErrors();
};

const saveFollowup = () => {
    if (!followupModal.value) return;

    followupForm.post(`/walkins/${followupModal.value.id}/followup`, {
        preserveScroll: true,
        onSuccess: closeFollowup,
    });
};

const openHistory = async (walkin) => {
    historyModal.value = walkin;
    historyItems.value = [];
    historyLoading.value = true;

    try {
        const response = await fetch(`/walkins/${walkin.id}/followup-history`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = response.ok ? await response.json() : { history: [] };
        historyItems.value = data.history || [];
    } finally {
        historyLoading.value = false;
    }
};

const closeHistory = () => {
    historyModal.value = null;
    historyItems.value = [];
};
</script>

<template>
    <AppLayout>
        <Head title="Walk-ins & Enquiries" />

        <div class="flex flex-col gap-4">
            <div>
                <h1 class="text-[22px] font-semibold leading-tight md:text-[26px]">Walk-ins & Enquiries</h1>
                <p class="mt-1 text-sm text-slate-300">Manage day passes, trials, visitor enquiries and follow-ups.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">Today's Walk-ins</p>
                    <p class="mt-1 text-xl font-bold leading-none">{{ todayWalkins }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">Follow-ups Due Today</p>
                    <p class="mt-1 text-xl font-bold leading-none text-amber-400">{{ pendingEnquiries }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <input v-model="search" type="text" placeholder="Search by name or phone..." class="min-w-[220px] flex-1 rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none focus:border-orange-400 sm:max-w-[320px]">
                <select v-model="purposeFilter" class="rounded-lg border border-white/10 bg-slate-950/50 px-3 py-2 text-sm text-slate-300 outline-none">
                    <option value="">All Purposes</option>
                    <option value="day_pass">Day Pass</option>
                    <option value="free_trial">Free Trial</option>
                    <option value="inquiry">Inquiry</option>
                    <option value="guest">Guest</option>
                </select>
                <Link
                    :href="props.todayFollowup ? '/walkins' : '/walkins?today_followup=1'"
                    class="rounded-lg border px-3 py-2 text-xs font-bold"
                    :class="props.todayFollowup ? 'border-orange-400/60 bg-orange-500/15 text-orange-300' : 'border-white/10 bg-slate-950/50 text-slate-300 hover:border-orange-400/50 hover:text-orange-300'"
                >
                    Today's Follow-ups
                    <span v-if="pendingEnquiries" class="ml-1 rounded-full bg-orange-500 px-1.5 py-0.5 text-[10px] text-slate-950">{{ pendingEnquiries }}</span>
                </Link>
                <Link v-if="props.todayFollowup || props.followupDate" href="/walkins" class="rounded-lg border border-white/10 px-3 py-2 text-xs font-bold text-slate-300 hover:border-orange-400/50 hover:text-orange-300">
                    Clear
                </Link>
            </div>

            <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                    <div v-if="filteredRows.length === 0" class="flex min-h-[260px] flex-col items-center justify-center gap-3 px-4 py-12 text-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full border border-white/10 bg-slate-950/50 text-xl font-bold text-slate-400">W</div>
                        <p class="text-sm font-semibold">No walk-ins or enquiries found</p>
                        <p class="text-sm text-slate-400">Add a new walk-in or adjust the filters.</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full min-w-[900px] divide-y divide-white/10 text-left text-sm">
                            <thead class="bg-slate-950/60 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">
                                <tr>
                                    <th class="px-4 py-3">Visitor</th>
                                    <th class="px-4 py-3">Purpose</th>
                                    <th class="px-4 py-3">Fee</th>
                                    <th class="px-4 py-3">Branch / Time</th>
                                    <th class="px-4 py-3">Follow-up</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-white/5">
                                <tr v-for="walkin in filteredRows" :key="walkin.id" class="hover:bg-white/5">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold">{{ walkin.name }}</p>
                                        <p class="text-xs text-slate-400">{{ walkin.phone }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-start gap-1">
                                            <span class="rounded-full px-2 py-0.5 text-xs font-bold capitalize" :class="getPurposeBadge(walkin.purpose)">
                                                {{ walkin.purpose?.replace('_', ' ') }}
                                            </span>
                                            <span v-if="walkin.purpose === 'inquiry'" class="rounded-full px-2 py-0.5 text-[11px] font-bold" :class="getStatusBadge(walkin.enquiry_status)">
                                                {{ labelize(walkin.enquiry_status || 'open') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-400">{{ formatMoney(walkin.fee_paise) }}</td>
                                    <td class="px-4 py-3 text-slate-400">
                                        <p>{{ walkin.branch?.name || '-' }}</p>
                                        <p class="text-xs">{{ formatDate(walkin.created_at) }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div v-if="walkin.purpose === 'inquiry'" class="flex flex-col items-start gap-1.5">
                                            <div v-if="!['converted', 'closed'].includes(walkin.enquiry_status)" class="flex flex-wrap gap-1.5">
                                                <button type="button" @click="openFollowup(walkin)" class="rounded-lg bg-orange-500/15 px-2.5 py-1 text-xs font-bold text-orange-300 hover:bg-orange-500/25">
                                                    + Follow Up
                                                </button>
                                                <Link v-if="props.canAddMembers" :href="`/members/create?walkin_id=${walkin.id}`" class="rounded-lg bg-emerald-500/15 px-2.5 py-1 text-xs font-bold text-emerald-300 hover:bg-emerald-500/25">
                                                    Convert to Member
                                                </Link>
                                            </div>
                                            <button type="button" @click="openHistory(walkin)" class="rounded-lg border border-white/10 px-2.5 py-1 text-xs font-bold text-slate-300 hover:border-orange-400/50 hover:text-orange-300">
                                                {{ walkin.followups?.length || 0 }} logs
                                            </button>
                                            <p v-if="nextFollowup(walkin)" class="text-[11px] font-semibold" :class="String(nextFollowup(walkin)).slice(0, 10) < todayIso ? 'text-red-300' : 'text-orange-300'">
                                                Next follow-up: {{ nextFollowupLabel(nextFollowup(walkin)) }}
                                            </p>
                                        </div>
                                        <span v-else class="text-slate-500">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5">
                    <div class="border-b border-white/10 px-4 py-3">
                        <h3 class="text-base font-bold">New Walk-in / Enquiry</h3>
                    </div>
                    <form @submit.prevent="submit" class="flex flex-col gap-3 p-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Name <span class="text-red-400">*</span></label>
                            <input v-model="form.name" type="text" :class="['w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-white outline-none focus:border-orange-400', form.errors.name ? 'field-invalid' : '']" required>
                            <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Phone <span class="text-red-400">*</span></label>
                            <input v-model="form.phone" type="tel" placeholder="10 digit mobile number" :class="['w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-white outline-none focus:border-orange-400', form.errors.phone ? 'field-invalid' : '']" required>
                            <p v-if="form.errors.phone" class="field-error">{{ form.errors.phone }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Purpose</label>
                            <select v-model="form.purpose" :class="['w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-white outline-none focus:border-orange-400', form.errors.purpose ? 'field-invalid' : '']">
                                <option value="">Select purpose</option>
                                <option value="day_pass">Day Pass</option>
                                <option value="free_trial">Free Trial</option>
                                <option value="inquiry">Inquiry</option>
                                <option value="guest">Guest</option>
                            </select>
                            <p v-if="form.errors.purpose" class="field-error">{{ form.errors.purpose }}</p>
                        </div>
                        <div v-if="form.purpose === 'day_pass'">
                            <label class="mb-1.5 block text-sm font-medium">1-Day Membership Plan</label>
                            <select v-model="form.plan_id" :class="['w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-white outline-none focus:border-orange-400', form.errors.plan_id ? 'field-invalid' : '']">
                                <option value="">Select a 1-day plan</option>
                                <option v-for="plan in dayPassPlans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
                            </select>
                            <p v-if="form.errors.plan_id" class="field-error">{{ form.errors.plan_id }}</p>
                        </div>
                        <div v-if="form.purpose === 'day_pass'">
                            <label class="mb-1.5 block text-sm font-medium">Amount</label>
                            <input v-model="form.amount" type="number" step="0.01" placeholder="Rs." :class="['w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-white outline-none focus:border-orange-400', form.errors.amounts || form.errors.fee_paise ? 'field-invalid' : '']">
                            <p v-if="selectedPlan" class="mt-1 text-xs text-slate-400">Plan amount: Rs. {{ selectedPlanAmount }}</p>
                            <p v-if="form.errors.amounts" class="field-error">{{ form.errors.amounts }}</p>
                            <p v-if="form.errors.fee_paise" class="field-error">{{ form.errors.fee_paise }}</p>
                        </div>
                        <div v-if="form.purpose === 'day_pass'">
                            <label class="mb-1.5 block text-sm font-medium">Payment Method</label>
                            <select v-model="form.payment_method" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-white outline-none focus:border-orange-400">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                        <div v-if="form.purpose === 'day_pass'">
                            <label class="mb-1.5 block text-sm font-medium">Reference</label>
                            <input v-model="form.reference" type="text" placeholder="Optional" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-white outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Notes</label>
                            <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-white outline-none focus:border-orange-400"></textarea>
                        </div>
                        <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400 disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Add Walk-in' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div v-if="followupModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4" @click.self="closeFollowup">
            <div class="w-full max-w-md rounded-2xl border border-white/10 bg-slate-900 shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-white/10 px-4 py-3">
                    <div>
                        <h3 class="text-base font-bold">Log Follow-Up</h3>
                        <p class="mt-0.5 text-xs text-slate-400">{{ followupModal.name }} - {{ followupModal.phone }}</p>
                    </div>
                    <button type="button" @click="closeFollowup" class="rounded-lg px-2 py-1 text-sm text-slate-400 hover:bg-white/10 hover:text-white">x</button>
                </div>
                <form @submit.prevent="saveFollowup" class="flex flex-col gap-3 p-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Outcome <span class="text-red-400">*</span></label>
                        <select v-model="followupForm.outcome" :class="['w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-white outline-none focus:border-orange-400', followupForm.errors.outcome ? 'field-invalid' : '']" required>
                            <option value="">Select outcome</option>
                            <option value="called">Called - spoke with them</option>
                            <option value="visited">Visited - they came in</option>
                            <option value="messaged">Messaged - WhatsApp / SMS</option>
                            <option value="no_answer">No answer</option>
                            <option value="not_interested">Not interested - close</option>
                            <option value="converted">Converted - joined as member</option>
                        </select>
                        <p v-if="followupForm.errors.outcome" class="field-error">{{ followupForm.errors.outcome }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Notes</label>
                        <textarea v-model="followupForm.notes" rows="3" placeholder="What was discussed?" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-white outline-none focus:border-orange-400"></textarea>
                        <p v-if="followupForm.errors.notes" class="field-error">{{ followupForm.errors.notes }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Next Follow-Up Date</label>
                        <input v-model="followupForm.next_followup_date" type="date" :min="todayIso" class="w-full rounded-lg border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-white outline-none focus:border-orange-400">
                        <p v-if="followupForm.errors.next_followup_date" class="field-error">{{ followupForm.errors.next_followup_date }}</p>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="closeFollowup" class="rounded-lg border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-white/5">Cancel</button>
                        <button type="submit" :disabled="followupForm.processing" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400 disabled:opacity-60">
                            {{ followupForm.processing ? 'Saving...' : 'Save Follow-Up' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="historyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4" @click.self="closeHistory">
            <div class="w-full max-w-lg rounded-2xl border border-white/10 bg-slate-900 shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-white/10 px-4 py-3">
                    <div>
                        <h3 class="text-base font-bold">Follow-Up History</h3>
                        <p class="mt-0.5 text-xs text-slate-400">{{ historyModal.name }} - {{ historyModal.phone }}</p>
                    </div>
                    <button type="button" @click="closeHistory" class="rounded-lg px-2 py-1 text-sm text-slate-400 hover:bg-white/10 hover:text-white">x</button>
                </div>
                <div class="max-h-[60vh] overflow-auto p-4">
                    <p v-if="historyLoading" class="py-8 text-center text-sm text-slate-400">Loading...</p>
                    <p v-else-if="historyItems.length === 0" class="py-8 text-center text-sm text-slate-400">No follow-ups logged yet.</p>
                    <div v-else class="divide-y divide-white/10">
                        <div v-for="item in historyItems" :key="item.id" class="py-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-bold">{{ labelize(item.outcome) }}</p>
                                <p class="text-xs text-slate-500">{{ item.created_at }}</p>
                            </div>
                            <p v-if="item.notes" class="mt-1 text-sm text-slate-300">{{ item.notes }}</p>
                            <p v-if="item.next_followup_date" class="mt-1 text-xs font-semibold text-orange-300">Next: {{ item.next_followup_date }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ item.logged_by }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

