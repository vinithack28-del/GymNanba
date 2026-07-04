<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: String,
    subscription: Object,
});

const formatDate = (date) => {
    if (!date) return 'â€”';
    return new Date(date).toLocaleDateString('en-GB').replaceAll('/', '-');
};

const form = useForm({});

const logout = () => {
    form.post('/logout');
};
</script>

<template>
    <Head>
        <title>Account Blocked | GymNanba</title>
    </Head>
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md text-center">
            
            <div v-if="status === 'trial_ended'" class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-amber-500/15">
                <svg class="h-10 w-10 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <h1 v-if="status === 'trial_ended'" class="text-2xl font-semibold text-slate-200">Your free trial has ended</h1>
            <p v-if="status === 'trial_ended'" class="mt-3 text-sm leading-7 text-slate-400">
                Your trial period is over. To continue using GymNanba, please contact support to upgrade your account.
            </p>

            <div v-else-if="status === 'subscription_expired'" class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-500/15">
                <svg class="h-10 w-10 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                </svg>
            </div>
            <h1 v-else-if="status === 'subscription_expired'" class="text-2xl font-semibold text-slate-200">Subscription expired</h1>
            <p v-else-if="status === 'subscription_expired'" class="mt-3 text-sm leading-7 text-slate-400">
                <span v-if="subscription?.end_date">Your subscription ended on <strong>{{ formatDate(subscription.end_date) }}</strong>.</span>
                Please contact support to renew your plan and restore access.
            </p>

            <div v-else-if="status === 'suspended'" class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-500/15">
                <svg class="h-10 w-10 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/><path d="M4.93 4.93l14.14 14.14"/>
                </svg>
            </div>
            <h1 v-else-if="status === 'suspended'" class="text-2xl font-semibold text-slate-200">Account suspended</h1>
            <p v-else-if="status === 'suspended'" class="mt-3 text-sm leading-7 text-slate-400">
                Your account has been suspended. Please contact support to resolve this.
            </p>

            <template v-else>
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-slate-500/15">
                    <svg class="h-10 w-10 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-semibold text-slate-200">Account inactive</h1>
                <p class="mt-3 text-sm leading-7 text-slate-400">
                    Your account is no longer active. Please contact support for assistance.
                </p>
            </template>

            <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-5">
                <p class="text-sm text-slate-400">
                    Contact GymNanba support at
                    <a href="mailto:support@gymos.in" class="font-medium underline text-orange-400">support@gymos.in</a>
                </p>
            </div>

            <form @submit.prevent="logout" class="mt-6">
                <button type="submit" class="text-sm underline text-slate-400">Sign out</button>
            </form>
        </div>
    </div>
</template>
