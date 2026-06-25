<x-layouts.admin
    title="Tenants"
    eyebrow="{{ __('tenants.eyebrow') }}"
    heading="{{ __('tenants.heading') }}"
    subheading="{{ __('tenants.subheading') }}"
>
    <x-slot:headerAction>
        <a href="{{ route('admin.tenants.create') }}"
           style="display:inline-flex;align-items:center;gap:.4rem;background:var(--app-brand);color:#fff;border-radius:999px;padding:.55rem 1.1rem;font-size:.85rem;font-weight:700;text-decoration:none;transition:opacity .15s"
           onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
            <svg style="width:15px;height:15px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            {{ __('tenants.add_new') }}
        </a>
    </x-slot:headerAction>

@push('styles')
<style>
.tn-shell{display:flex;flex-direction:column;gap:1.25rem}
.tn-filter{background:var(--app-panel);border:1px solid var(--app-border);border-radius:1.5rem;padding:1.1rem 1.25rem;display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end}
.tn-input,.tn-select{border:1px solid var(--app-border);background:var(--app-panel-strong);color:var(--app-text);border-radius:.9rem;padding:.65rem 1rem;font-size:.85rem;outline:none;min-width:0}
.tn-input:focus,.tn-select:focus{border-color:var(--app-brand)}
.tn-btn{display:inline-flex;align-items:center;justify-content:center;border-radius:.9rem;padding:.65rem 1.2rem;font-size:.85rem;font-weight:700;cursor:pointer;border:none;transition:opacity .15s;white-space:nowrap}
.tn-btn:hover{opacity:.85}
.tn-btn-primary{background:var(--app-brand);color:#fff}
.tn-btn-clear{background:var(--app-panel-strong);color:var(--app-text);border:1px solid var(--app-border)}
.tn-panel{background:var(--app-panel);border:1px solid var(--app-border);border-radius:1.5rem;overflow:hidden}
.tn-table-wrap{overflow-x:auto}
.tn-table{width:100%;border-collapse:collapse;font-size:.875rem}
.tn-table th{padding:.75rem 1rem;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--app-text-muted);background:var(--app-panel-strong);text-align:left;white-space:nowrap;border-bottom:1px solid var(--app-border)}
.tn-table td{padding:.9rem 1rem;border-top:1px solid var(--app-border);color:var(--app-text);vertical-align:middle}
.tn-table tr:hover td{background:color-mix(in srgb,var(--app-brand) 4%,transparent)}
.tn-badge{display:inline-flex;align-items:center;padding:.18rem .6rem;border-radius:999px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap}
.tn-badge-green{background:color-mix(in srgb,#22c55e 15%,transparent);color:#16a34a}
.tn-badge-sky{background:color-mix(in srgb,#38bdf8 15%,transparent);color:#0284c7}
.tn-badge-amber{background:color-mix(in srgb,#f59e0b 15%,transparent);color:#b45309}
.tn-badge-red{background:color-mix(in srgb,#ef4444 15%,transparent);color:#dc2626}
.tn-badge-slate{background:color-mix(in srgb,#94a3b8 15%,transparent);color:#64748b}
.tn-badge-orange{background:color-mix(in srgb,#f97316 15%,transparent);color:#c2410c}
.tn-sub{font-size:.78rem;color:var(--app-text-muted);margin-top:.15rem}
.tn-domain{font-size:.78rem;font-family:monospace;background:var(--app-panel-strong);border:1px solid var(--app-border);border-radius:.5rem;padding:.15rem .5rem;color:var(--app-text-muted)}
.tn-icon-btn{display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;border-radius:.5rem;transition:background .14s}
[data-theme='light'] .tn-badge-green{background:#dcfce7;color:#166534}
[data-theme='light'] .tn-badge-sky{background:#e0f2fe;color:#0369a1}
[data-theme='light'] .tn-badge-amber{background:#fef3c7;color:#92400e}
[data-theme='light'] .tn-badge-red{background:#fee2e2;color:#991b1b}
[data-theme='light'] .tn-badge-slate{background:#f1f5f9;color:#475569}
[data-theme='light'] .tn-badge-orange{background:#ffedd5;color:#c2410c}
</style>
@endpush

<div class="tn-shell">

    {{-- Flash --}}
    @if (session('status'))
        <div style="background:color-mix(in srgb,#22c55e 12%,transparent);border:1px solid color-mix(in srgb,#22c55e 30%,transparent);border-radius:1rem;color:#16a34a;font-size:.85rem;padding:.8rem 1rem">
            {{ session('status') }}
        </div>
    @endif

    {{-- Filter bar --}}
    <form method="GET" class="tn-filter">
        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="{{ __('tenants.filters.search') }}" class="tn-input" style="flex:1;min-width:200px">
        <select name="status" class="tn-select">
            <option value="">{{ __('tenants.filters.all_statuses') }}</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>
                    {{ __('common.statuses.'.$status) }}
                </option>
            @endforeach
        </select>
        <select name="business_type" class="tn-select">
            <option value="">{{ __('tenants.filters.all_business_types') }}</option>
            @foreach ($businessTypes as $type)
                <option value="{{ $type }}" @selected(request('business_type') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <button type="submit" class="tn-btn tn-btn-primary">{{ __('tenants.filters.apply') }}</button>
        @if (request()->hasAny(['search', 'status', 'business_type']))
            <a href="{{ route('admin.tenants.index') }}" class="tn-btn tn-btn-clear" style="text-decoration:none">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="tn-panel">
        <div class="tn-table-wrap">
            <table class="tn-table">
                <thead>
                    <tr>
                        <th>{{ __('tenants.table.gym') }}</th>
                        <th>{{ __('tenants.table.owner') }}</th>
                        <th>{{ __('tenants.table.subdomain') }}</th>
                        <th>{{ __('tenants.table.plan') }}</th>
                        <th>Expiry</th>
                        <th>{{ __('common.status') }}</th>
                        <th>{{ __('tenants.table.members') }}</th>
                        <th>{{ __('tenants.table.created') }}</th>
                        <th>{{ __('tenants.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenants as $tenant)
                        @php
                            $latestSub  = $tenant->subscriptions->sortByDesc('id')->first();
                            $planName   = $latestSub?->plan?->name ?? '—';
                            $subStatus  = $latestSub?->status;
                            $expDate    = $latestSub?->end_date ?? $latestSub?->trial_end_date;
                            $daysLeft   = $expDate ? (int) now()->diffInDays($expDate, false) : null;
                            $badgeClass = match($tenant->status) {
                                'active'               => 'tn-badge-green',
                                'trial'                => 'tn-badge-sky',
                                'trial_ended'          => 'tn-badge-amber',
                                'subscription_expired' => 'tn-badge-orange',
                                'suspended'            => 'tn-badge-red',
                                default                => 'tn-badge-slate',
                            };
                        @endphp
                        <tr>
                            <td>
                                <p style="font-weight:700;color:var(--app-text)">{{ $tenant->gym_name }}</p>
                                <p class="tn-sub">{{ $tenant->business_type }} · {{ $tenant->city }}</p>
                            </td>
                            <td>
                                <p style="color:var(--app-text)">{{ $tenant->owner_name }}</p>
                                <p class="tn-sub">{{ $tenant->owner_email }}</p>
                            </td>
                            <td>
                                <span class="tn-domain">{{ $tenant->subdomain }}.gymos.in</span>
                            </td>
                            <td>
                                <p style="color:var(--app-text)">{{ $planName }}</p>
                                @if ($subStatus === 'partial')
                                    <p class="tn-sub" style="color:#b45309">Part paid</p>
                                @endif
                            </td>
                            <td>
                                @if ($expDate)
                                    <p style="color:var(--app-text);font-size:.85rem">{{ $expDate->format('d M Y') }}</p>
                                    <p class="tn-sub" style="color:{{ $daysLeft < 0 ? '#dc2626' : ($daysLeft <= 7 ? '#b45309' : ($daysLeft <= 30 ? '#0284c7' : 'var(--app-text-muted)')) }}">
                                        {{ $daysLeft < 0 ? abs($daysLeft).'d ago' : ($daysLeft === 0 ? 'Today' : $daysLeft.'d left') }}
                                    </p>
                                @else
                                    <span style="color:var(--app-text-muted)">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="tn-badge {{ $badgeClass }}">
                                    {{ __('common.statuses.'.$tenant->status) }}
                                </span>
                            </td>
                            <td style="color:var(--app-text)">{{ number_format($tenant->members_count) }}</td>
                            <td style="color:var(--app-text-muted);font-size:.82rem">{{ $tenant->created_at->format('d M Y') }}</td>
                            <td>
                                <div style="display:inline-flex;align-items:center;gap:.3rem">
                                    <a href="{{ route('admin.tenants.show', $tenant) }}"
                                       class="tn-icon-btn"
                                       style="background:color-mix(in srgb,#38bdf8 12%,transparent);color:#0284c7"
                                       title="{{ __('common.view_details') }}">
                                        <svg style="width:15px;height:15px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.06 12.35a1 1 0 0 1 0-.7C3.76 7.2 7.52 4 12 4s8.24 3.2 9.94 7.65a1 1 0 0 1 0 .7C20.24 16.8 16.48 20 12 20S3.76 16.8 2.06 12.35Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="{{ route('admin.tenants.edit', $tenant) }}"
                                       class="tn-icon-btn"
                                       style="background:color-mix(in srgb,#f59e0b 12%,transparent);color:#b45309"
                                       title="{{ __('common.edit') }}">
                                        <svg style="width:15px;height:15px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.tenants.delete', $tenant) }}"
                                       class="tn-icon-btn"
                                       style="background:color-mix(in srgb,#ef4444 12%,transparent);color:#dc2626"
                                       title="{{ __('common.delete') }}">
                                        <svg style="width:15px;height:15px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:2.5rem;color:var(--app-text-muted)">
                                {{ __('tenants.table.no_results') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination bar --}}
    @if ($tenants->isNotEmpty())
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;
                background:var(--app-panel);border:1px solid var(--app-border);border-radius:1.5rem;padding:.75rem 1.25rem">
        <p style="font-size:.78rem;color:var(--app-text-muted)">
            Showing {{ $tenants->firstItem() }} to {{ $tenants->lastItem() }} of {{ number_format($tenants->total()) }} tenants
        </p>
        <div style="display:flex;align-items:center;gap:.75rem">
            <select class="tn-select" style="font-size:.78rem;padding:.4rem .75rem"
                    onchange="window.location='{{ route('admin.tenants.index') }}?'+new URLSearchParams({...Object.fromEntries(new URLSearchParams(window.location.search)),...{per_page:this.value,page:1}}).toString()">
                @foreach ([10, 25, 50, 100] as $pp)
                    <option value="{{ $pp }}" @selected($tenants->perPage() === $pp)>{{ $pp }} / page</option>
                @endforeach
            </select>
            {{ $tenants->links() }}
        </div>
    </div>
    @endif

</div>
</x-layouts.admin>
