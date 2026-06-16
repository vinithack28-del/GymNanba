<x-layouts.admin
    title="Dashboard"
    eyebrow="Gym Workspace"
    heading="{{ $tenant?->gym_name ?? 'Gym Dashboard' }}"
    subheading="Owner navigation is now isolated from the super admin area. Use the sidebar to move between modules as they are built."
>
    <div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
        <section class="app-panel rounded-[2rem] border p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-[var(--app-info)]">Owner workspace</p>
            <h3 class="mt-4 text-2xl font-semibold">Gym dashboard shell is ready.</h3>
            <p class="app-muted mt-4 max-w-2xl text-sm leading-7">
                The sidebar now follows the gym-owner structure. Each menu currently opens a shared coming-soon page so we can build the modules one by one without changing the navigation again.
            </p>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="app-panel-strong rounded-2xl border p-4">
                    <p class="app-muted text-xs uppercase tracking-[0.24em]">Login</p>
                    <p class="mt-2 font-semibold">{{ auth()->user()?->email }}</p>
                </div>
                <div class="app-panel-strong rounded-2xl border p-4">
                    <p class="app-muted text-xs uppercase tracking-[0.24em]">Domain mode</p>
                    <p class="mt-2 font-semibold uppercase">{{ $tenant?->domain_mode ?? 'shared' }}</p>
                </div>
                <div class="app-panel-strong rounded-2xl border p-4">
                    <p class="app-muted text-xs uppercase tracking-[0.24em]">Database mode</p>
                    <p class="mt-2 font-semibold uppercase">{{ $tenant?->database_mode ?? 'shared' }}</p>
                </div>
            </div>
        </section>

        <section class="app-panel rounded-[2rem] border p-6">
            <h3 class="text-xl font-semibold">Profile snapshot</h3>
            <div class="mt-5 space-y-4 text-sm">
                <div>
                    <p class="app-muted uppercase tracking-[0.24em] text-xs">Owner</p>
                    <p class="mt-2 font-semibold">{{ $tenant?->owner_name }}</p>
                </div>
                <div>
                    <p class="app-muted uppercase tracking-[0.24em] text-xs">Business type</p>
                    <p class="mt-2 font-semibold">{{ $tenant?->business_type }}</p>
                </div>
                <div>
                    <p class="app-muted uppercase tracking-[0.24em] text-xs">Primary domain</p>
                    <p class="mt-2 font-semibold">{{ $tenant?->primary_domain }}</p>
                </div>
            </div>
        </section>
    </div>
</x-layouts.admin>
