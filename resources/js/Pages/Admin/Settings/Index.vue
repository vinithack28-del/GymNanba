<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    activeSessions: Number,
    languages: Array,
});

const toggleLanguage = (language) => {
    useForm({
        is_active: language.is_active ? 0 : 1,
    }).patch(`/admin/settings/languages/${language.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Settings" />
        
        <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <section class="space-y-6">
                <article class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                    <p class="text-sm text-slate-400">Security posture</p>
                    <h3 class="mt-2 text-2xl font-semibold">Current authentication stack</h3>
                    <ul class="mt-5 space-y-3 text-sm leading-7 text-slate-300">
                        <li>Email + password via Laravel web session authentication.</li>
                        <li>CSRF-protected login and logout requests.</li>
                        <li>Protected admin routes under `/admin/*`.</li>
                        <li>Active signed-in superadmin shown in the portal shell.</li>
                        <li>Audit log entries recorded for login, logout, and admin actions.</li>
                    </ul>
                </article>

                <article class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                    <p class="text-sm text-slate-400">Sessions</p>
                    <h3 class="mt-2 text-2xl font-semibold">{{ activeSessions }} active session{{ activeSessions !== 1 ? 's' : '' }}</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-300">
                        Phase 1 session listing, forced 2FA, IP whitelist enforcement, and recovery-code flows are the next security-specific chunk.
                    </p>
                </article>
            </section>

            <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-400">Language registry</p>
                        <h3 class="mt-2 text-2xl font-semibold">Platform languages</h3>
                    </div>
                    <span class="rounded-full bg-slate-950/70 px-3 py-1 text-xs text-slate-300">Enable only if completeness ≥ 90%</span>
                </div>

                <div class="mt-6 space-y-4">
                    <form v-for="language in languages" :key="language.id" @submit.prevent="toggleLanguage(language)" class="rounded-[1.5rem] border border-white/10 bg-slate-950/50 p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="flex items-center gap-3">
                                    <p class="text-lg font-semibold">{{ language.display_name }}</p>
                                    <span class="rounded-full px-3 py-1 text-xs uppercase tracking-[0.2em]" :class="language.is_active ? 'bg-emerald-500/15 text-emerald-300' : 'bg-slate-500/15 text-slate-300'">
                                        {{ language.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-slate-400">{{ language.locale_code }} · Completeness {{ language.completeness_pct }}%</p>
                            </div>

                            <button
                                type="submit"
                                :class="['rounded-full px-4 py-2 text-sm font-semibold transition', language.is_active ? 'bg-slate-700 text-white hover:bg-slate-600' : 'bg-orange-500 text-slate-950 hover:bg-orange-400']"
                            >
                                {{ language.is_active ? 'Disable' : 'Enable' }}
                            </button>
                        </div>

                        <div class="mt-4 h-2 rounded-full bg-white/10">
                            <div class="h-2 rounded-full" :class="language.completeness_pct >= 90 ? 'bg-emerald-400' : 'bg-amber-400'" :style="{ width: Math.min(100, language.completeness_pct) + '%' }"></div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </AppLayout>
</template>