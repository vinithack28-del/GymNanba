<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import TenantStepNav from '../../../Components/Admin/TenantStepNav.vue';
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

const primaryOwner = computed(() => form.owners[0] || createOwner());
const latestSub = computed(() => props.tenant.subscriptions?.sort((a, b) => b.id - a.id)[0]);
const selectedPlan = computed(() => latestSub.value?.plan || null);
const stepErrors = ref({
    1: [],
    2: [],
    3: [],
    4: [],
});
const clientErrors = ref({});

const fieldError = (field) => form.errors?.[field] || clientErrors.value[field] || '';
const ownerError = (index, field) => form.errors?.[`owners.${index}.${field}`] || clientErrors.value[`owners.${index}.${field}`] || '';
const fieldClass = (field, base) => [base, fieldError(field) ? 'field-invalid' : ''];
const ownerFieldClass = (index, field, base) => [base, ownerError(index, field) ? 'field-invalid' : ''];

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
    if (!date) return 'â€”';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
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
        3: ['subdomain', 'domain_mode', 'custom_domain', 'database_mode'],
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
                                <option v-for="type in businessTypes" :key="type" :value="type">{{ type }}</option>
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
                            <textarea v-model="form.address" rows="3" :class="fieldClass('address', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" required></textarea>
                            <p v-if="fieldError('address')" class="field-error">{{ fieldError('address') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">GST Number <span class="text-slate-400 font-normal">(optional)</span></label>
                            <input v-model="form.gst_number" :class="fieldClass('gst_number', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')">
                            <p v-if="fieldError('gst_number')" class="field-error">{{ fieldError('gst_number') }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Phone</label>
                            <input v-model="form.phone" :class="fieldClass('phone', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')" required minlength="10">
                            <p v-if="fieldError('phone')" class="field-error">{{ fieldError('phone') }}</p>
                        </div>
                    </div>
                </section>

                <section v-if="currentStep === 2" class="space-y-4">
                    <div>
                        <h3 class="mt-1.5 text-xl font-semibold">Owner account</h3>
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
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Current Plan</label>
                            <div class="rounded-xl border border-white/10 bg-slate-950/50 p-3">
                                <div v-if="selectedPlan" class="grid gap-2 text-sm">
                                    <div class="flex justify-between gap-3"><span class="text-slate-400">Plan:</span><span class="font-semibold">{{ selectedPlan.name }}</span></div>
                                    <div class="flex justify-between gap-3"><span class="text-slate-400">Price:</span><span class="font-semibold">{{ formatPlanPrice(selectedPlan.price_paise) }} / {{ selectedPlan.billing_cycle }}</span></div>
                                    <div v-if="latestSub" class="flex justify-between gap-3"><span class="text-slate-400">Status:</span><span class="font-semibold capitalize">{{ latestSub.status }}</span></div>
                                    <div v-if="latestSub?.start_date" class="flex justify-between gap-3"><span class="text-slate-400">Start Date:</span><span class="font-semibold">{{ formatDate(latestSub.start_date) }}</span></div>
                                    <div v-if="latestSub?.end_date" class="flex justify-between gap-3"><span class="text-slate-400">End Date:</span><span class="font-semibold">{{ formatDate(latestSub.end_date) }}</span></div>
                                </div>
                                <div v-else class="text-sm text-slate-400">No active plan found.</div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.08em] text-slate-300">Internal notes</label>
                            <textarea v-model="form.notes" rows="3" :class="fieldClass('notes', 'w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-white outline-none focus:border-orange-400')"></textarea>
                            <p v-if="fieldError('notes')" class="field-error">{{ fieldError('notes') }}</p>
                        </div>
                    </div>
                </section>

                <section v-if="currentStep === 4" class="space-y-4">
                    <div>
                        <h3 class="mt-1.5 text-xl font-semibold">Review</h3>
                        <p class="mt-1 text-sm text-slate-400">Review tenant details before saving.</p>
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
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Status & Notes</p>
                            <div class="mt-3 grid gap-3 text-sm">
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Status:</span><span class="font-semibold text-right capitalize">{{ form.status || 'â€”' }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-400">Notes:</span><span class="font-semibold text-right">{{ form.notes || 'â€”' }}</span></div>
                            </div>
                        </div>
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
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>



