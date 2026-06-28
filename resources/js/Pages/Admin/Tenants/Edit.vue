<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tenant: Object,
    businessTypes: Array,
});

const currentStep = ref(1);
const steps = [
    { id: 1, title: 'Business Info', desc: 'Gym name, type, location, and GST' },
    { id: 2, title: 'Owner Details', desc: 'Owner contact and login credentials' },
    { id: 3, title: 'Routing & Technical', desc: 'Subdomain, domain mode, and language' },
    { id: 4, title: 'Status & Notes', desc: 'Tenant status and internal notes' },
];

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
    owner_password: '',
    owner_password_confirmation: '',
    subdomain: props.tenant.subdomain,
    domain_mode: props.tenant.domain_mode,
    custom_domain: props.tenant.custom_domain,
    database_mode: props.tenant.database_mode,
    default_language: props.tenant.default_language,
    status: props.tenant.status,
    notes: props.tenant.notes,
});

const nextStep = () => {
    if (currentStep.value < steps.length) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const goToStep = (step) => {
    currentStep.value = step;
};

const submit = () => {
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

            <form @submit.prevent="submit" class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                <div v-if="form.errors" class="mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                    {{ Object.values(form.errors)[0] }}
                </div>

                <section v-show="currentStep === 1" class="space-y-6">
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

                <section v-show="currentStep === 2" class="space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.36em] text-emerald-300">Step 2 of 4</p>
                        <h3 class="mt-3 text-2xl font-semibold">Owner Details</h3>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Owner Name</label>
                            <input v-model="form.owner_name" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Owner Email</label>
                            <input type="email" v-model="form.owner_email" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">New Password <span class="text-slate-400 font-normal">(leave blank to keep current)</span></label>
                            <input type="password" v-model="form.owner_password" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Confirm New Password</label>
                            <input type="password" v-model="form.owner_password_confirmation" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400">
                        </div>
                    </div>
                </section>

                <section v-show="currentStep === 3" class="space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.36em] text-emerald-300">Step 3 of 4</p>
                        <h3 class="mt-3 text-2xl font-semibold">Routing & Technical</h3>
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
                        <div v-if="form.domain_mode === 'separate'">
                            <label class="mb-2 block text-sm font-medium">Database Mode</label>
                            <select v-model="form.database_mode" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                                <option value="shared">Shared database</option>
                                <option value="separate">Separate database</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Default Language</label>
                            <select v-model="form.default_language" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                                <option value="en">English</option>
                                <option value="hi">Hindi</option>
                                <option value="ta">Tamil</option>
                            </select>
                        </div>
                    </div>
                </section>

                <section v-show="currentStep === 4" class="space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.36em] text-emerald-300">Step 4 of 4</p>
                        <h3 class="mt-3 text-2xl font-semibold">Status & Notes</h3>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Status</label>
                            <select v-model="form.status" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                                <option value="active">Active</option>
                                <option value="trial">Trial</option>
                                <option value="suspended">Suspended</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Internal Notes</label>
                        <textarea v-model="form.notes" rows="4" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400"></textarea>
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