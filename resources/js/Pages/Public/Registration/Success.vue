<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    tenant: Object,
});
</script>

<template>
    <Head :title="`Registration Submitted â€” ${tenant.gym_name}`" />
    
    <div class="flex min-h-screen items-center justify-center bg-slate-100 p-6">
        <div class="w-full max-w-[460px] rounded-3xl bg-white p-10 text-center shadow-xl">
            <div class="mx-auto mb-6 flex h-18 w-18 items-center justify-center rounded-full bg-emerald-500/10">
                <svg class="h-9 w-9 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-slate-900">Registration Submitted!</h1>
            <p class="mt-3 text-base leading-relaxed text-slate-600">
                Your details have been received. The team at <strong>{{ tenant.gym_name }}</strong> will review your registration and confirm your membership soon.
            </p>

            <div class="my-7 rounded-xl bg-slate-50 p-5 text-left">
                <div class="flex items-start gap-3 text-sm text-slate-700 leading-relaxed">
                    <span class="flex h-5.5 w-5.5 shrink-0 items-center justify-center rounded-full bg-orange-500 text-xs font-bold text-white">1</span>
                    <span>Your registration has been saved and is pending review.</span>
                </div>
                <div class="mt-3 flex items-start gap-3 text-sm text-slate-700 leading-relaxed">
                    <span class="flex h-5.5 w-5.5 shrink-0 items-center justify-center rounded-full bg-orange-500 text-xs font-bold text-white">2</span>
                    <span>A staff member will verify your details and assign a membership plan.</span>
                </div>
                <div class="mt-3 flex items-start gap-3 text-sm text-slate-700 leading-relaxed">
                    <span class="flex h-5.5 w-5.5 shrink-0 items-center justify-center rounded-full bg-orange-500 text-xs font-bold text-white">3</span>
                    <span>Once confirmed, you'll be officially enrolled as a member.</span>
                </div>
            </div>

            <p class="text-sm text-slate-400">Feel free to visit {{ tenant.gym_name }} for any queries or follow-up.</p>

            <span class="mt-6 inline-block text-xs font-semibold uppercase tracking-widest text-slate-400">{{ tenant.gym_name }}</span>
        </div>
    </div>
</template>
