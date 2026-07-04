<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';

const props = defineProps({
    errors: Object,
});

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const fieldError = (field) => props.errors?.[field] || form.errors?.[field] || '';
</script>

<template>
    <AppLayout>
        <Head title="Change Password" />
        <div class="app-theme-shell flex min-h-screen items-center justify-center px-4 py-10">
            <div class="app-panel w-full max-w-lg rounded-[2rem] border p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.34em] text-[var(--app-info)]">Security</p>
                <h1 class="mt-4 text-3xl font-semibold">Change your temporary password</h1>
                <p class="app-muted mt-3 text-sm leading-7">
                    This account was created with a temporary password. Set a new password before continuing.
                </p>

                <form @submit.prevent="form.post(route('password.change.update'))" class="mt-8 space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Current password</label>
                        <input
                            v-model="form.current_password"
                            type="password"
                            :class="['w-full rounded-2xl border px-4 py-3 outline-none', fieldError('current_password') ? 'field-invalid' : '']"
                            required
                        >
                        <p v-if="fieldError('current_password')" class="field-error">{{ fieldError('current_password') }}</p>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">New password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            :class="['w-full rounded-2xl border px-4 py-3 outline-none', fieldError('password') ? 'field-invalid' : '']"
                            required
                        >
                        <p v-if="fieldError('password')" class="field-error">{{ fieldError('password') }}</p>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Confirm new password</label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            :class="['w-full rounded-2xl border px-4 py-3 outline-none', fieldError('password_confirmation') ? 'field-invalid' : '']"
                            required
                        >
                        <p v-if="fieldError('password_confirmation')" class="field-error">{{ fieldError('password_confirmation') }}</p>
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-orange-400 disabled:opacity-50"
                    >
                        Update password
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

