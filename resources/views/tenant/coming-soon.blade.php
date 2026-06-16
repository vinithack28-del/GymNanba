<x-layouts.admin
    title="{{ $pageTitle }}"
    eyebrow="Gym Workspace"
    heading="{{ $pageTitle }}"
    subheading="This module has not been built yet. The menu is ready, and this screen will be replaced when we develop this area."
>
    <section class="app-panel rounded-[2rem] border p-8">
        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-[var(--app-info)]">Coming soon</p>
                <h3 class="mt-3 text-2xl font-semibold">{{ $pageTitle }}</h3>
                <p class="app-muted mt-3 max-w-2xl text-sm leading-7">
                    {{ $tenant?->gym_name ?? 'Your gym workspace' }} will use this page once this module is implemented.
                </p>
            </div>
            <a
                href="{{ route('tenant.dashboard') }}"
                class="rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-orange-400"
            >
                Back to dashboard
            </a>
        </div>
    </section>
</x-layouts.admin>
