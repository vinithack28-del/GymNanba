{{-- Settings horizontal tab nav --}}
@php
    $navItems = [
        ['route' => 'tenant.settings.profile',      'label' => __('settings.nav.profile')],
        ['route' => 'tenant.settings.account',      'label' => __('settings.nav.account')],
        ['route' => 'tenant.settings.integrations', 'label' => __('settings.nav.integrations')],
        ['route' => 'tenant.settings.language',     'label' => __('settings.nav.language')],
        ['route' => 'tenant.settings.subscription', 'label' => __('settings.nav.subscription')],
        ['route' => 'tenant.settings.data',         'label' => __('settings.nav.data')],
    ];
@endphp

<div class="overflow-x-auto -mx-1 mb-6">
    <nav class="flex gap-1 min-w-max px-1">
        @foreach ($navItems as $item)
            @php $active = request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}"
               class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors"
               style="{{ $active
                   ? 'background:color-mix(in srgb,var(--app-brand) 12%,transparent);color:var(--app-brand)'
                   : 'color:var(--app-text-muted)' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</div>
