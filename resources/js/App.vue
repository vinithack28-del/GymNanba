<template>
    <main class="min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top,#202a44_0%,#0f172a_45%,#020617_100%)] text-white">
        <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(249,115,22,0.18),transparent_35%,transparent_65%,rgba(16,185,129,0.12))]"></div>
        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-8 lg:px-10">
            <header class="flex items-center justify-between rounded-full border border-white/10 bg-white/5 px-5 py-3 backdrop-blur">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.4em] text-orange-300">GymNanba</p>
                    <p class="mt-1 text-sm text-slate-300">Strength. Routine. Results.</p>
                </div>
                <a
                    href="/login"
                    class="rounded-full bg-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-orange-400"
                >
                    Sign in
                </a>
            </header>

            <section class="grid flex-1 items-center gap-16 py-16 lg:grid-cols-[1.15fr_0.85fr] lg:py-20">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.45em] text-emerald-300">
                        Premium Fitness Platform
                    </p>
                    <h1 class="mt-6 max-w-4xl text-5xl font-semibold tracking-tight text-white sm:text-6xl lg:text-7xl">
                        Build a stronger gym brand with a landing page that actually converts.
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                        Showcase coaching, memberships, class schedules, and personal transformation with a sharper
                        digital front door for your gym.
                    </p>

                    <div class="mt-10 flex flex-wrap gap-4">
                        <a
                            href="/login"
                            class="rounded-full bg-emerald-400 px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-300"
                        >
                            Access Portal
                        </a>
                        <a
                            href="#programs"
                            class="rounded-full border border-white/15 bg-white/5 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                        >
                            Explore Programs
                        </a>
                    </div>

                    <div class="mt-12 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <p class="text-3xl font-semibold text-orange-300">12+</p>
                            <p class="mt-2 text-sm text-slate-300">Coaching tracks from fat loss to strength cycles.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <p class="text-3xl font-semibold text-emerald-300">24/7</p>
                            <p class="mt-2 text-sm text-slate-300">Always-on access to schedules, plans, and updates.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <p class="text-3xl font-semibold text-sky-300">1 App</p>
                            <p class="mt-2 text-sm text-slate-300">One place for superadmin, admin, trainer, receptionist, and member access.</p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -inset-6 rounded-[2rem] bg-orange-500/10 blur-3xl"></div>
                    <div class="relative rounded-[2rem] border border-white/10 bg-slate-900/80 p-6 shadow-2xl shadow-black/40 backdrop-blur">
                        <div class="rounded-[1.5rem] border border-white/10 bg-[linear-gradient(160deg,#111827,#172033_55%,#1f2937)] p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.35em] text-orange-300">Today at GymNanba</p>
                                    <h2 class="mt-2 text-2xl font-semibold">Train with intent.</h2>
                                </div>
                                <div class="rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-xs font-medium text-emerald-300">
                                    Live classes
                                </div>
                            </div>

                            <div class="mt-8 space-y-4">
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-slate-400">06:00 AM</p>
                                            <p class="mt-1 text-lg font-semibold">Athletic Conditioning</p>
                                        </div>
                                        <span class="rounded-full bg-orange-500/15 px-3 py-1 text-xs text-orange-200">28 spots</span>
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-slate-400">07:30 AM</p>
                                            <p class="mt-1 text-lg font-semibold">Strength Foundations</p>
                                        </div>
                                        <span class="rounded-full bg-sky-500/15 px-3 py-1 text-xs text-sky-200">Coach led</span>
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-slate-400">08:30 PM</p>
                                            <p class="mt-1 text-lg font-semibold">Recovery Mobility</p>
                                        </div>
                                        <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs text-emerald-200">New</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 rounded-2xl border border-white/10 bg-black/20 p-5">
                                <p class="text-sm text-slate-400">Member momentum</p>
                                <div class="mt-3 flex items-end gap-3">
                                    <p class="text-4xl font-semibold text-white">87%</p>
                                    <p class="pb-1 text-sm text-emerald-300">weekly attendance consistency</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="programs" class="grid gap-5 pb-10 md:grid-cols-3">
                <article class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 backdrop-blur">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-orange-300">Strength</p>
                    <h3 class="mt-4 text-2xl font-semibold">Powerlifting and muscle-building blocks.</h3>
                    <p class="mt-4 text-sm leading-7 text-slate-300">
                        Structured programming for progressive overload, movement quality, and visible results.
                    </p>
                </article>
                <article class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 backdrop-blur">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-300">Conditioning</p>
                    <h3 class="mt-4 text-2xl font-semibold">High-energy sessions built for stamina.</h3>
                    <p class="mt-4 text-sm leading-7 text-slate-300">
                        Hybrid classes that blend endurance, functional training, and metabolic work.
                    </p>
                </article>
                <article class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 backdrop-blur">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-sky-300">Recovery</p>
                    <h3 class="mt-4 text-2xl font-semibold">Mobility and coaching that keep members consistent.</h3>
                    <p class="mt-4 text-sm leading-7 text-slate-300">
                        Recovery-focused sessions that reduce drop-off and support long-term retention.
                    </p>
                </article>
            </section>
        </div>
    </main>
</template>

