<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
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

const hasErrors = computed(() => Object.keys(form.errors || {}).length > 0);
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

    if (step === 1) {
        if (!hasMinLength(form.gym_name, 2)) errors.push('Gym Name is required.');
        if (!form.business_type) errors.push('Business Type is required.');
        if (!form.city.trim()) errors.push('City is required.');
        if (!form.state.trim()) errors.push('State is required.');
        if (!hasMinLength(form.address, 10)) errors.push('Address must be at least 10 characters.');
        if (!hasDigitsLength(form.phone, 10)) errors.push('Business Phone must be at least 10 digits.');
    }

    if (step === 2) {
        if (!form.owners.length) {
            errors.push('At least one owner is required.');
        }

        form.owners.forEach((owner, index) => {
            const label = index === 0 ? 'Primary Owner' : `Additional Owner ${index}`;
            if (!hasMinLength(owner.name, 2)) errors.push(`${label} name must be at least 2 characters.`);
            if (!isEmail(owner.email)) errors.push(`${label} email must be valid.`);
            if (!hasDigitsLength(owner.phone, 10)) errors.push(`${label} phone must be at least 10 digits.`);
        });
    }

    if (step === 3) {
        if (!hasMinLength(form.subdomain, 3)) errors.push('Subdomain must be at least 3 characters.');
        if (!form.domain_mode) errors.push('Domain Mode is required.');
        if (form.domain_mode === 'separate' && !form.custom_domain.trim()) errors.push('Custom Domain is required for separate domain mode.');
        if (!form.database_mode) errors.push('Database Mode is required.');
        if (!form.plan_id) errors.push('Plan is required.');
        if (selectedPlanIsTrial.value && !form.trial_end_date) errors.push('Trial End Date is required for trial plans.');
        if (paymentExcessPaise.value > 0) errors.push(`Payment exceeds plan amount by ${formatPlanPrice(paymentExcessPaise.value)}.`);
    }

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

        <div class="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
            <aside class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
                <div class="space-y-4">
                    <button
                        v-for="step in steps"
                        :key="step.id"
                        type="button"
                        @click="goToStep(step.id)"
                        :class="['flex w-full items-start gap-4 rounded-[1.5rem] border px-4 py-4 text-left transition', currentStep === step.id ? 'border-orange-400 bg-orange-500/10' : 'border-white/10 bg-slate-950/50 hover:bg-white/5']"
                    >
                        <span :class="['inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold', currentStep === step.id ? 'bg-orange-500 text-slate-950' : 'bg-orange-500/20 text-orange-300']">
                            {{ String(step.id).padStart(2, '0') }}
                        </span>
                        <span>
                            <span class="block text-sm font-semibold">{{ step.title }}</span>
                            <span class="mt-1 block text-xs text-slate-400">{{ step.desc }}</span>
                        </span>
                    </button>
                </div>
            </aside>

            <form @submit.prevent="submit" novalidate class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                <div v-if="hasErrors" class="mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                    {{ Object.values(form.errors)[0] }}
                </div>

                <section v-if="currentStep === 1" class="space-y-6">
                    <div v-if="stepErrors[1]?.length" class="rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                        <div v-for="error in stepErrors[1]" :key="error">{{ error }}</div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.36em] text-emerald-300">Step 1 of 4</p>
                        <h3 class="mt-3 text-2xl font-semibold">Business Info</h3>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Gym Name</label>
                            <input v-model="form.gym_name" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Business Type</label>
                            <select v-model="form.business_type" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                                <option value="">Select type</option>
                                <option value="Gym">Gym</option>
                                <option value="Yoga">Yoga</option>
                                <option value="Turf">Turf</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">City</label>
                            <input v-model="form.city" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">State</label>
                            <input v-model="form.state" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium">Address</label>
                            <textarea v-model="form.address" rows="4" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required></textarea>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">GST Number</label>
                            <input v-model="form.gst_number" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Business Phone</label>
                            <input v-model="form.phone" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required minlength="10">
                        </div>
                    </div>
                </section>

                <section v-if="currentStep === 2" class="space-y-6">
                    <div v-if="stepErrors[2]?.length" class="rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                        <div v-for="error in stepErrors[2]" :key="error">{{ error }}</div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.36em] text-emerald-300">Step 2 of 4</p>
                        <h3 class="mt-3 text-2xl font-semibold">Owner account</h3>
                        <p class="mt-1 text-sm text-slate-400">Password is auto-generated: first 4 chars of email + @ + last 4 digits of phone.</p>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(owner, index) in form.owners" :key="index" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                    {{ index === 0 ? 'Primary Owner' : `Additional Owner ${index}` }}
                                </span>
                                <button
                                    v-if="index > 0"
                                    type="button"
                                    @click="removeOwner(index)"
                                    class="rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-red-400 transition hover:bg-white/10"
                                >
                                    Remove
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-2 block text-sm font-medium">Name</label>
                                    <input
                                        v-model="owner.name"
                                        class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400"
                                        :placeholder="index === 0 ? 'Owner name' : 'Owner name'"
                                        required
                                    >
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium">Email</label>
                                    <input
                                        v-model="owner.email"
                                        type="email"
                                        class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400"
                                        placeholder="owner@example.com"
                                        required
                                    >
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium">Phone</label>
                                    <input
                                        v-model="owner.phone"
                                        class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400"
                                        placeholder="10-digit mobile"
                                        required
                                        minlength="10"
                                    >
                                </div>
                            </div>

                            <div
                                v-if="ownerPassword(owner)"
                                class="flex flex-wrap items-center gap-3 rounded-2xl border border-orange-400/20 bg-orange-500/10 px-5 py-3 text-sm"
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
                        class="w-full rounded-[1.25rem] border border-dashed border-white/10 px-4 py-4 text-sm font-semibold text-slate-400 transition hover:bg-white/5"
                    >
                        + Add Another Owner
                    </button>
                </section>

                <section v-if="currentStep === 3" class="space-y-6">
                    <div v-if="stepErrors[3]?.length" class="rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                        <div v-for="error in stepErrors[3]" :key="error">{{ error }}</div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.36em] text-emerald-300">Step 3 of 4</p>
                        <h3 class="mt-3 text-2xl font-semibold">Plan & Routing</h3>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Subdomain</label>
                            <input v-model="form.subdomain" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Domain Mode</label>
                            <select v-model="form.domain_mode" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                                <option value="shared">Shared domain</option>
                                <option value="separate">Separate domain</option>
                            </select>
                        </div>
                        <div v-if="form.domain_mode === 'separate'" class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium">Custom Domain</label>
                            <input v-model="form.custom_domain" placeholder="gym.example.com" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400">
                        </div>
                        <div :class="form.domain_mode === 'separate' ? '' : 'md:col-span-2'">
                            <label class="mb-2 block text-sm font-medium">Database Mode</label>
                            <select v-model="form.database_mode" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                                <option value="shared">Shared database</option>
                                <option value="separate">Separate database</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium">Plan</label>
                            <select v-model="form.plan_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                                <option value="">Select plan</option>
                                <option v-for="plan in props.plans" :key="plan.id" :value="String(plan.id)">
                                    {{ plan.is_trial ? `${plan.name} · Trial (${plan.trial_days} days)` : `${plan.name} · ${formatPlanPrice(plan.price_paise)} / ${plan.billing_cycle}` }}
                                </option>
                            </select>
                        </div>

                        <div v-if="selectedPlanIsTrial" class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium">Trial End Date</label>
                            <input v-model="form.trial_end_date" type="date" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400">
                            <p class="mt-2 text-xs text-slate-400">Trial plan selected{{ selectedPlanTrialDays ? ` · default ${selectedPlanTrialDays} days` : '' }}.</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium">Internal notes</label>
                            <textarea v-model="form.notes" rows="3" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400"></textarea>
                        </div>

                        <div v-if="shouldShowPaymentSection" class="md:col-span-2 rounded-[1.25rem] border border-white/10 bg-white/5 p-6">
                            <p class="mb-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Payment (Optional)</p>

                            <div class="mb-4 flex flex-wrap justify-between gap-3 text-sm text-slate-400">
                                <span>Plan price: <strong class="text-white">{{ formatPlanPrice(selectedPlanPricePaise) }}</strong></span>
                                <span>Paying now: <strong class="text-white">{{ formatPlanPrice(paymentTotalPaise) }}</strong></span>
                                <span>Balance: <strong class="text-orange-300">{{ formatPlanPrice(paymentBalancePaise) }}</strong></span>
                            </div>

                            <div class="mb-3 flex flex-col gap-3">
                                <div v-for="(split, index) in form.payment_splits" :key="index" class="grid gap-3 lg:grid-cols-[1.1fr_1fr_1.2fr_auto]">
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Method</label>
                                        <select v-model="split.method" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400">
                                            <option value="Cash">Cash</option>
                                            <option value="Bank transfer">Bank transfer</option>
                                            <option value="UPI">UPI</option>
                                            <option value="Cheque">Cheque</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Amount (₹)</label>
                                        <input v-model="split.amount" type="number" step="0.01" min="0" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" placeholder="0.00">
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Reference</label>
                                        <input v-model="split.reference" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" placeholder="UPI ID / cheque no.">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" @click="removePaymentSplit(index)" class="inline-flex h-[52px] w-[52px] items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-red-400 transition hover:bg-white/10" title="Remove">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" @click="addPaymentSplit" class="mb-4 w-full rounded-2xl border border-dashed border-white/10 px-4 py-3 text-sm font-semibold text-slate-400 transition hover:bg-white/5">
                                + Add Payment Method
                            </button>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Payment Date</label>
                                    <input v-model="form.payment_paid_at" type="date" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400">
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Payment Notes</label>
                                    <input v-model="form.payment_notes" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" placeholder="Optional">
                                </div>
                            </div>

                            <div v-if="paymentStatus" :class="['mt-4 rounded-2xl border px-4 py-3 text-sm', paymentStatus.tone]">
                                {{ paymentStatus.message }}
                            </div>
                        </div>
                    </div>
                </section>

                <section v-if="currentStep === 4" class="space-y-6">
                    <div v-if="stepErrors[4]?.length" class="rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                        <div v-for="error in stepErrors[4]" :key="error">{{ error }}</div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.36em] text-emerald-300">Step 4 of 4</p>
                        <h3 class="mt-3 text-2xl font-semibold">Review</h3>
                        <p class="mt-1 text-sm text-slate-400">Review tenant details before creating.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Business</p>
                            <div class="mt-3 grid gap-3 text-sm">
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Gym Name:</span><span class="font-semibold text-right">{{ form.gym_name || '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Business Type:</span><span class="font-semibold text-right">{{ form.business_type || '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Business Phone:</span><span class="font-semibold text-right">{{ form.phone || '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">City:</span><span class="font-semibold text-right">{{ form.city || '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">State:</span><span class="font-semibold text-right">{{ form.state || '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">GST Number:</span><span class="font-semibold text-right">{{ form.gst_number || '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Address:</span><span class="font-semibold text-right">{{ form.address || '—' }}</span></div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Routing & Plan</p>
                            <div class="mt-3 grid gap-3 text-sm">
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Subdomain:</span><span class="font-semibold text-right">{{ form.subdomain || '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Domain Mode:</span><span class="font-semibold text-right">{{ form.domain_mode || '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Custom Domain:</span><span class="font-semibold text-right">{{ form.custom_domain || '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Database Mode:</span><span class="font-semibold text-right">{{ form.database_mode || '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Plan:</span><span class="font-semibold text-right">{{ selectedPlan ? selectedPlan.name : '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Plan Price:</span><span class="font-semibold text-right">{{ selectedPlan ? formatPlanPrice(selectedPlan.price_paise) : '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Trial End Date:</span><span class="font-semibold text-right">{{ form.trial_end_date || '—' }}</span></div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4 md:col-span-2">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Owners</p>
                            <div class="mt-3 grid gap-3">
                                <div v-for="(owner, index) in reviewOwners" :key="`review-owner-${index}`" class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm">
                                    <div class="mb-2 text-xs uppercase tracking-[0.18em] text-slate-400">{{ index === 0 ? 'Primary Owner' : `Additional Owner ${index}` }}</div>
                                    <div class="grid gap-2 md:grid-cols-3">
                                        <div><span class="text-slate-400">Name:</span> <span class="font-semibold">{{ owner.name || '—' }}</span></div>
                                        <div><span class="text-slate-400">Email:</span> <span class="font-semibold">{{ owner.email || '—' }}</span></div>
                                        <div><span class="text-slate-400">Phone:</span> <span class="font-semibold">{{ owner.phone || '—' }}</span></div>
                                    </div>
                                </div>
                                <div v-if="!reviewOwners.length" class="text-sm font-semibold">—</div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4 md:col-span-2">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Payment</p>
                            <div v-if="selectedPlanIsTrial" class="mt-3 text-sm font-semibold">Not required for trial plan.</div>
                            <div v-else class="mt-3 grid gap-3 text-sm">
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Paying Now:</span><span class="font-semibold text-right">{{ formatPlanPrice(paymentTotalPaise) }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Balance:</span><span class="font-semibold text-right">{{ formatPlanPrice(paymentBalancePaise) }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Payment Date:</span><span class="font-semibold text-right">{{ form.payment_paid_at || '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Payment Notes:</span><span class="font-semibold text-right">{{ form.payment_notes || '—' }}</span></div>
                                <div class="grid gap-2">
                                    <div v-for="(split, index) in reviewPaymentSplits" :key="`review-split-${index}`" class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div class="grid gap-2 md:grid-cols-3">
                                            <div><span class="text-slate-400">Method:</span> <span class="font-semibold">{{ split.method || '—' }}</span></div>
                                            <div><span class="text-slate-400">Amount:</span> <span class="font-semibold">{{ split.amount ? formatPlanPrice(Math.round(Number(split.amount || 0) * 100)) : '—' }}</span></div>
                                            <div><span class="text-slate-400">Reference:</span> <span class="font-semibold">{{ split.reference || '—' }}</span></div>
                                        </div>
                                    </div>
                                    <div v-if="!reviewPaymentSplits.length" class="font-semibold">No payment entered.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Internal Notes</p>
                        <p class="mt-2 text-sm font-semibold">{{ form.notes || '—' }}</p>
                    </div>
                </section>

                <div class="mt-8 flex items-center justify-between">
                    <button
                        v-if="currentStep > 1"
                        type="button"
                        @click="prevStep"
                        class="rounded-2xl border border-white/10 bg-slate-950/50 px-5 py-3 text-sm font-semibold hover:bg-white/10"
                    >
                        Back
                    </button>
                    <div v-else></div>

                    <button
                        v-if="currentStep < steps.length"
                        type="button"
                        @click="nextStep"
                        class="rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400"
                    >
                        Next
                    </button>
                    <button
                        v-else
                        type="submit"
                        class="rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400"
                        :disabled="form.processing"
                    >
                        Create Tenant
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
