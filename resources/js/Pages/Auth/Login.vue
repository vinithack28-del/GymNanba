<script setup>
import AuthLayout from '../../Layouts/AuthLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    errors: Object,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const credentialFeedback = ref('');

const fillCredentials = (email, password) => {
    form.email = email;
    form.password = password;
    
    try {
        navigator.clipboard.writeText(`Email: ${email}\nPassword: ${password}`);
        credentialFeedback.value = 'Superadmin credentials copied and filled into the form.';
    } catch (error) {
        credentialFeedback.value = 'Superadmin credentials filled into the form.';
    }
};
</script>

<template>
    <AuthLayout>
        <Head title="Sign in" />
        <div class="grid w-full gap-10 lg:grid-cols-[minmax(0,1.2fr)_640px]">
            <section class="flex flex-col justify-center">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500/20">
                    <span class="text-orange-300 font-bold">GN</span>
                </div>
                <h1 class="mt-6 max-w-2xl text-5xl font-semibold tracking-tight sm:text-6xl">
                    Unified access for every role in your gym.
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-slate-400">
                    Sign in as superadmin, admin, trainer, receptionist, or member to manage the work that matters
                    to your role.
                </p>
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/5 p-8 shadow-2xl shadow-black/20 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-400">Welcome back</p>
                        <h2 class="mt-2 text-3xl font-semibold">Sign in</h2>
                    </div>
                </div>

                <div v-if="errors && Object.keys(errors).length > 0" class="mt-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                    {{ Object.values(errors)[0] }}
                </div>

                <form @submit.prevent="form.post('/login')" class="mt-8 space-y-5">
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-slate-200">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="name@gymnanba.com"
                            class="w-full rounded-2xl border border-white/10 bg-slate-800 px-4 py-3 outline-none transition placeholder:text-slate-500 focus:border-orange-400"
                            required
                            autofocus
                        >
                    </div>
                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-slate-200">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Enter your password"
                            class="w-full rounded-2xl border border-white/10 bg-slate-800 px-4 py-3 outline-none transition placeholder:text-slate-500 focus:border-orange-400"
                            required
                        >
                    </div>
                    <label class="flex items-center gap-3 text-sm text-slate-400">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            value="1"
                            class="h-4 w-4 rounded border-white/10 bg-slate-950/70 text-orange-500 focus:ring-orange-400"
                        >
                        Remember me
                    </label>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-orange-400 disabled:opacity-50"
                    >
                        Sign in
                    </button>
                </form>

                <div class="mt-8">
                    <p class="text-sm font-medium text-slate-400">Default login details</p>
                    <div class="mt-3 overflow-hidden rounded-2xl border border-white/10">
                        <table class="w-full table-fixed divide-y divide-white/10 text-left text-sm">
                            <thead class="bg-slate-800 text-slate-300">
                                <tr>
                                    <th class="w-[22%] px-3 py-3 font-medium">Role</th>
                                    <th class="w-[36%] px-3 py-3 font-medium">Email</th>
                                    <th class="w-[27%] px-3 py-3 font-medium">Password</th>
                                    <th class="w-[15%] px-3 py-3 font-medium text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-transparent text-slate-200">
                                <tr>
                                    <td class="px-3 py-3 align-middle font-medium break-words">Superadmin</td>
                                    <td class="px-3 py-3 align-middle text-slate-300 break-words text-[13px] leading-5">superadmin@gymnanba.com</td>
                                    <td class="px-3 py-3 align-middle font-mono text-[12px] leading-5 tracking-[0.02em] text-orange-200 break-all">SuperAdmin@123</td>
                                    <td class="px-3 py-3 text-center align-middle">
                                        <button
                                            type="button"
                                            @click="fillCredentials('superadmin@gymnanba.com', 'SuperAdmin@123')"
                                            class="inline-flex items-center justify-center rounded-full border border-white/10 bg-orange-500/10 p-2 transition hover:opacity-90"
                                            title="Copy and fill login details"
                                            aria-label="Copy and fill superadmin login details"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <rect x="9" y="9" width="13" height="13" rx="2"></rect>
                                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Click the action button to copy and fill credentials.</p>
                    <p v-if="credentialFeedback" class="mt-3 text-xs text-emerald-300">{{ credentialFeedback }}</p>
                </div>
            </section>
        </div>
    </AuthLayout>
</template>
