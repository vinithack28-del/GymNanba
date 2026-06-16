<x-layouts.admin
    title="Tenants"
    eyebrow="{{ __('tenants.eyebrow') }}"
    heading="{{ __('tenants.heading') }}"
    subheading="{{ __('tenants.subheading') }}"
>
    <x-slot:headerAction>
        <a href="{{ route('admin.tenants.create') }}" class="rounded-full bg-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-orange-400">
            {{ __('tenants.add_new') }}
        </a>
    </x-slot:headerAction>

    <form method="GET" class="grid gap-4 rounded-[2rem] border border-white/10 bg-white/5 p-5 lg:grid-cols-4">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="{{ __('tenants.filters.search') }}"
            class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-white outline-none placeholder:text-slate-500"
        >
        <select name="status" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-white outline-none">
            <option value="">{{ __('tenants.filters.all_statuses') }}</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ __('common.statuses.'.$status) }}</option>
            @endforeach
        </select>
        <select name="business_type" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-white outline-none">
            <option value="">{{ __('tenants.filters.all_business_types') }}</option>
            @foreach ($businessTypes as $type)
                <option value="{{ $type }}" @selected(request('business_type') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-slate-950 hover:bg-sky-300">
            {{ __('tenants.filters.apply') }}
        </button>
    </form>

    <div class="mt-6 overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
        <table class="w-full divide-y divide-white/10 text-left text-sm">
            <thead class="bg-slate-950/60 text-slate-300">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('tenants.table.gym') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('tenants.table.owner') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('tenants.table.subdomain') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('tenants.table.plan') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('common.status') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('tenants.table.members') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('tenants.table.created') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('tenants.table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10 bg-white/5">
                @forelse ($tenants as $tenant)
                    @php
                        $planName = $tenant->subscriptions->sortByDesc('id')->first()?->plan?->name ?? 'Unassigned';
                        $statusColors = [
                            'active' => 'bg-emerald-500/15 text-emerald-300',
                            'trial' => 'bg-sky-500/15 text-sky-300',
                            'suspended' => 'bg-red-500/15 text-red-300',
                            'archived' => 'bg-slate-500/15 text-slate-300',
                        ];
                    @endphp
                    <tr>
                        <td class="px-4 py-4">
                            <p class="font-semibold">{{ $tenant->gym_name }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $tenant->business_type }} · {{ $tenant->city }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <p>{{ $tenant->owner_name }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $tenant->owner_email }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <span class="rounded-full bg-slate-950/60 px-3 py-1 text-xs">{{ $tenant->subdomain }}.gymos.in</span>
                        </td>
                        <td class="px-4 py-4">{{ $planName }}</td>
                        <td class="px-4 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $statusColors[$tenant->status] ?? 'bg-white/10 text-white' }}">
                                {{ __('common.statuses.'.$tenant->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-4">{{ $tenant->members_count }}</td>
                        <td class="px-4 py-4">{{ $tenant->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap gap-2">
                                <a
                                    href="{{ route('admin.tenants.show', $tenant) }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-sky-500/15 text-sky-300 transition hover:bg-sky-500/25"
                                    title="{{ __('common.view_details') }}"
                                    aria-label="{{ __('common.view_details') }} {{ $tenant->gym_name }}"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M2.06 12.35a1 1 0 0 1 0-.7C3.76 7.2 7.52 4 12 4s8.24 3.2 9.94 7.65a1 1 0 0 1 0 .7C20.24 16.8 16.48 20 12 20S3.76 16.8 2.06 12.35Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                                <a
                                    href="{{ route('admin.tenants.edit', $tenant) }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/15 text-amber-300 transition hover:bg-amber-500/25"
                                    title="{{ __('common.edit') }}"
                                    aria-label="{{ __('common.edit') }} {{ $tenant->gym_name }}"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"></path>
                                    </svg>
                                </a>
                                <a
                                    href="{{ route('admin.tenants.delete', $tenant) }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-red-500/15 text-red-300 transition hover:bg-red-500/25"
                                    title="{{ __('common.delete') }}"
                                    aria-label="{{ __('common.delete') }} {{ $tenant->gym_name }}"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 6h18"></path>
                                        <path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"></path>
                                        <path d="M19 6l-1 14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1L5 6"></path>
                                        <path d="M10 11v6"></path>
                                        <path d="M14 11v6"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">{{ __('tenants.table.no_results') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tenants->links() }}
    </div>
</x-layouts.admin>
