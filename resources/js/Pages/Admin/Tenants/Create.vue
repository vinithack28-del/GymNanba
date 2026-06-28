<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const currentStep = ref(1);
const steps = [
    { id: 1, title: 'Business Info', desc: 'Gym name, type, location, and GST' },
    { id: 2, title: 'Owner Details', desc: 'Owner contact and login credentials' },
    { id: 3, title: 'Plan Selection', desc: 'Choose subscription plan and payment' },
    { id: 4, title: 'Review', desc: 'Review and confirm tenant details' },
];

const form = useForm({
    gym_name: '',
    business_type: '',
    city: '',
    state: '',
    address: '',
    gst_number: '',
    phone: '',
    owner_name: '',
    owner_email: '',
    plan_id: '',
    notes: '',
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
                            <label class="mb-2 block text-sm font-medium">Phone</label>
                            <input v-model="form.phone" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required minlength="10">
                        </div>
                    </div>
                </section>

                <section v-show="currentStep === 2" class="space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.36em] text-emerald-300">Step 2 of 4</p>
                        <h3 class="mt-3 text-2xl font-semibold">Owner Details</h3>
                        <p class="mt-1 text-sm text-slate-400">Password will be auto-generated based on email and phone.</p>
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
                    </div>
                </section>

                <section v-show="currentStep === 3" class="space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.36em] text-emerald-300">Step 3 of 4</p>
                        <h3 class="mt-3 text-2xl font-semibold">Plan Selection</h3>
                        <p class="mt-1 text-sm text-slate-400">Select a subscription plan for the tenant.</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Plan</label>
                        <select v-model="form.plan_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400" required>
                            <option value="">Select a plan</option>
                            <option value="1">Basic - Monthly</option>
                            <option value="2">Pro - Monthly</option>
                            <option value="3">Enterprise - Annual</option>
                        </select>
                    </div>
                </section>

                <section v-show="currentStep === 4" class="space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.36em] text-emerald-300">Step 4 of 4</p>
                        <h3 class="mt-3 text-2xl font-semibold">Review</h3>
                        <p class="mt-1 text-sm text-slate-400">Review tenant details before creating.</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                        <div class="grid gap-3 text-sm">
                            <div class="flex justify-between"><span class="text-slate-400">Gym Name:</span><span class="font-semibold">{{ form.gym_name }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Business Type:</span><span class="font-semibold">{{ form.business_type }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Location:</span><span class="font-semibold">{{ form.city }}, {{ form.state }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Owner:</span><span class="font-semibold">{{ form.owner_name }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Email:</span><span class="font-semibold">{{ form.owner_email }}</span></div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Internal Notes</label>
                        <textarea v-model="form.notes" rows="3" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none focus:border-orange-400"></textarea>
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