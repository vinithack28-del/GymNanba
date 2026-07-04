<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';

const props = defineProps({
    title: String,
});

const theme = ref(localStorage.getItem('gymos-theme') || 'dark');
const page = usePage();
const translations = computed(() => page.props.translations?.common || {});
const portalLanguages = computed(() => page.props.portalLanguages || []);
const localeForm = useForm({
    locale_code: page.props.locale || 'en-IN',
});

const t = (key, fallback) => {
    return key.split('.').reduce((value, part) => value?.[part], translations.value) || fallback;
};

onMounted(() => {
    document.documentElement.dataset.theme = theme.value;
});

const toggleTheme = () => {
    theme.value = theme.value === 'light' ? 'dark' : 'light';
    document.documentElement.dataset.theme = theme.value;
    localStorage.setItem('gymos-theme', theme.value);
};

const updateLanguage = () => {
    localeForm.post('/language', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="min-h-screen app-theme-shell">
        <Head :title="`${title} | GymNanba`" />
        
        <header class="sticky top-0 z-30 border-b app-topbar px-4 py-3 backdrop-blur lg:px-5 lg:py-3.5">
            <div class="flex w-full items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-orange-500/20 lg:h-11 lg:w-11">
                        <span class="text-base font-bold text-orange-300 lg:text-lg">GN</span>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-orange-400 sm:text-xs sm:tracking-[0.42em]">GymNanba</p>
                        <h1 class="mt-0.5 text-base font-semibold lg:text-lg">{{ t('auth.portal', 'Portal') }}</h1>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <form v-if="portalLanguages.length > 0" @submit.prevent="updateLanguage" class="hidden sm:block">
                        <label class="sr-only" for="auth-locale">{{ t('language_selector', 'Portal language') }}</label>
                        <select
                            id="auth-locale"
                            v-model="localeForm.locale_code"
                            @change="updateLanguage"
                            class="h-9 rounded-lg border app-panel px-2.5 text-sm outline-none"
                        >
                            <option v-for="language in portalLanguages" :key="language.locale_code" :value="language.locale_code">
                                {{ language.display_name }}
                            </option>
                        </select>
                    </form>
                    <a href="/" class="text-sm text-slate-400 transition hover:opacity-80">{{ t('auth.back_to_home', 'Back to home') }}</a>
                    <button @click="toggleTheme" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-slate-800 px-3 py-2 text-sm text-slate-400 transition hover:opacity-90">
                        <svg v-if="theme === 'dark'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
                        <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="4"/><path d="M12 2v2.5M12 19.5V22M4.93 4.93l1.77 1.77M17.3 17.3l1.77 1.77M2 12h2.5M19.5 12H22M4.93 19.07 6.7 17.3M17.3 6.7l1.77-1.77"/></svg>
                    </button>
                </div>
            </div>
        </header>

        <main class="flex min-h-[calc(100vh-65px)] w-full items-start overflow-auto px-4 py-3 sm:py-4 lg:min-h-[calc(100vh-72px)] lg:px-6 lg:py-5">
            <slot />
        </main>
    </div>
</template>

