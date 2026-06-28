<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    title: String,
});

const theme = ref(localStorage.getItem('gymos-theme') || 'dark');

onMounted(() => {
    document.documentElement.dataset.theme = theme.value;
});

const toggleTheme = () => {
    theme.value = theme.value === 'light' ? 'dark' : 'light';
    document.documentElement.dataset.theme = theme.value;
    localStorage.setItem('gymos-theme', theme.value);
};
</script>

<template>
    <div class="min-h-screen bg-slate-900 text-slate-100">
        <Head :title="`${title} | GymNanba`" />
        
        <header class="sticky top-0 z-30 border-b border-white/10 bg-slate-900/80 px-4 py-4 backdrop-blur lg:px-6">
            <div class="flex w-full items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl overflow-hidden bg-orange-500/20">
                        <span class="text-orange-300 font-bold text-lg">GN</span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.42em] text-orange-400">GymNanba</p>
                        <h1 class="mt-1 text-lg font-semibold">Portal</h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="/" class="text-sm text-slate-400 transition hover:opacity-80">Back to home</a>
                    <button @click="toggleTheme" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-slate-800 px-3 py-2 text-sm text-slate-400 transition hover:opacity-90">
                        <svg v-if="theme === 'dark'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
                        <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="4"/><path d="M12 2v2.5M12 19.5V22M4.93 4.93l1.77 1.77M17.3 17.3l1.77 1.77M2 12h2.5M19.5 12H22M4.93 19.07 6.7 17.3M17.3 6.7l1.77-1.77"/></svg>
                    </button>
                </div>
            </div>
        </header>

        <main class="flex min-h-[calc(100vh-76px)] w-full items-center px-4 py-8 lg:px-8">
            <slot />
        </main>
    </div>
</template>
