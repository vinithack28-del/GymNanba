<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import TenantStepNav from '../../../Components/Admin/TenantStepNav.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
});

const currentStep = ref(1);
const steps = [
    { id: 1, title: 'Business Info', desc: 'Gym name, type, location, and GST' },
    { id: 2, title: 'Owner Account', desc: 'Primary and additional owners' },
    { id: 3, title: 'Plan & Routing', desc: 'Choose plan and technical setup' },
    { id: 4, title: 'Review', desc: 'Review and confirm tenant details' },
];

const createOwner = () => ({
    name: '',
    email: '',
    phone: '',
});

const createPaymentSplit = () => ({
    method: 'Cash',
    amount: '',
    reference: '',
});

const form = useForm({
    gym_name: '',
    business_type: '',
    city: '',
    state: '',
    address: '',
    gst_number: '',
    phone: '',
    owners: [createOwner()],
    subdomain: '',
    domain_mode: 'shared',
    custom_domain: '',
    database_mode: 'shared',
    plan_id: '',
    trial_end_date: '',
    notes: '',
    payment_splits: [createPaymentSplit()],
    payment_paid_at: new Date().toISOString().slice(0, 10),
    payment_notes: '',
});

const primaryOwner = computed(() => form.owners[0] || createOwner());
const selectedPlan = computed(() => props.plans.find((plan) => String(plan.id) === String(form.plan_id)) || null);
const selectedPlanIsTrial = computed(() => Boolean(selectedPlan.value?.is_trial));
const selectedPlanPricePaise = computed(() => Number(selectedPlan.value?.price_paise || 0));
const selectedPlanTrialDays = computed(() => Number(selectedPlan.value?.trial_days || 0));
const paymentTotalPaise = computed(() => {
    return form.payment_splits.reduce((sum, split) => {
        const amount = Number.parseFloat(split.amount || 0);

        if (!Number.isFinite(amount) || amount <= 0) {
            return sum;
        }

        return sum + Math.round(amount * 100);
    }, 0);
});
const paymentBalancePaise = computed(() => Math.max(selectedPlanPricePaise.value - paymentTotalPaise.value, 0));
const paymentExcessPaise = computed(() => Math.max(paymentTotalPaise.value - selectedPlanPricePaise.value, 0));
const shouldShowPaymentSection = computed(() => Boolean(selectedPlan.value) && !selectedPlanIsTrial.value && selectedPlanPricePaise.value > 0);
const paymentStatus = computed(() => {
    if (!shouldShowPaymentSection.value) {
        return null;
    }

    if (paymentExcessPaise.value > 0) {
        return {
            tone: 'border-red-400/20 bg-red-500/10 text-red-200',
            message: `Excess amount entered. Reduce by ${formatPlanPrice(paymentExcessPaise.value)}.`,
        };
    }

    if (paymentTotalPaise.value === 0) {
        return null;
    }

    if (paymentBalancePaise.value > 0) {
        return {
            tone: 'border-sky-400/20 bg-sky-500/10 text-sky-200',
            message: `Part payment. Balance due ${formatPlanPrice(paymentBalancePaise.value)}.`,
        };
    }

    return {
        tone: 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200',
        message: 'Full payment entered.',
    };
});
const reviewOwners = computed(() => form.owners.filter((owner) => owner.name || owner.email || owner.phone));
const reviewPaymentSplits = computed(() => {
    if (!shouldShowPaymentSection.value) {
        return [];
    }

    return form.payment_splits.filter((split) => {
        const amount = Number.parseFloat(split.amount || 0);
        return split.method || split.reference || (Number.isFinite(amount) && amount > 0);
    });
});
const stepErrors = ref({
    1: [],
    2: [],
    3: [],
    4: [],
});
const clientErrors = ref({});

const fieldError = (field) => form.errors?.[field] || clientErrors.value[field] || '';
const ownerError = (index, field) => form.errors?.[`owners.${index}.${field}`] || clientErrors.value[`owners.${index}.${field}`] || '';
const paymentSplitError = (index, field) => form.errors?.[`payment_splits.${index}.${field}`] || clientErrors.value[`payment_splits.${index}.${field}`] || '';
const fieldClass = (field, base) => [base, fieldError(field) ? 'field-invalid' : ''];
const ownerFieldClass = (index, field, base) => [base, ownerError(index, field) ? 'field-invalid' : ''];
const paymentSplitFieldClass = (index, field, base) => [base, paymentSplitError(index, field) ? 'field-invalid' : ''];

const nextStep = () => {
    if (!validateStep(currentStep.value)) {
        return;
    }

    if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const goToStep = (step) => {
    if (step > currentStep.value && !validateStep(currentStep.value)) {
        return;
    }

    currentStep.value = step;
};

const addOwner = () => {
    form.owners.push(createOwner());
};

const removeOwner = (index) => {
    if (index === 0) return;
    form.owners.splice(index, 1);
};

const addPaymentSplit = () => {
    form.payment_splits.push(createPaymentSplit());
};

const removePaymentSplit = (index) => {
    if (form.payment_splits.length === 1) {
        form.payment_splits[0] = createPaymentSplit();
        return;
    }

    form.payment_splits.splice(index, 1);
};

const ownerPassword = (owner) => {
    const email = String(owner?.email || '').trim().toLowerCase();
    const phone = String(owner?.phone || '').replace(/\D/g, '');

    if (!email || phone.length < 4) {
        return '';
    }

    const emailPart = email.split('@')[0].slice(0, 4);
    const phonePart = phone.slice(-4);

    if (!emailPart || !phonePart) {
        return '';
    }

    return `${emailPart}@${phonePart}`;
};

const copyOwnerPassword = async (owner) => {
    const password = ownerPassword(owner);

    if (!password || !navigator?.clipboard) {
        return;
    }

    await navigator.clipboard.writeText(password);
};

const formatPlanPrice = (pricePaise) => {
    const amount = Number(pricePaise || 0) / 100;

    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0,
    }).format(amount);
};

const isEmail = (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim());
const hasMinLength = (value, length) => String(value || '').trim().length >= length;
const hasDigitsLength = (value, length) => String(value || '').replace(/\D/g, '').length >= length;

const clearStepErrors = (step) => {
    stepErrors.value[step] = [];
};

const validateStep = (step) => {
    const errors = [];
    const nextClientErrors = {};
    const stepFieldPrefixes = {
        1: ['gym_name', 'business_type', 'city', 'state', 'address', 'phone'],
        2: ['owners'],
        3: ['subdomain', 'domain_mode', 'custom_domain', 'database_mode', 'plan_id', 'trial_end_date', 'payment_splits'],
    };

    if (step === 1) {
        if (!hasMinLength(form.gym_name, 2)) nextClientErrors.gym_name = 'Gym Name is required.';
        if (!form.business_type) nextClientErrors.business_type = 'Business Type is required.';
        if (!form.city.trim()) nextClientErrors.city = 'City is required.';
        if (!form.state.trim()) nextClientErrors.state = 'State is required.';
        if (!hasMinLength(form.address, 10)) nextClientErrors.address = 'Address must be at least 10 characters.';
        if (!hasDigitsLength(form.phone, 10)) nextClientErrors.phone = 'Business Phone must be at least 10 digits.';
    }

    if (step === 2) {
        if (!form.owners.length) {
            nextClientErrors.owners = 'At least one owner is required.';
        }

        form.owners.forEach((owner, index) => {
            const label = index === 0 ? 'Primary Owner' : `Additional Owner ${index}`;
            if (!hasMinLength(owner.name, 2)) nextClientErrors[`owners.${index}.name`] = `${label} name must be at least 2 characters.`;
            if (!isEmail(owner.email)) nextClientErrors[`owners.${index}.email`] = `${label} email must be valid.`;
            if (!hasDigitsLength(owner.phone, 10)) nextClientErrors[`owners.${index}.phone`] = `${label} phone must be at least 10 digits.`;
        });
    }

    if (step === 3) {
        if (!hasMinLength(form.subdomain, 3)) nextClientErrors.subdomain = 'Subdomain must be at least 3 characters.';
        if (!form.domain_mode) nextClientErrors.domain_mode = 'Domain Mode is required.';
        if (form.domain_mode === 'separate' && !form.custom_domain.trim()) nextClientErrors.custom_domain = 'Custom Domain is required for separate domain mode.';
        if (!form.database_mode) nextClientErrors.database_mode = 'Database Mode is required.';
        if (!form.plan_id) nextClientErrors.plan_id = 'Plan is required.';
        if (selectedPlanIsTrial.value && !form.trial_end_date) nextClientErrors.trial_end_date = 'Trial End Date is required for trial plans.';
        if (paymentExcessPaise.value > 0) nextClientErrors.payment_splits = `Payment exceeds plan amount by ${formatPlanPrice(paymentExcessPaise.value)}.`;
    }

    errors.push(...Object.values(nextClientErrors));
    const remainingErrors = { ...clientErrors.value };
    (stepFieldPrefixes[step] || []).forEach((prefix) => {
        Object.keys(remainingErrors).forEach((key) => {
            if (key === prefix || key.startsWith(`${prefix}.`)) {
                delete remainingErrors[key];
            }
        });
    });
    clientErrors.value = { ...remainingErrors, ...nextClientErrors };
    stepErrors.value[step] = errors;

    return errors.length === 0;
};

watch(() => form.domain_mode, (mode) => {
    if (mode === 'shared') {
        form.custom_domain = '';
        form.database_mode = 'shared';
    }
});

watch(selectedPlanIsTrial, (isTrial) => {
    if (isTrial) {
        form.payment_splits = [createPaymentSplit()];
        form.payment_notes = '';
        if (!form.trial_end_date) {
            const date = new Date();
            date.setDate(date.getDate() + Math.max(selectedPlanTrialDays.value, 1));
            form.trial_end_date = date.toISOString().slice(0, 10);
        }
    } else {
        form.trial_end_date = '';
    }
});

const submit = () => {
    if (!validateStep(4) || !validateStep(3) || !validateStep(2) || !validateStep(1)) {
        currentStep.value = [1, 2, 3, 4].find((step) => stepErrors.value[step]?.length) || 1;
        return;
    }

    form.post('/admin/tenants');
};
</script>

<template>
    <AppLayout>
        <Head title="Add Tenant" />

        <div class="flex flex-col gap-3">
            <TenantStepNav
                :steps="steps"
                :current-step="currentStep"
                :step-errors="stepErrors"
                @select="goToStep"
            />

            <form @submit.prevent="submit" novalidate class="rounded-xl border border-white/10 bg-white/5 p-3">
                <section v-if="currentStep === 1" class="space-y-4">
                    <div>
                        <h3 class="mt-1.5 text-xl font-semibold">Business Info</h3>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Gym Name</label>
                            <input v-model="form.gym_name" :class="fieldClass('gym_name', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" required>
                            <p v-if="fieldError('gym_name')" class="field-error">{{ fieldError('gym_name') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Business Type</label>
                            <select v-model="form.business_type" :class="fieldClass('business_type', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" required>
                                <option value="">Select type</option>
                                <option value="Gym">Gym</option>
                                <option value="Yoga">Yoga</option>
                                <option value="Turf">Turf</option>
                            </select>
                            <p v-if="fieldError('business_type')" class="field-error">{{ fieldError('business_type') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">City</label>
                            <input v-model="form.city" :class="fieldClass('city', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" required>
                            <p v-if="fieldError('city')" class="field-error">{{ fieldError('city') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">State</label>
                            <input v-model="form.state" :class="fieldClass('state', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" required>
                            <p v-if="fieldError('state')" class="field-error">{{ fieldError('state') }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Address</label>
                            <textarea v-model="form.address" rows="4" :class="fieldClass('address', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" required></textarea>
                            <p v-if="fieldError('address')" class="field-error">{{ fieldError('address') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">GST Number</label>
                            <input v-model="form.gst_number" :class="fieldClass('gst_number', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')">
                            <p v-if="fieldError('gst_number')" class="field-error">{{ fieldError('gst_number') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Business Phone</label>
                            <input v-model="form.phone" :class="fieldClass('phone', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" required minlength="10">
                            <p v-if="fieldError('phone')" class="field-error">{{ fieldError('phone') }}</p>
                        </div>
                    </div>
                </section>

                <section v-if="currentStep === 2" class="space-y-4">
                    <div>
                        <h3 class="mt-1.5 text-xl font-semibold">Owner account</h3>
                        <p class="mt-1 text-sm text-slate-400">Password is auto-generated: first 4 chars of email + @ + last 4 digits of phone.</p>
                    </div>

                    <div class="space-y-3">
                        <div v-for="(owner, index) in form.owners" :key="index" class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                    {{ index === 0 ? 'Primary Owner' : `Additional Owner ${index}` }}
                                </span>
                                <button
                                    v-if="index > 0"
                                    type="button"
                                    @click="removeOwner(index)"
                                    class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-sm text-red-400 transition hover:bg-white/10"
                                >
                                    Remove
                                </button>
                            </div>

                            <div class="grid gap-3 md:grid-cols-3">
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Name</label>
                                    <input
                                        v-model="owner.name"
                                        :class="ownerFieldClass(index, 'name', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')"
                                        :placeholder="index === 0 ? 'Owner name' : 'Owner name'"
                                        required
                                    >
                                    <p v-if="ownerError(index, 'name')" class="field-error">{{ ownerError(index, 'name') }}</p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Email</label>
                                    <input
                                        v-model="owner.email"
                                        type="email"
                                        :class="ownerFieldClass(index, 'email', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')"
                                        placeholder="owner@example.com"
                                        required
                                    >
                                    <p v-if="ownerError(index, 'email')" class="field-error">{{ ownerError(index, 'email') }}</p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Phone</label>
                                    <input
                                        v-model="owner.phone"
                                        :class="ownerFieldClass(index, 'phone', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')"
                                        placeholder="10-digit mobile"
                                        required
                                        minlength="10"
                                    >
                                    <p v-if="ownerError(index, 'phone')" class="field-error">{{ ownerError(index, 'phone') }}</p>
                                </div>
                            </div>

                            <div
                                v-if="ownerPassword(owner)"
                                class="flex flex-wrap items-center gap-3 rounded-xl border border-orange-400/20 bg-orange-500/10 px-3 py-2 text-sm"
                            >
                                <span class="text-slate-400">Login password:</span>
                                <code class="font-semibold text-white">{{ ownerPassword(owner) }}</code>
                                <button
                                    type="button"
                                    class="ml-auto rounded-xl border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-300 transition hover:bg-white/10"
                                    @click="copyOwnerPassword(owner)"
                                >
                                    Copy
                                </button>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="addOwner"
                        class="w-full rounded-xl border border-dashed border-white/10 px-3 py-2.5 text-sm font-semibold text-slate-400 transition hover:bg-white/5"
                    >
                        + Add Another Owner
                    </button>
                </section>

                <section v-if="currentStep === 3" class="space-y-4">
                    <div>
                        <h3 class="mt-1.5 text-xl font-semibold">Plan & Routing</h3>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Subdomain</label>
                            <input v-model="form.subdomain" :class="fieldClass('subdomain', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" required>
                            <p v-if="fieldError('subdomain')" class="field-error">{{ fieldError('subdomain') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Domain Mode</label>
                            <select v-model="form.domain_mode" :class="fieldClass('domain_mode', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" required>
                                <option value="shared">Shared domain</option>
                                <option value="separate">Separate domain</option>
                            </select>
                            <p v-if="fieldError('domain_mode')" class="field-error">{{ fieldError('domain_mode') }}</p>
                        </div>
                        <div v-if="form.domain_mode === 'separate'" class="md:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Custom Domain</label>
                            <input v-model="form.custom_domain" placeholder="gym.example.com" :class="fieldClass('custom_domain', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')">
                            <p v-if="fieldError('custom_domain')" class="field-error">{{ fieldError('custom_domain') }}</p>
                        </div>
                        <div :class="form.domain_mode === 'separate' ? '' : 'md:col-span-2'">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Database Mode</label>
                            <select v-model="form.database_mode" :class="fieldClass('database_mode', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" required>
                                <option value="shared">Shared database</option>
                                <option value="separate">Separate database</option>
                            </select>
                            <p v-if="fieldError('database_mode')" class="field-error">{{ fieldError('database_mode') }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Plan</label>
                            <select v-model="form.plan_id" :class="fieldClass('plan_id', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" required>
                                <option value="">Select plan</option>
                                <option v-for="plan in props.plans" :key="plan.id" :value="String(plan.id)">
                                    {{ plan.is_trial ? `${plan.name} Â· Trial (${plan.trial_days} days)` : `${plan.name} Â· ${formatPlanPrice(plan.price_paise)} / ${plan.billing_cycle}` }}
                                </option>
                            </select>
                            <p v-if="fieldError('plan_id')" class="field-error">{{ fieldError('plan_id') }}</p>
                        </div>

                        <div v-if="selectedPlanIsTrial" class="md:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Trial End Date</label>
                            <input v-model="form.trial_end_date" type="date" :class="fieldClass('trial_end_date', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')">
                            <p v-if="fieldError('trial_end_date')" class="field-error">{{ fieldError('trial_end_date') }}</p>
                            <p class="mt-2 text-xs text-slate-400">Trial plan selected{{ selectedPlanTrialDays ? ` Â· default ${selectedPlanTrialDays} days` : '' }}.</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Internal notes</label>
                            <textarea v-model="form.notes" rows="3" :class="fieldClass('notes', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')"></textarea>
                            <p v-if="fieldError('notes')" class="field-error">{{ fieldError('notes') }}</p>
                        </div>

                        <div v-if="shouldShowPaymentSection" class="md:col-span-2 rounded-xl border border-white/10 bg-white/5 p-4">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Payment (Optional)</p>

                            <div class="mb-3 flex flex-wrap justify-between gap-2 text-sm text-slate-400">
                                <span>Plan price: <strong class="text-white">{{ formatPlanPrice(selectedPlanPricePaise) }}</strong></span>
                                <span>Paying now: <strong class="text-white">{{ formatPlanPrice(paymentTotalPaise) }}</strong></span>
                                <span>Balance: <strong class="text-orange-300">{{ formatPlanPrice(paymentBalancePaise) }}</strong></span>
                            </div>

                            <div class="mb-2 flex flex-col gap-2">
                                <div v-for="(split, index) in form.payment_splits" :key="index" class="grid gap-3 lg:grid-cols-[1.1fr_1fr_1.2fr_auto]">
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Method</label>
                                        <select v-model="split.method" :class="paymentSplitFieldClass(index, 'method', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')">
                                            <option value="Cash">Cash</option>
                                            <option value="Bank transfer">Bank transfer</option>
                                            <option value="UPI">UPI</option>
                                            <option value="Cheque">Cheque</option>
                                        </select>
                                        <p v-if="paymentSplitError(index, 'method')" class="field-error">{{ paymentSplitError(index, 'method') }}</p>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Amount (â‚¹)</label>
                                        <input v-model="split.amount" type="number" step="0.01" min="0" :class="paymentSplitFieldClass(index, 'amount', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" placeholder="0.00">
                                        <p v-if="paymentSplitError(index, 'amount')" class="field-error">{{ paymentSplitError(index, 'amount') }}</p>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Reference</label>
                                        <input v-model="split.reference" :class="paymentSplitFieldClass(index, 'reference', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" placeholder="UPI ID / cheque no.">
                                        <p v-if="paymentSplitError(index, 'reference')" class="field-error">{{ paymentSplitError(index, 'reference') }}</p>
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" @click="removePaymentSplit(index)" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-red-400 transition hover:bg-white/10" title="Remove">
                                            âœ•
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" @click="addPaymentSplit" class="mb-3 w-full rounded-xl border border-dashed border-white/10 px-3 py-2 text-sm font-semibold text-slate-400 transition hover:bg-white/5">
                                + Add Payment Method
                            </button>
                            <p v-if="fieldError('payment_splits')" class="field-error">{{ fieldError('payment_splits') }}</p>

                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Payment Date</label>
                                    <input v-model="form.payment_paid_at" type="date" :class="fieldClass('payment_paid_at', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')">
                                    <p v-if="fieldError('payment_paid_at')" class="field-error">{{ fieldError('payment_paid_at') }}</p>
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Payment Notes</label>
                                    <input v-model="form.payment_notes" :class="fieldClass('payment_notes', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" placeholder="Optional">
                                    <p v-if="fieldError('payment_notes')" class="field-error">{{ fieldError('payment_notes') }}</p>
                                </div>
                            </div>

                            <div v-if="paymentStatus" :class="['mt-3 rounded-xl border px-3 py-2 text-sm', paymentStatus.tone]">
                                {{ paymentStatus.message }}
                            </div>
                        </div>
                    </div>
                </section>

                <section v-if="currentStep === 4" class="space-y-4">
                    <div>
                        <h3 class="mt-1.5 text-xl font-semibold">Review</h3>
                        <p class="mt-1 text-sm text-slate-400">Review tenant details before creating.</p>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="rounded-xl border border-white/10 bg-slate-950/50 p-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Business</p>
                            <div class="mt-3 grid gap-3 text-sm">
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Gym Name:</span><span class="font-semibold text-right">{{ form.gym_name || 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Business Type:</span><span class="font-semibold text-right">{{ form.business_type || 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Business Phone:</span><span class="font-semibold text-right">{{ form.phone || 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">City:</span><span class="font-semibold text-right">{{ form.city || 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">State:</span><span class="font-semibold text-right">{{ form.state || 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">GST Number:</span><span class="font-semibold text-right">{{ form.gst_number || 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Address:</span><span class="font-semibold text-right">{{ form.address || 'â€”' }}</span></div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-white/10 bg-slate-950/50 p-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Routing & Plan</p>
                            <div class="mt-3 grid gap-3 text-sm">
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Subdomain:</span><span class="font-semibold text-right">{{ form.subdomain || 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Domain Mode:</span><span class="font-semibold text-right">{{ form.domain_mode || 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Custom Domain:</span><span class="font-semibold text-right">{{ form.custom_domain || 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Database Mode:</span><span class="font-semibold text-right">{{ form.database_mode || 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Plan:</span><span class="font-semibold text-right">{{ selectedPlan ? selectedPlan.name : 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Plan Price:</span><span class="font-semibold text-right">{{ selectedPlan ? formatPlanPrice(selectedPlan.price_paise) : 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Trial End Date:</span><span class="font-semibold text-right">{{ form.trial_end_date || 'â€”' }}</span></div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-white/10 bg-slate-950/50 p-3 md:col-span-2">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Owners</p>
                            <div class="mt-3 grid gap-3">
                                <div v-for="(owner, index) in reviewOwners" :key="`review-owner-${index}`" class="rounded-lg border border-white/10 bg-white/5 p-2.5 text-sm">
                                    <div class="mb-2 text-xs uppercase tracking-[0.18em] text-slate-400">{{ index === 0 ? 'Primary Owner' : `Additional Owner ${index}` }}</div>
                                    <div class="grid gap-2 md:grid-cols-3">
                                        <div><span class="text-slate-400">Name:</span> <span class="font-semibold">{{ owner.name || 'â€”' }}</span></div>
                                        <div><span class="text-slate-400">Email:</span> <span class="font-semibold">{{ owner.email || 'â€”' }}</span></div>
                                        <div><span class="text-slate-400">Phone:</span> <span class="font-semibold">{{ owner.phone || 'â€”' }}</span></div>
                                    </div>
                                </div>
                                <div v-if="!reviewOwners.length" class="text-sm font-semibold">â€”</div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-white/10 bg-slate-950/50 p-3 md:col-span-2">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Payment</p>
                            <div v-if="selectedPlanIsTrial" class="mt-3 text-sm font-semibold">Not required for trial plan.</div>
                            <div v-else class="mt-3 grid gap-3 text-sm">
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Paying Now:</span><span class="font-semibold text-right">{{ formatPlanPrice(paymentTotalPaise) }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Balance:</span><span class="font-semibold text-right">{{ formatPlanPrice(paymentBalancePaise) }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Payment Date:</span><span class="font-semibold text-right">{{ form.payment_paid_at || 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Payment Notes:</span><span class="font-semibold text-right">{{ form.payment_notes || 'â€”' }}</span></div>
                                <div class="grid gap-2">
                                    <div v-for="(split, index) in reviewPaymentSplits" :key="`review-split-${index}`" class="rounded-xl border border-white/10 bg-white/5 p-3">
                                        <div class="grid gap-2 md:grid-cols-3">
                                            <div><span class="text-slate-400">Method:</span> <span class="font-semibold">{{ split.method || 'â€”' }}</span></div>
                                            <div><span class="text-slate-400">Amount:</span> <span class="font-semibold">{{ split.amount ? formatPlanPrice(Math.round(Number(split.amount || 0) * 100)) : 'â€”' }}</span></div>
                                            <div><span class="text-slate-400">Reference:</span> <span class="font-semibold">{{ split.reference || 'â€”' }}</span></div>
                                        </div>
                                    </div>
                                    <div v-if="!reviewPaymentSplits.length" class="font-semibold">No payment entered.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-slate-950/50 p-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Internal Notes</p>
                        <p class="mt-2 text-sm font-semibold">{{ form.notes || 'â€”' }}</p>
                    </div>
                </section>

                <div class="mt-5 flex items-center justify-between">
                    <button
                        v-if="currentStep > 1"
                        type="button"
                        @click="prevStep"
                        class="rounded-lg border border-white/10 bg-slate-950/50 px-4 py-2 text-sm font-semibold hover:bg-white/10"
                    >
                        Back
                    </button>
                    <div v-else></div>

                    <button
                        v-if="currentStep < steps.length"
                        type="button"
                        @click="nextStep"
                        class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400"
                    >
                        Next
                    </button>
                    <button
                        v-else
                        type="submit"
                        class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-orange-400"
                        :disabled="form.processing"
                    >
                        Create Tenant
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>



