<script setup>
import AuthLayout from '../../Layouts/AuthLayout.vue';
import { useForm, Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    errors: Object,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const credentialFeedback = ref('');
const page = usePage();
const translations = computed(() => page.props.translations?.common || {});

const t = (key, fallback) => {
    return key.split('.').reduce((value, part) => value?.[part], translations.value) || fallback;
};

const fillCredentials = (email, password) => {
    form.email = email;
    form.password = password;
    
    try {
        navigator.clipboard.writeText(`Email: ${email}\nPassword: ${password}`);
        credentialFeedback.value = t('auth.credentials_copied', 'Superadmin credentials copied and filled into the form.');
    } catch (error) {
        credentialFeedback.value = t('auth.credentials_filled', 'Superadmin credentials filled into the form.');
    }
};

const fieldError = (field) => props.errors?.[field] || form.errors?.[field] || '';
</script>

<template>
    <AuthLayout>
        <Head :title="t('auth.sign_in', 'Sign in')" />
        <div class="mx-auto grid w-full max-w-[1240px] gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(440px,540px)] lg:items-center xl:gap-8 xl:grid-cols-[minmax(0,1.1fr)_580px]">
            <section class="flex flex-col justify-center lg:min-h-[calc(100vh-120px)]">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-500/20 lg:h-10 lg:w-10">
                    <span class="text-orange-300 font-bold">GN</span>
                </div>
                <h1 class="mt-4 max-w-2xl text-3xl font-semibold tracking-tight sm:text-4xl lg:mt-5 lg:text-[2.35rem] xl:text-5xl">
                    {{ t('auth.hero_title', 'Unified access for every role in your gym.') }}
                </h1>
                <p class="mt-3 max-w-xl text-base leading-7 text-slate-400 lg:text-[1.05rem]">
                    {{ t('auth.hero_subtitle', 'Sign in as superadmin, admin, trainer, receptionist, or member to manage the work that matters to your role.') }}
                </p>
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/20 backdrop-blur sm:p-6 lg:p-5 xl:p-7">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-400">{{ t('auth.welcome_back', 'Welcome back') }}</p>
                        <h2 class="mt-1 text-2xl font-semibold lg:text-[1.7rem]">{{ t('auth.sign_in', 'Sign in') }}</h2>
                    </div>
                </div>

                <form @submit.prevent="form.post('/login')" class="mt-4 space-y-3">
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-200">{{ t('auth.email', 'Email') }}</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            :placeholder="t('auth.email_placeholder', 'name@gymnanba.com')"
                            :class="['w-full rounded-xl border border-white/10 bg-slate-800 px-4 py-2.5 outline-none transition placeholder:text-slate-500 focus:border-orange-400', fieldError('email') ? 'field-invalid' : '']"
                            required
                            autofocus
                        >
                        <p v-if="fieldError('email')" class="field-error">{{ fieldError('email') }}</p>
                    </div>
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-200">{{ t('auth.password', 'Password') }}</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            :placeholder="t('auth.password_placeholder', 'Enter your password')"
                            :class="['w-full rounded-xl border border-white/10 bg-slate-800 px-4 py-2.5 outline-none transition placeholder:text-slate-500 focus:border-orange-400', fieldError('password') ? 'field-invalid' : '']"
                            required
                        >
                        <p v-if="fieldError('password')" class="field-error">{{ fieldError('password') }}</p>
                    </div>
                    <label class="flex items-center gap-3 text-sm text-slate-400">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            value="1"
                            class="h-4 w-4 rounded border-white/10 bg-slate-950/70 text-orange-500 focus:ring-orange-400"
                        >
                        {{ t('auth.remember_me', 'Remember me') }}
                    </label>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-orange-400 disabled:opacity-50"
                    >
                        {{ t('auth.sign_in', 'Sign in') }}
                    </button>
                </form>

                <div class="mt-4">
                    <p class="text-sm font-medium text-slate-400">{{ t('auth.default_login_details', 'Default login details') }}</p>
                    <div class="mt-2 grid gap-2 rounded-xl border border-white/10 bg-slate-800/70 px-3 py-2.5 text-sm sm:grid-cols-[1fr_auto] sm:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span class="font-semibold text-slate-200">{{ t('auth.superadmin', 'Superadmin') }}</span>
                                <span class="break-all text-xs text-slate-300">superadmin@gymnanba.com</span>
                                <span class="font-mono text-xs text-orange-200">SuperAdmin@123</span>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="fillCredentials('superadmin@gymnanba.com', 'SuperAdmin@123')"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-orange-500/10 transition hover:opacity-90"
                            :title="t('auth.copy_fill_login', 'Copy and fill login details')"
                            :aria-label="t('auth.copy_fill_superadmin', 'Copy and fill superadmin login details')"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="9" y="9" width="13" height="13" rx="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                        </button>
                    </div>
                    <p v-if="credentialFeedback" class="mt-2 text-xs text-emerald-300">{{ credentialFeedback }}</p>
                </div>
            </section>
        </div>
    </AuthLayout>
</template>

