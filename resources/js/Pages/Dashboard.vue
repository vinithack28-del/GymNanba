<script setup>
import AppLayout from '../Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    auth: Object,
});

const form = useForm({});

const logout = () => {
    form.post('/logout');
};
</script>

<template>
    <AppLayout>
        <main class="min-h-screen bg-[radial-gradient(circle_at_top,#1f2937_0%,#0f172a_45%,#020617_100%)] text-white">
            <div class="mx-auto max-w-7xl px-6 py-8 lg:px-10">
                <header class="flex flex-col gap-4 rounded-[2rem] border border-white/10 bg-white/5 p-6 backdrop-blur sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.4em] text-emerald-300">GymNanba Dashboard</p>
                        <h1 class="mt-3 text-4xl font-semibold tracking-tight">Welcome, {{ auth.user.name }}</h1>
                        <p class="mt-2 text-slate-300">You are signed in with Laravel session-based authentication.</p>
                    </div>

                    <form @submit.prevent="logout">
                        <button
                            type="submit"
                            class="rounded-full bg-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-orange-400"
                        >
                            Logout
                        </button>
                    </form>
                </header>

                <section class="mt-8 grid gap-5 md:grid-cols-3">
                    <article class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 backdrop-blur">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-orange-300">Authenticated User</p>
                        <p class="mt-4 text-2xl font-semibold">{{ auth.user.name }}</p>
                        <p class="mt-2 break-all text-sm text-slate-300">{{ auth.user.email }}</p>
                    </article>
                    <article class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 backdrop-blur">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-sky-300">Access Type</p>
                        <p class="mt-4 text-2xl font-semibold">Web Session</p>
                        <p class="mt-2 text-sm text-slate-300">Guard: <span class="font-mono">session</span></p>
                    </article>
                    <article class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 backdrop-blur">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-300">Next Step</p>
                        <p class="mt-4 text-2xl font-semibold">Build role-based modules</p>
                        <p class="mt-2 text-sm text-slate-300">Superadmin, admin, trainer, receptionist, and member areas can branch from here.</p>
                    </article>
                </section>
            </div>
        </main>
    </AppLayout>
</template>
