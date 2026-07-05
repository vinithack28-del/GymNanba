<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tenant: Object,
    token: String,
    invitedEmail: String,
    errors: Object,
});

const hasInvitedEmail = computed(() => !!props.invitedEmail);

const form = useForm({
    name: '',
    phone: '',
    email: props.invitedEmail || '',
    gender: '',
    dob: '',
    address: '',
});

const fieldError = (field) => props.errors?.[field] || form.errors?.[field] || '';
const fieldClass = (field, base) => [base, fieldError(field) ? 'field-invalid' : ''];

const submit = () => {
    const emailQuery = hasInvitedEmail.value ? `?email=${encodeURIComponent(props.invitedEmail)}` : '';
    form.post(`/join/${props.token}${emailQuery}`);
};
</script>

<template>
    <Head :title="`Join ${tenant.gym_name}`" />
    
    <div class="min-h-screen bg-slate-100 p-6 pb-12">
        <div class="mx-auto max-w-[560px]">
            <div class="mb-7 pt-3 text-center">
                <div class="mx-auto mb-3.5 flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-500 text-2xl font-bold text-white">
                    {{ tenant.gym_name.charAt(0).toUpperCase() }}
                </div>
                <div class="text-2xl font-bold text-slate-900">{{ tenant.gym_name }}</div>
                <div v-if="tenant.city" class="mt-1 text-sm text-slate-500">
                    {{ tenant.city }}{{ tenant.state ? `, ${tenant.state}` : '' }}
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-lg">
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 p-6 text-white">
                    <h1 class="text-lg font-bold">Member Registration</h1>
                    <p class="mt-1 text-sm opacity-85">Fill in your details and we'll get you set up.</p>
                </div>
                <div class="p-7">
                    <form @submit.prevent="submit" novalidate>
                        <p class="mb-3.5 text-xs font-bold uppercase tracking-widest text-slate-400">Basic Information</p>

                        <div class="mb-4.5">
                            <label class="mb-1.5 block text-sm font-semibold text-slate-600">Full Name <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" placeholder="Your full name" :class="fieldClass('name', 'w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition-colors focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10')" required maxlength="100" autocomplete="name">
                            <p v-if="fieldError('name')" class="field-error field-error-light">{{ fieldError('name') }}</p>
                        </div>

                        <div class="mb-4.5">
                            <label class="mb-1.5 block text-sm font-semibold text-slate-600">Phone Number <span class="text-red-500">*</span></label>
                            <input v-model="form.phone" type="tel" placeholder="+91 98765 43210" :class="fieldClass('phone', 'w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition-colors focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10')" required maxlength="20" autocomplete="tel">
                            <p v-if="fieldError('phone')" class="field-error field-error-light">{{ fieldError('phone') }}</p>
                        </div>

                        <div class="mb-4.5">
                            <label class="mb-1.5 block text-sm font-semibold text-slate-600">Email Address</label>
                            <input v-model="form.email" type="email" placeholder="you@example.com" :readonly="hasInvitedEmail" :class="fieldClass('email', `w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition-colors focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10 ${hasInvitedEmail ? 'cursor-not-allowed text-slate-500' : ''}`)" maxlength="255" autocomplete="email">
                            <p v-if="hasInvitedEmail" class="mt-1 text-xs text-slate-500">This email was used for your invitation and cannot be changed.</p>
                            <p v-if="fieldError('email')" class="field-error field-error-light">{{ fieldError('email') }}</p>
                        </div>

                        <div class="my-5.5 h-px bg-slate-100"></div>
                        <p class="mb-3.5 text-xs font-bold uppercase tracking-widest text-slate-400">Personal Details <span class="font-normal tracking-normal text-slate-400/70 text-xs">(optional)</span></p>

                        <div class="grid gap-3.5 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-600">Gender</label>
                                <select v-model="form.gender" :class="fieldClass('gender', 'w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition-colors focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10')">
                                    <option value="">Selectâ€¦</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <p v-if="fieldError('gender')" class="field-error field-error-light">{{ fieldError('gender') }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-600">Date of Birth</label>
                                <input v-model="form.dob" type="date" :class="fieldClass('dob', 'w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition-colors focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10')">
                                <p v-if="fieldError('dob')" class="field-error field-error-light">{{ fieldError('dob') }}</p>
                            </div>
                        </div>

                        <div class="mt-4.5">
                            <label class="mb-1.5 block text-sm font-semibold text-slate-600">Address</label>
                            <textarea v-model="form.address" rows="3" placeholder="Your address (optional)" maxlength="500" :class="fieldClass('address', 'w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition-colors focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10 resize-y min-h-[80px]')"></textarea>
                            <p v-if="fieldError('address')" class="field-error field-error-light">{{ fieldError('address') }}</p>
                        </div>

                        <button type="submit" class="mt-6 block w-full rounded-xl bg-orange-500 py-3.5 text-base font-bold text-white transition-colors hover:bg-orange-600 active:scale-[0.98]" :disabled="form.processing">Submit Registration -></button>
                    </form>
                </div>
            </div>

            <div class="mt-5 text-center text-xs text-slate-400">
                After submission, gym staff will review your details and confirm your membership.
            </div>
        </div>
    </div>
</template>

