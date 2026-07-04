<script setup>
import { computed } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    member: Object,
    prefill: Object,
    plans: {
        type: Array,
        default: () => [],
    },
    branches: {
        type: Array,
        default: () => [],
    },
    selectedBranchId: {
        type: [String, Number],
        default: null,
    },
});

const editing = !!props.member;
const pageTitle = editing ? 'Edit member' : 'Add new member';
const pageSub = editing ? `Update details for ${props.member?.name}.` : 'Register a new member at your gym.';
const today = new Date().toISOString().slice(0, 10);
const maxDob = (() => {
    const date = new Date();
    date.setFullYear(date.getFullYear() - 5);
    return date.toISOString().slice(0, 10);
})();

const normalizeDateInput = (value) => {
    if (!value) {
        return '';
    }

    return String(value).slice(0, 10);
};

const createSplit = () => ({
    method: 'cash',
    amount: '',
    reference: '',
});

const form = useForm({
    name: props.member?.name || props.prefill?.name || '',
    phone: props.member?.phone || props.prefill?.phone || '',
    email: props.member?.email || '',
    gender: props.member?.gender || '',
    dob: normalizeDateInput(props.member?.dob),
    address: props.member?.address || '',
    id_proof_type: props.member?.id_proof_type || '',
    id_proof_number: props.member?.id_proof_number || '',
    branch_id: props.member?.branch_id || props.prefill?.branch_id || props.selectedBranchId || '',
    plan_id: props.member?.plan_id || '',
    start_date: normalizeDateInput(props.member?.start_date) || today,
    notes: props.member?.notes || props.prefill?.notes || '',
    status: props.member?.status || 'active',
    freeze_days: props.member?.frozen_until ? Math.max(Math.ceil((new Date(props.member.frozen_until) - new Date()) / 86400000), 1) : '',
    splits: [createSplit()],
    is_partial: false,
    due_amount: '',
    due_date: '',
    walkin_id: props.prefill?.id || '',
});

const paymentMethods = [
    { value: 'cash', label: 'Cash' },
    { value: 'upi', label: 'UPI' },
    { value: 'card', label: 'Card' },
    { value: 'bank', label: 'Bank transfer' },
    { value: 'cheque', label: 'Cheque' },
];

const idProofTypes = [
    { value: 'aadhaar', label: 'Aadhaar' },
    { value: 'pan', label: 'PAN' },
    { value: 'passport', label: 'Passport' },
    { value: 'voter_id', label: 'Voter ID' },
    { value: 'dl', label: 'Driving Licence' },
];

const currency = (value) => `Rs. ${Number(value || 0).toFixed(2)}`;
const getPlanTotal = (plan) => {
    if (!plan) {
        return 0;
    }

    if (plan.total_price_paise != null) {
        return Number(plan.total_price_paise) / 100;
    }

    const basePrice = Number(plan.price_paise || 0);
    const gstRate = plan.gst_applicable ? Number(plan.gst_rate || 0) : 0;

    return Math.round(basePrice * (1 + gstRate / 100)) / 100;
};

const selectedPlan = computed(() => {
    return props.plans.find((plan) => String(plan.id) === String(form.plan_id)) || null;
});

const selectedBranch = computed(() => {
    return props.branches.find((branch) => String(branch.id) === String(form.branch_id)) || null;
});

const planTotal = computed(() => {
    return getPlanTotal(selectedPlan.value);
});

const paidAmount = computed(() => {
    return form.splits.reduce((sum, split) => sum + Number(split.amount || 0), 0);
});

const remainingAmount = computed(() => {
    return Math.max(planTotal.value - paidAmount.value, 0);
});

const freezePlanError = computed(() => {
    if (!editing || form.status !== 'frozen' || !selectedPlan.value || selectedPlan.value.allow_freeze) {
        return '';
    }

    return `The plan "${selectedPlan.value.name}" does not allow freeze.`;
});

const fieldError = (field) => form.errors?.[field] || '';
const splitError = (index, field) => form.errors?.[`splits.${index}.${field}`] || '';
const fieldClass = (field, base) => [base, fieldError(field) ? 'field-invalid' : ''];
const splitFieldClass = (index, field, base) => [base, splitError(index, field) ? 'field-invalid' : ''];

const expiryPreview = computed(() => {
    if (!selectedPlan.value || !form.start_date) {
        return editing && props.member?.expiry_date
            ? `Current: ${formatDate(props.member.expiry_date)}`
            : 'Select a plan and start date';
    }

    if (Number(selectedPlan.value.session_limit || 0) > 0) {
        const sessions = Number(selectedPlan.value.session_limit);
        return `${sessions} ${sessions === 1 ? 'session' : 'sessions'} included`;
    }

    const baseDate = new Date(form.start_date);
    if (Number.isNaN(baseDate.getTime())) {
        return 'Select a valid start date';
    }

    const durationType = selectedPlan.value.duration_type;
    const durationValue = Number(selectedPlan.value.duration_value || selectedPlan.value.duration_days || 0);

    if (durationType === 'months') {
        baseDate.setMonth(baseDate.getMonth() + durationValue);
    } else {
        baseDate.setDate(baseDate.getDate() + durationValue);
    }

    if (editing && form.status === 'frozen' && Number(form.freeze_days) > 0) {
        baseDate.setDate(baseDate.getDate() + Number(form.freeze_days));
    }

    return formatDate(baseDate);
});

function formatDate(value) {
    if (!value) {
        return '';
    }

    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleDateString('en-GB').replaceAll('/', '-');
}

function addSplit() {
    form.splits.push(createSplit());
}

function removeSplit(index) {
    if (form.splits.length === 1) {
        return;
    }

    form.splits.splice(index, 1);
}

function submit() {
    form.is_partial = Number(form.due_amount || 0) > 0;

    if (editing) {
        form.put(`/members/${props.member.id}`);
        return;
    }

    form.post('/members');
}
</script>

<template>
    <AppLayout>
        <Head :title="pageTitle" />

        <div class="flex flex-col gap-4 text-slate-900">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <h1 class="text-[28px] font-semibold leading-none tracking-tight text-slate-900">{{ pageTitle }}</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ pageSub }}</p>
                </div>
                <Link href="/members" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                    <span>â†</span>
                    <span>Back to members</span>
                </Link>
            </div>

            <div v-if="!editing && prefill" class="flex items-start gap-3 rounded-2xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-900">
                <span class="mt-0.5 text-sm">i</span>
                <p>
                    <strong>Walk-in inquiry found.</strong>
                    Details were pre-filled for {{ prefill.name }} ({{ prefill.phone }}). You can still update them before saving.
                </p>
            </div>

            <form @submit.prevent="submit" class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
                <div class="flex flex-col gap-5">
                    <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-semibold text-slate-900">Personal information</h2>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="mb-2 block text-[13px] font-semibold text-slate-700">Full name <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" placeholder="e.g. Priya Sharma" :class="fieldClass('name', 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')" required maxlength="100">
                                <p v-if="fieldError('name')" class="field-error field-error-light">{{ fieldError('name') }}</p>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-[13px] font-semibold text-slate-700">Phone <span class="text-red-500">*</span></label>
                                    <input v-model="form.phone" type="tel" placeholder="+91 98000 00000" :class="fieldClass('phone', 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')" required maxlength="20">
                                    <p v-if="fieldError('phone')" class="field-error field-error-light">{{ fieldError('phone') }}</p>
                                </div>
                                <div>
                                    <label class="mb-2 block text-[13px] font-semibold text-slate-700">Email</label>
                                    <input v-model="form.email" type="email" placeholder="Optional" :class="fieldClass('email', 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')" maxlength="255">
                                    <p v-if="fieldError('email')" class="field-error field-error-light">{{ fieldError('email') }}</p>
                                </div>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-[13px] font-semibold text-slate-700">Gender</label>
                                    <div class="flex flex-wrap gap-4 pt-1">
                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                            <input v-model="form.gender" type="radio" value="male" class="h-4 w-4 accent-orange-500">
                                            <span>Male</span>
                                        </label>
                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                            <input v-model="form.gender" type="radio" value="female" class="h-4 w-4 accent-orange-500">
                                            <span>Female</span>
                                        </label>
                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                            <input v-model="form.gender" type="radio" value="other" class="h-4 w-4 accent-orange-500">
                                            <span>Other</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-2 block text-[13px] font-semibold text-slate-700">Date of birth</label>
                                    <input v-model="form.dob" type="date" :max="maxDob" :class="fieldClass('dob', 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')">
                                    <p v-if="fieldError('dob')" class="field-error field-error-light">{{ fieldError('dob') }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-[13px] font-semibold text-slate-700">Address</label>
                                <textarea v-model="form.address" rows="3" maxlength="300" placeholder="Optional" :class="fieldClass('address', 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')"></textarea>
                                <p v-if="fieldError('address')" class="field-error field-error-light">{{ fieldError('address') }}</p>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-[13px] font-semibold text-slate-700">ID proof type</label>
                                    <select v-model="form.id_proof_type" :class="fieldClass('id_proof_type', 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')">
                                        <option value="">Select...</option>
                                        <option v-for="type in idProofTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                                    </select>
                                    <p v-if="fieldError('id_proof_type')" class="field-error field-error-light">{{ fieldError('id_proof_type') }}</p>
                                </div>
                                <div>
                                    <label class="mb-2 block text-[13px] font-semibold text-slate-700">ID number</label>
                                    <input v-model="form.id_proof_number" type="text" maxlength="50" placeholder="Optional" :class="fieldClass('id_proof_number', 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')">
                                    <p v-if="fieldError('id_proof_number')" class="field-error field-error-light">{{ fieldError('id_proof_number') }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-semibold text-slate-900">Membership</h2>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="mb-2 block text-[13px] font-semibold text-slate-700">Branch <span class="text-red-500">*</span></label>
                                <select v-model="form.branch_id" :class="fieldClass('branch_id', 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')" required>
                                    <option value="">Select branch...</option>
                                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                        {{ branch.name }}{{ branch.is_primary ? ' (Primary)' : '' }}
                                    </option>
                                </select>
                                <p v-if="fieldError('branch_id')" class="field-error field-error-light">{{ fieldError('branch_id') }}</p>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-[13px] font-semibold text-slate-700">Plan <span class="text-red-500">*</span></label>
                                    <select v-model="form.plan_id" :class="fieldClass('plan_id', 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')" required>
                                        <option value="">Select plan...</option>
                                        <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                            {{ plan.name }} - {{ currency(getPlanTotal(plan)) }}
                                        </option>
                                    </select>
                                    <p v-if="fieldError('plan_id')" class="field-error field-error-light">{{ fieldError('plan_id') }}</p>
                                </div>
                                <div>
                                    <label class="mb-2 block text-[13px] font-semibold text-slate-700">Start date <span class="text-red-500">*</span></label>
                                    <input v-model="form.start_date" type="date" :class="fieldClass('start_date', 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')" required>
                                    <p v-if="fieldError('start_date')" class="field-error field-error-light">{{ fieldError('start_date') }}</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-orange-200 bg-orange-50/70 px-4 py-3">
                                <p class="text-[13px] font-semibold text-slate-700">Calculated expiry</p>
                                <p class="mt-1 text-sm text-slate-900">{{ expiryPreview }}</p>
                            </div>

                            <div v-if="editing" class="space-y-5">
                                <div>
                                    <label class="mb-2 block text-[13px] font-semibold text-slate-700">Status</label>
                                    <div class="flex flex-wrap gap-4 pt-1">
                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                            <input v-model="form.status" type="radio" value="active" class="h-4 w-4 accent-orange-500">
                                            <span>Active</span>
                                        </label>
                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                            <input v-model="form.status" type="radio" value="inactive" class="h-4 w-4 accent-orange-500">
                                            <span>Inactive</span>
                                        </label>
                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                            <input v-model="form.status" type="radio" value="frozen" class="h-4 w-4 accent-orange-500">
                                            <span>Frozen</span>
                                        </label>
                                    </div>
                                </div>

                                <div v-if="form.status === 'frozen'" class="grid gap-4">
                                    <div v-if="freezePlanError" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                        {{ freezePlanError }}
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-[13px] font-semibold text-slate-700">Freeze days</label>
                                        <input v-model="form.freeze_days" type="number" min="1" max="3650" placeholder="30" :class="fieldClass('freeze_days', 'w-full max-w-[180px] rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')">
                                        <p v-if="fieldError('freeze_days')" class="field-error field-error-light">{{ fieldError('freeze_days') }}</p>
                                        <p v-if="selectedPlan?.max_freeze_days" class="mt-2 text-xs text-slate-500">
                                            Max allowed by plan: {{ selectedPlan.max_freeze_days }} days
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="flex flex-col gap-5">
                    <section v-if="!editing" class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-xl font-semibold text-slate-900">Payment</h2>

                        <div class="mt-4 space-y-4">
                            <div v-for="(split, index) in form.splits" :key="index" class="rounded-2xl border border-slate-200 p-3">
                                <div class="grid gap-3">
                                    <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_130px]">
                                        <select v-model="split.method" :class="splitFieldClass(index, 'method', 'rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')">
                                            <option v-for="method in paymentMethods" :key="method.value" :value="method.value">{{ method.label }}</option>
                                        </select>
                                        <input v-model="split.amount" type="number" min="0" step="0.01" placeholder="0.00" :class="splitFieldClass(index, 'amount', 'rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')">
                                    </div>
                                    <p v-if="splitError(index, 'method')" class="field-error field-error-light">{{ splitError(index, 'method') }}</p>
                                    <p v-if="splitError(index, 'amount')" class="field-error field-error-light">{{ splitError(index, 'amount') }}</p>
                                    <div class="flex gap-3">
                                        <input v-model="split.reference" type="text" maxlength="100" placeholder="Reference (optional)" :class="splitFieldClass(index, 'reference', 'min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')">
                                        <button v-if="form.splits.length > 1" type="button" @click="removeSplit(index)" class="rounded-2xl border border-slate-200 px-4 py-3 text-xs font-semibold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600">
                                            Remove
                                        </button>
                                    </div>
                                    <p v-if="splitError(index, 'reference')" class="field-error field-error-light">{{ splitError(index, 'reference') }}</p>
                                </div>
                            </div>

                            <button type="button" @click="addSplit" class="rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                                + Add method
                            </button>

                            <div class="grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-700">
                                <div class="flex items-center justify-between">
                                    <span>Plan total</span>
                                    <span class="font-semibold text-slate-900">{{ currency(planTotal) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Collected now</span>
                                    <span class="font-semibold text-slate-900">{{ currency(paidAmount) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Balance due</span>
                                    <span class="font-semibold text-slate-900">{{ currency(remainingAmount) }}</span>
                                </div>
                            </div>

                            <div class="grid gap-4">
                                <div>
                                    <label class="mb-2 block text-[13px] font-semibold text-slate-700">Due amount</label>
                                    <input v-model="form.due_amount" type="number" min="0" step="0.01" placeholder="0.00" :class="fieldClass('due_amount', 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')">
                                    <p v-if="fieldError('due_amount')" class="field-error field-error-light">{{ fieldError('due_amount') }}</p>
                                </div>
                                <div>
                                    <label class="mb-2 block text-[13px] font-semibold text-slate-700">Due date</label>
                                    <input v-model="form.due_date" type="date" :class="fieldClass('due_date', 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')">
                                    <p v-if="fieldError('due_date')" class="field-error field-error-light">{{ fieldError('due_date') }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-xl font-semibold text-slate-900">Notes</h2>
                        <div class="mt-4">
                            <label class="mb-2 block text-[13px] font-semibold text-slate-700">Internal notes</label>
                            <textarea v-model="form.notes" rows="5" maxlength="500" placeholder="Any internal notes about this member..." :class="fieldClass('notes', 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-orange-400')"></textarea>
                            <p v-if="fieldError('notes')" class="field-error field-error-light">{{ fieldError('notes') }}</p>
                        </div>
                    </section>

                    <section v-if="editing" class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-xl font-semibold text-slate-900">Member info</h2>

                        <div class="mt-4 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4">
                                <span>Member code</span>
                                <span class="font-semibold text-slate-900">{{ member?.member_code || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Current branch</span>
                                <span class="text-right font-semibold text-slate-900">{{ selectedBranch?.name || 'Not assigned' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Current expiry</span>
                                <span class="font-semibold text-slate-900">{{ member?.expiry_date ? formatDate(member.expiry_date) : '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Balance due</span>
                                <span class="font-semibold text-slate-900">{{ currency((member?.balance_paise || 0) / 100) }}</span>
                            </div>
                        </div>
                    </section>

                    <div class="flex items-center justify-end gap-3 pt-1">
                        <Link href="/members" class="rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                            Cancel
                        </Link>
                        <button type="submit" class="rounded-2xl bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500 disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing || !!freezePlanError">
                            {{ editing ? 'Update member' : 'Add member' }}
                        </button>
                    </div>
                </aside>
            </form>
        </div>
    </AppLayout>
</template>

