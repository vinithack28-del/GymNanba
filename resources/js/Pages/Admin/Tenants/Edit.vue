<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    tenant: Object,
    businessTypes: Array,
    plans: {
        type: Array,
        default: () => [],
    },
    owners: {
        type: Array,
        default: () => [],
    },
});

const currentStep = ref(1);
const steps = [
    { id: 1, title: 'Business Info', desc: 'Gym name, type, location, and GST' },
    { id: 2, title: 'Owner Account', desc: 'Primary and additional owners' },
    { id: 3, title: 'Plan & Routing', desc: 'View plan and technical setup' },
    { id: 4, title: 'Review', desc: 'Review and confirm tenant details' },
];

const createOwner = () => ({
    name: '',
    email: '',
    phone: '',
});

const form = useForm({
    gym_name: props.tenant.gym_name,
    business_type: props.tenant.business_type,
    city: props.tenant.city,
    state: props.tenant.state,
    address: props.tenant.address,
    gst_number: props.tenant.gst_number,
    phone: props.tenant.phone,
    owner_name: props.tenant.owner_name,
    owner_email: props.tenant.owner_email,
    owner_phone: props.tenant.owner_user?.phone || '',
    owners: props.owners?.length ? props.owners : [{ name: props.tenant.owner_name, email: props.tenant.owner_email, phone: props.tenant.owner_user?.phone || '' }],
    subdomain: props.tenant.subdomain,
    domain_mode: props.tenant.domain_mode,
    custom_domain: props.tenant.custom_domain,
    database_mode: props.tenant.database_mode,
    default_language: props.tenant.default_language,
    status: props.tenant.status,
    notes: props.tenant.notes,
});

const hasErrors = computed(() => Object.keys(form.errors || {}).length > 0);
const primaryOwner = computed(() => form.owners[0] || createOwner());
const latestSub = computed(() => props.tenant.subscriptions?.sort((a, b) => b.id - a.id)[0]);
const selectedPlan = computed(() => latestSub.value?.plan || null);
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

const formatPlanPrice = (pricePaise) => {
    const amount = Number(pricePaise || 0) / 100;

    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
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
    }

    stepErrors.value[step] = errors;

    return errors.length === 0;
};

const reviewOwners = computed(() => form.owners.filter((owner) => owner.name || owner.email || owner.phone));

// Watch for changes in owners array to sync with individual fields
watch(() => form.owners, (owners) => {
    if (owners.length > 0) {
        form.owner_name = owners[0].name;
        form.owner_email = owners[0].email;
        form.owner_phone = owners[0].phone;
    }
}, { deep: true });

const submit = () => {
    if (!validateStep(3) || !validateStep(2) || !validateStep(1)) {
        currentStep.value = [1, 2, 3].find((step) => stepErrors.value[step]?.length) || 1;
        return;
    }

    // Sync primary owner for backend compatibility
    if (form.owners.length > 0) {
        form.owner_name = form.owners[0].name;
        form.owner_email = form.owners[0].email;
        form.owner_phone = form.owners[0].phone;
    }

    form.put(`/admin/tenants/${props.tenant.id}`);
};
</script>

<template>
    <AppLayout>
        <Head title="Edit Tenant" />
        
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
                                <option v-for="type in businessTypes" :key="type" :value="type">{{ type }}</option>
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
                            <textarea v-model="form.address" rows="3" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required></textarea>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">GST Number <span class="text-slate-400 font-normal">(optional)</span></label>
                            <input v-model="form.gst_number" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Phone</label>
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
                            <label class="mb-2 block text-sm font-medium">Current Plan</label>
                            <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                                <div v-if="selectedPlan" class="grid gap-2 text-sm">
                                    <div class="flex justify-between gap-4"><span class="text-slate-400">Plan:</span><span class="font-semibold">{{ selectedPlan.name }}</span></div>
                                    <div class="flex justify-between gap-4"><span class="text-slate-400">Price:</span><span class="font-semibold">{{ formatPlanPrice(selectedPlan.price_paise) }} / {{ selectedPlan.billing_cycle }}</span></div>
                                    <div v-if="latestSub" class="flex justify-between gap-4"><span class="text-slate-400">Status:</span><span class="font-semibold capitalize">{{ latestSub.status }}</span></div>
                                    <div v-if="latestSub?.start_date" class="flex justify-between gap-4"><span class="text-slate-400">Start Date:</span><span class="font-semibold">{{ formatDate(latestSub.start_date) }}</span></div>
                                    <div v-if="latestSub?.end_date" class="flex justify-between gap-4"><span class="text-slate-400">End Date:</span><span class="font-semibold">{{ formatDate(latestSub.end_date) }}</span></div>
                                </div>
                                <div v-else class="text-sm text-slate-400">No active plan found.</div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium">Internal notes</label>
                            <textarea v-model="form.notes" rows="3" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400"></textarea>
                        </div>
                    </div>
                </section>

                <section v-if="currentStep === 4" class="space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.36em] text-emerald-300">Step 4 of 4</p>
                        <h3 class="mt-3 text-2xl font-semibold">Review</h3>
                        <p class="mt-1 text-sm text-slate-400">Review tenant details before saving.</p>
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
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Status & Notes</p>
                            <div class="mt-3 grid gap-3 text-sm">
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Status:</span><span class="font-semibold text-right capitalize">{{ form.status || '—' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="text-slate-400">Notes:</span><span class="font-semibold text-right">{{ form.notes || '—' }}</span></div>
                            </div>
                        </div>
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
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
