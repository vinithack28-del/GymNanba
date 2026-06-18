<x-layouts.admin :title="__('reports.nav.reports')">

<div class="mb-6">
    <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('reports.nav.reports') }}</h1>
    <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('reports.index.subtitle') }}</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-3xl">

    {{-- Revenue --}}
    <div class="rounded-2xl p-6 flex flex-col gap-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl flex-none" style="background:color-mix(in srgb,var(--app-brand) 15%,transparent)">
                <svg class="h-5 w-5" style="color:var(--app-brand)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M16 12h.01"/><path d="M3 9h18"/></svg>
            </span>
            <div>
                <h2 class="font-semibold text-sm" style="color:var(--app-text)">{{ __('reports.nav.revenue') }}</h2>
                <p class="text-xs mt-0.5" style="color:var(--app-text-muted)">{{ __('reports.index.revenue_desc') }}</p>
            </div>
        </div>
        @if ($canRevenue)
            <a href="{{ route('tenant.reports.revenue') }}"
               class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-medium text-white"
               style="background:var(--app-brand)">
                {{ __('reports.index.open') }} →
            </a>
        @else
            <span class="mt-auto text-xs" style="color:var(--app-text-muted)">{{ __('reports.index.no_access') }}</span>
        @endif
    </div>

    {{-- Members --}}
    <div class="rounded-2xl p-6 flex flex-col gap-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl flex-none" style="background:color-mix(in srgb,var(--app-brand) 15%,transparent)">
                <svg class="h-5 w-5" style="color:var(--app-brand)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="3"/><path d="M20 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 4.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div>
                <h2 class="font-semibold text-sm" style="color:var(--app-text)">{{ __('reports.nav.members') }}</h2>
                <p class="text-xs mt-0.5" style="color:var(--app-text-muted)">{{ __('reports.index.members_desc') }}</p>
            </div>
        </div>
        @if ($canMembers)
            <a href="{{ route('tenant.reports.members') }}"
               class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-medium text-white"
               style="background:var(--app-brand)">
                {{ __('reports.index.open') }} →
            </a>
        @else
            <span class="mt-auto text-xs" style="color:var(--app-text-muted)">{{ __('reports.index.no_access') }}</span>
        @endif
    </div>

    {{-- Attendance --}}
    <div class="rounded-2xl p-6 flex flex-col gap-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl flex-none" style="background:color-mix(in srgb,var(--app-brand) 15%,transparent)">
                <svg class="h-5 w-5" style="color:var(--app-brand)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7V5a1 1 0 0 1 1-1h2"/><path d="M17 4h2a1 1 0 0 1 1 1v2"/><path d="M20 17v2a1 1 0 0 1-1 1h-2"/><path d="M7 20H5a1 1 0 0 1-1-1v-2"/><path d="M7 12h10"/></svg>
            </span>
            <div>
                <h2 class="font-semibold text-sm" style="color:var(--app-text)">{{ __('reports.nav.attendance') }}</h2>
                <p class="text-xs mt-0.5" style="color:var(--app-text-muted)">{{ __('reports.index.attendance_desc') }}</p>
            </div>
        </div>
        @if ($canAttendance)
            <a href="{{ route('tenant.reports.attendance') }}"
               class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-medium text-white"
               style="background:var(--app-brand)">
                {{ __('reports.index.open') }} →
            </a>
        @else
            <span class="mt-auto text-xs" style="color:var(--app-text-muted)">{{ __('reports.index.no_access') }}</span>
        @endif
    </div>

    {{-- Staff --}}
    <div class="rounded-2xl p-6 flex flex-col gap-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl flex-none" style="background:color-mix(in srgb,var(--app-brand) 15%,transparent)">
                <svg class="h-5 w-5" style="color:var(--app-brand)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="7" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M14 20a5 5 0 0 1 7 0"/></svg>
            </span>
            <div>
                <h2 class="font-semibold text-sm" style="color:var(--app-text)">{{ __('reports.nav.staff') }}</h2>
                <p class="text-xs mt-0.5" style="color:var(--app-text-muted)">{{ __('reports.index.staff_desc') }}</p>
            </div>
        </div>
        @if ($canStaff)
            <a href="{{ route('tenant.reports.staff') }}"
               class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-medium text-white"
               style="background:var(--app-brand)">
                {{ __('reports.index.open') }} →
            </a>
        @else
            <span class="mt-auto text-xs" style="color:var(--app-text-muted)">{{ __('reports.index.no_access') }}</span>
        @endif
    </div>

</div>

</x-layouts.admin>
