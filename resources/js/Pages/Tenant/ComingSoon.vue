<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    pageTitle: String,
    tenant: Object,
});
</script>

<template>
    <AppLayout>
        <Head :title="pageTitle" />
        
        <div class="flex flex-col gap-5">
            <div>
                <h1 class="mt-2 text-3xl font-semibold">{{ pageTitle }}</h1>
                <p class="mt-1 text-slate-300">This module has not been built yet. The menu is ready, and this screen will be replaced when we develop this area.</p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-8">
                <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-orange-400">Coming soon</p>
                        <h3 class="mt-3 text-2xl font-semibold">{{ pageTitle }}</h3>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-400">
                            {{ tenant?.gym_name ?? 'Your gym workspace' }} will use this page once this module is implemented.
                        </p>
                    </div>
                    <Link
                        href="/tenant/dashboard"
                        class="rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-orange-400"
                    >
                        Back to dashboard
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

