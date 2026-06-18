<x-layouts.admin
    title="{{ __('members.title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('members.title') }}"
>

{{-- ── Branch context chip ───────────────────────────────────────────────── --}}
@if (isset($selectedBranch) && $selectedBranch)
    <div class="mb-4 flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full border border-[var(--app-brand)]/30 bg-[var(--app-brand-soft)]/40 px-3 py-1 text-xs font-semibold text-[var(--app-brand)]">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M15 9h.01"/><path d="M15 13h.01"/></svg>
            {{ $selectedBranch->name }}
        </span>
        <span class="text-xs text-[var(--app-text-muted)]">{{ __('members.showing_branch') }}</span>
    </div>
@endif

{{-- ── Stats Bar ─────────────────────────────────────────────────────────── --}}
<div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
    @php
        $statCards = [
            ['label' => __('members.stats.total'),    'value' => $stats['total'],    'filter' => '',          'color' => 'var(--app-text)'],
            ['label' => __('members.stats.active'),   'value' => $stats['active'],   'filter' => 'active',    'color' => '#1D9E75'],
            ['label' => __('members.stats.inactive'), 'value' => $stats['inactive'], 'filter' => 'inactive',  'color' => '#888780'],
            ['label' => __('members.stats.expired'),  'value' => $stats['expired'],  'filter' => 'expired',   'color' => '#E24B4A'],
        ];
    @endphp
    @foreach ($statCards as $card)
        <a
            href="{{ route('tenant.members.index', array_merge(request()->query(), ['status' => $card['filter'], 'page' => 1])) }}"
            class="app-panel member-stat-card rounded-2xl border p-4 transition hover:opacity-90 {{ request('status') === $card['filter'] && $card['filter'] !== '' ? 'member-stat-active' : '' }}"
        >
            <p class="app-muted text-xs font-medium uppercase tracking-[0.2em]">{{ $card['label'] }}</p>
            <p class="mt-2 text-2xl font-semibold" style="color: {{ $card['color'] }}">{{ number_format($card['value']) }}</p>
        </a>
    @endforeach
</div>

{{-- ── Filters Bar ───────────────────────────────────────────────────────── --}}
<div class="app-panel mb-4 rounded-2xl border p-3">
    <form id="filter-form" method="GET" action="{{ route('tenant.members.index') }}">
        <div class="flex flex-wrap items-center gap-2">

            {{-- Search --}}
            <div class="member-search-wrap flex-1">
                <span class="member-search-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                </span>
                <input
                    id="search-input"
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="{{ __('members.filters.search_placeholder') }}"
                    class="member-search-input"
                    autocomplete="off"
                >
            </div>

            {{-- Status --}}
            <select name="status" onchange="this.form.submit()" class="member-filter-select">
                <option value="">{{ __('members.filters.all_statuses') }}</option>
                @foreach (['active', 'inactive', 'expired', 'frozen'] as $val)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ __('members.statuses.'.$val) }}</option>
                @endforeach
            </select>

            {{-- Gender --}}
            <select name="gender" onchange="this.form.submit()" class="member-filter-select">
                <option value="">{{ __('members.filters.all_genders') }}</option>
                @foreach (['male', 'female', 'other'] as $val)
                    <option value="{{ $val }}" @selected(request('gender') === $val)>{{ __('members.genders.'.$val) }}</option>
                @endforeach
            </select>

            {{-- Preserve sort --}}
            @if(request('sort_by'))  <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
            @if(request('sort_dir')) <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}"> @endif
            @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

            {{-- Actions --}}
            <div class="ml-auto flex items-center gap-2">
                @if(request()->hasAny(['search','status','gender']))
                    <a href="{{ route('tenant.members.index') }}" class="member-btn-ghost text-xs">{{ __('members.filters.clear_filters') }}</a>
                @endif
                <a href="{{ route('tenant.members.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="member-btn-ghost text-xs">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    {{ __('members.filters.export') }}
                </a>
                <a href="{{ route('tenant.members.create') }}" class="member-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    {{ __('members.add_member') }}
                </a>
            </div>
        </div>
    </form>
</div>

{{-- ── Members Table ─────────────────────────────────────────────────────── --}}
<div class="app-panel rounded-[2rem] border overflow-hidden">
    @if ($members->isEmpty())
        <div class="flex flex-col items-center gap-4 py-20 text-center">
            <div class="member-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="3"/><path d="M20 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 4.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            @if(request()->hasAny(['search','status','gender']))
                <p class="text-base font-semibold">{{ __('members.empty.no_match') }}</p>
                <p class="app-muted text-sm">{{ __('members.empty.try_adjusting') }}</p>
                <a href="{{ route('tenant.members.index') }}" class="member-btn-ghost mt-2">{{ __('members.empty.clear_all') }}</a>
            @else
                <p class="text-base font-semibold">{{ __('members.empty.no_members') }}</p>
                <p class="app-muted text-sm">{{ __('members.empty.get_started') }}</p>
                <a href="{{ route('tenant.members.create') }}" class="member-btn-primary mt-2">{{ __('members.add_member') }}</a>
            @endif
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="member-table">
                <thead class="app-table-head">
                    <tr>
                        <th class="member-th w-9"><input type="checkbox" id="select-all" class="member-checkbox"></th>
                        @php
                            $cols = [
                                ['key' => 'member_code', 'label' => __('members.table.id')],
                                ['key' => 'name',        'label' => __('members.table.member')],
                                ['key' => null,          'label' => __('members.table.phone')],
                                ['key' => 'plan_name',   'label' => __('members.table.plan')],
                                ['key' => 'created_at',  'label' => __('members.table.joined')],
                                ['key' => 'expiry_date', 'label' => __('members.table.expires')],
                                ['key' => 'status',      'label' => __('members.table.status')],
                                ['key' => 'balance_paise','label' => __('members.table.balance')],
                                ['key' => null,          'label' => ''],
                            ];
                            $sortBy  = request('sort_by', 'created_at');
                            $sortDir = request('sort_dir', 'desc');
                        @endphp
                        @foreach ($cols as $col)
                            <th class="member-th {{ $col['key'] ? 'cursor-pointer select-none' : '' }}">
                                @if ($col['key'])
                                    @php
                                        $nextDir = ($sortBy === $col['key'] && $sortDir === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <a href="{{ route('tenant.members.index', array_merge(request()->query(), ['sort_by' => $col['key'], 'sort_dir' => $nextDir, 'page' => 1])) }}" class="member-sort-link">
                                        {{ $col['label'] }}
                                        @if ($sortBy === $col['key'])
                                            <span class="member-sort-arrow {{ $sortDir === 'asc' ? 'rotate-180' : '' }}">▾</span>
                                        @else
                                            <span class="member-sort-arrow opacity-25">▾</span>
                                        @endif
                                    </a>
                                @else
                                    {{ $col['label'] }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $member)
                        @php
                            $effectiveStatus = $member->effective_status;
                            $statusColors = [
                                'active'   => ['bg' => 'rgba(29,158,117,0.15)', 'text' => '#1D9E75', 'dot' => '#1D9E75'],
                                'inactive' => ['bg' => 'rgba(136,135,128,0.15)', 'text' => '#888780', 'dot' => '#888780'],
                                'expired'  => ['bg' => 'rgba(226,75,74,0.15)', 'text' => '#E24B4A', 'dot' => '#E24B4A'],
                                'frozen'   => ['bg' => 'rgba(55,138,221,0.15)', 'text' => '#378ADD', 'dot' => '#378ADD'],
                            ];
                            $sc = $statusColors[$effectiveStatus] ?? $statusColors['inactive'];
                        @endphp
                        <tr class="member-row" data-member-id="{{ $member->id }}">
                            <td class="member-td"><input type="checkbox" class="member-checkbox row-checkbox" value="{{ $member->id }}"></td>
                            <td class="member-td">
                                <span class="member-code">{{ $member->member_code }}</span>
                            </td>
                            <td class="member-td">
                                <div class="flex items-center gap-2.5">
                                    <span class="member-avatar">{{ $member->initials }}</span>
                                    <div>
                                        <p class="member-name">{{ $member->name }}</p>
                                        @if ($member->email)
                                            <p class="app-muted text-xs">{{ $member->email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="member-td">
                                <span class="app-muted text-sm">{{ $member->phone }}</span>
                            </td>
                            <td class="member-td">
                                <span class="text-sm">{{ $member->plan_name ?? '—' }}</span>
                            </td>
                            <td class="member-td">
                                <span class="text-sm">{{ $member->created_at?->format('d M Y') ?? '—' }}</span>
                            </td>
                            <td class="member-td">
                                @if ($member->expiry_date)
                                    <span class="text-sm {{ $member->expiry_date->isPast() ? 'text-[#E24B4A]' : '' }}">
                                        {{ $member->expiry_date->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="app-muted text-sm">—</span>
                                @endif
                            </td>
                            <td class="member-td">
                                @if (in_array($member->status, ['active', 'inactive']))
                                    <form method="POST" action="{{ route('tenant.members.toggle-status', $member) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="member-status-toggle" title="{{ __('members.actions.toggle_status') }}">
                                            <span class="member-toggle-track {{ $member->status === 'active' ? 'member-toggle-on' : '' }}">
                                                <span class="member-toggle-thumb"></span>
                                            </span>
                                            <span style="color: {{ $sc['text'] }}; font-size: 0.78rem; font-weight: 600;">
                                                {{ $member->status_label }}
                                            </span>
                                        </button>
                                    </form>
                                @else
                                    <span class="member-status-badge" style="background: {{ $sc['bg'] }}; color: {{ $sc['text'] }};">
                                        <span class="member-status-dot" style="background: {{ $sc['dot'] }};"></span>
                                        {{ $member->status_label }}
                                    </span>
                                @endif
                            </td>
                            <td class="member-td">
                                @if ($member->balance_paise > 0)
                                    <span class="font-semibold text-[#E24B4A] text-sm">{{ $member->balance_rupees }}</span>
                                @else
                                    <span class="app-muted text-sm">₹0.00</span>
                                @endif
                            </td>
                            <td class="member-td text-right">
                                <div class="member-actions-wrap">
                                    <button type="button" class="member-action-btn" aria-label="Actions">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                                    </button>
                                    <div class="member-actions-menu">
                                        <a href="#" class="member-action-item">{{ __('members.actions.view_profile') }}</a>
                                        <a href="{{ route('tenant.members.edit', $member) }}" class="member-action-item">{{ __('members.actions.edit') }}</a>
                                        <a href="#" class="member-action-item">{{ __('members.actions.collect_fee') }}</a>
                                        <div class="member-action-divider"></div>
                                        <form method="POST" action="{{ route('tenant.members.destroy', $member) }}"
                                              onsubmit="return confirm('{{ __('members.actions.delete_confirm', ['name' => addslashes($member->name)]) }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="member-action-item member-action-danger w-full text-left">{{ __('members.actions.delete') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex flex-col items-center justify-between gap-3 border-t border-[var(--app-border)] px-5 py-3 sm:flex-row">
            <p class="app-muted text-xs">
                {{ __('members.table.showing', ['first' => $members->firstItem(), 'last' => $members->lastItem(), 'total' => number_format($members->total())]) }}
            </p>
            <div class="flex items-center gap-3">
                <select name="per_page" onchange="window.location='{{ route('tenant.members.index') }}?'+new URLSearchParams({...Object.fromEntries(new URLSearchParams(window.location.search)),...{per_page:this.value,page:1}}).toString()"
                    class="member-filter-select !py-1 !text-xs">
                    @foreach ([10, 25, 50, 100] as $pp)
                        <option value="{{ $pp }}" @selected($members->perPage() == $pp)>{{ $pp }} / page</option>
                    @endforeach
                </select>
                {{ $members->links() }}
            </div>
        </div>
    @endif
</div>


{{-- ── CSS ───────────────────────────────────────────────────────────────── --}}
@push('styles')
<style>
/* Stats */
.member-stat-card { cursor: pointer; }
.member-stat-active { box-shadow: inset 0 0 0 2px var(--app-brand); }

/* Search */
.member-search-wrap { display: flex; align-items: center; border: 1px solid var(--app-border); border-radius: 0.75rem; gap: 0.5rem; min-width: 200px; padding: 0 0.75rem; background: var(--app-panel-strong); }
.member-search-icon { color: var(--app-text-muted); display: inline-flex; height: 0.95rem; width: 0.95rem; flex: none; }
.member-search-icon svg { height: 100%; width: 100%; }
.member-search-input { background: transparent; border: none; color: var(--app-text); font-size: 0.875rem; outline: none; padding: 0.5rem 0; width: 100%; }
.member-search-input::placeholder { color: var(--app-text-muted); }

/* Filter selects */
.member-filter-select { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.75rem; color: var(--app-text); font-size: 0.8rem; outline: none; padding: 0.45rem 0.75rem; }

/* Buttons */
.member-btn-primary { align-items: center; background: var(--app-brand); border-radius: 0.75rem; color: #0f172a; display: inline-flex; font-size: 0.82rem; font-weight: 600; gap: 0.35rem; padding: 0.45rem 0.9rem; transition: opacity 160ms; white-space: nowrap; }
.member-btn-primary:hover { opacity: 0.88; }
.member-btn-ghost { align-items: center; border: 1px solid var(--app-border); border-radius: 0.75rem; color: var(--app-text-muted); display: inline-flex; font-size: 0.82rem; font-weight: 500; gap: 0.35rem; padding: 0.45rem 0.9rem; transition: background 160ms, color 160ms; white-space: nowrap; background: transparent; }
.member-btn-ghost:hover { background: color-mix(in srgb, var(--app-border) 50%, transparent); color: var(--app-text); }

/* Table */
.member-table { border-collapse: collapse; font-size: 0.82rem; width: 100%; }
.member-th { border-bottom: 1px solid var(--app-border); font-size: 0.7rem; font-weight: 600; letter-spacing: 0.08em; padding: 0.65rem 0.75rem; text-align: left; text-transform: uppercase; white-space: nowrap; color: var(--app-text-muted); }
.member-td { border-bottom: 1px solid color-mix(in srgb, var(--app-border) 60%, transparent); padding: 0.65rem 0.75rem; vertical-align: middle; }
.member-row:last-child .member-td { border-bottom: none; }
.member-row:hover { background: color-mix(in srgb, var(--app-brand-soft) 25%, transparent); }
.member-sort-link { align-items: center; color: inherit; display: inline-flex; gap: 0.25rem; }
.member-sort-link:hover { color: var(--app-text); }
.member-sort-arrow { font-size: 0.75rem; display: inline-block; transition: transform 160ms; }
.member-checkbox { accent-color: var(--app-brand); cursor: pointer; height: 0.9rem; width: 0.9rem; }
.member-code { color: var(--app-text-muted); font-family: monospace; font-size: 0.75rem; }
.member-avatar { align-items: center; background: color-mix(in srgb, var(--app-brand-soft) 80%, transparent); border-radius: 999px; color: var(--app-brand); display: inline-flex; flex: none; font-size: 0.7rem; font-weight: 700; height: 2rem; justify-content: center; letter-spacing: 0.04em; width: 2rem; }
.member-name { font-size: 0.875rem; font-weight: 500; }
.member-status-badge { align-items: center; border-radius: 999px; display: inline-flex; font-size: 0.72rem; font-weight: 600; gap: 0.35rem; padding: 0.25rem 0.6rem; white-space: nowrap; }
.member-status-dot { border-radius: 999px; flex: none; height: 0.4rem; width: 0.4rem; }

/* Status toggle */
.member-status-toggle { align-items: center; background: transparent; border: none; cursor: pointer; display: inline-flex; gap: 0.5rem; padding: 0; }
.member-toggle-track { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; display: inline-flex; flex: none; height: 1.15rem; padding: 0.12rem; transition: background 200ms, border-color 200ms; width: 2rem; }
.member-toggle-on { background: #1D9E75; border-color: #1D9E75; }
.member-toggle-thumb { background: #fff; border-radius: 999px; height: 0.8rem; transition: transform 200ms; width: 0.8rem; }
.member-toggle-on .member-toggle-thumb { transform: translateX(0.85rem); }
.member-toggle-track:not(.member-toggle-on) .member-toggle-thumb { background: var(--app-text-muted); }

/* Row actions */
.member-actions-wrap { position: relative; display: inline-flex; }
.member-action-btn { align-items: center; background: transparent; border: 1px solid transparent; border-radius: 0.5rem; color: var(--app-text-muted); display: inline-flex; padding: 0.25rem 0.4rem; transition: background 120ms, border-color 120ms; }
.member-action-btn:hover { background: var(--app-panel-strong); border-color: var(--app-border); color: var(--app-text); }
.member-actions-menu { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.85rem; box-shadow: 0 8px 32px rgba(0,0,0,0.28); display: none; min-width: 160px; padding: 0.35rem; position: fixed; z-index: 200; }
.member-actions-wrap.open .member-actions-menu { display: block; }
.member-action-item { border-radius: 0.5rem; color: var(--app-text); cursor: pointer; display: block; font-size: 0.8rem; padding: 0.45rem 0.6rem; text-decoration: none; transition: background 120ms; background: transparent; border: none; }
.member-action-item:hover { background: color-mix(in srgb, var(--app-border) 70%, transparent); }
.member-action-danger { color: #E24B4A !important; }
.member-action-divider { border-top: 1px solid var(--app-border); margin: 0.25rem 0; }

/* Empty state */
.member-empty-icon { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; color: var(--app-text-muted); display: inline-flex; height: 4.5rem; width: 4.5rem; align-items: center; justify-content: center; }
.member-empty-icon svg { height: 2rem; width: 2rem; }

</style>
@endpush

{{-- ── JS ────────────────────────────────────────────────────────────────── --}}
<script>
(function () {
    // Search debounce
    let searchTimer;
    document.getElementById('search-input').addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            document.getElementById('filter-form').submit();
        }, 350);
    });

    // Select-all checkbox
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
        });
    }

    // Row action menus — position:fixed so overflow-hidden containers don't clip them
    document.querySelectorAll('.member-actions-wrap').forEach(wrap => {
        const btn  = wrap.querySelector('.member-action-btn');
        const menu = wrap.querySelector('.member-actions-menu');
        btn.addEventListener('click', e => {
            e.stopPropagation();
            const isOpen = wrap.classList.contains('open');
            document.querySelectorAll('.member-actions-wrap.open').forEach(w => w.classList.remove('open'));
            if (!isOpen) {
                const rect = btn.getBoundingClientRect();
                menu.style.top   = (rect.bottom + 4) + 'px';
                menu.style.right = (window.innerWidth - rect.right) + 'px';
                wrap.classList.add('open');
            }
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.member-actions-wrap.open').forEach(w => w.classList.remove('open'));
    });
})();
</script>

</x-layouts.admin>
