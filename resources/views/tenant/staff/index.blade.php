<x-layouts.admin
    title="{{ __('staff.title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('staff.title') }}"
    subheading="{{ __('staff.subheading') }}"
>

{{-- Stats --}}
<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach ([
        ['label' => __('staff.stats.total'),       'value' => $stats['total'],       'color' => 'var(--app-text)'],
        ['label' => __('staff.stats.active'),      'value' => $stats['active'],      'color' => '#1D9E75'],
        ['label' => __('staff.stats.inactive'),    'value' => $stats['inactive'],    'color' => '#E24B4A'],
        ['label' => __('staff.stats.late_logins'), 'value' => $stats['late_logins'], 'color' => '#f97316'],
    ] as $card)
        <div class="app-panel rounded-2xl border p-4">
            <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ $card['label'] }}</p>
            <p class="mt-2 text-2xl font-semibold" style="color:{{ $card['color'] }}">{{ number_format($card['value']) }}</p>
        </div>
    @endforeach
</div>

{{-- Toolbar --}}
<div class="app-panel rounded-[2rem] border p-4">
    <form method="GET" action="{{ route('tenant.staff.index') }}" class="flex flex-wrap items-center gap-2">
        <input name="search" value="{{ request('search') }}"
               placeholder="{{ __('staff.filters.search') }}"
               class="min-w-[220px] flex-1 rounded-2xl border px-4 py-3 text-sm outline-none">

        <select name="role" onchange="this.form.submit()" class="rounded-2xl border px-4 py-3 text-sm outline-none">
            <option value="">{{ __('staff.filters.all_roles') }}</option>
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected(request('role') === $role)>
                    {{ str($role)->replace('_', ' ')->title() }}
                </option>
            @endforeach
        </select>

        <select name="branch_id" onchange="this.form.submit()" class="rounded-2xl border px-4 py-3 text-sm outline-none">
            <option value="">{{ __('staff.filters.all_branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>

        <select name="status" onchange="this.form.submit()" class="rounded-2xl border px-4 py-3 text-sm outline-none">
            <option value="">{{ __('staff.filters.all_statuses') }}</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>
                    {{ __('staff.statuses.'.$status) }}
                </option>
            @endforeach
        </select>

        <div class="flex items-center gap-2">
            <a href="{{ route('tenant.staff.roles') }}"
               class="rounded-2xl border px-4 py-3 text-sm font-semibold hover:opacity-80">
                {{ __('staff.actions.roles_link') }}
            </a>
            <a href="{{ route('tenant.staff.attendance') }}"
               class="rounded-2xl border px-4 py-3 text-sm font-semibold hover:opacity-80">
                {{ __('staff.actions.attendance_link') }}
            </a>
            @if ($canManage)
                <a href="{{ route('tenant.staff.create') }}"
                   class="rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                    {{ __('staff.actions.add') }}
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Table --}}
<div class="app-panel mt-5 w-full overflow-hidden rounded-[2rem] border">

    @if ($staff->isEmpty())
        {{-- Empty state (matches members page style) --}}
        <div class="flex flex-col items-center gap-4 py-20 text-center">
            <div class="sf-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/>
                    <circle cx="9.5" cy="7" r="3"/>
                    <path d="M20 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 4.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            @if (request()->hasAny(['search', 'role', 'branch_id', 'status']))
                <p class="text-base font-semibold">{{ __('staff.empty.no_match') }}</p>
                <p class="app-muted text-sm">{{ __('staff.empty.try_adjusting') }}</p>
                <a href="{{ route('tenant.staff.index') }}" class="sf-btn-ghost mt-1">{{ __('staff.empty.clear_all') }}</a>
            @else
                <p class="text-base font-semibold">{{ __('staff.empty.heading') }}</p>
                <p class="app-muted text-sm">{{ __('staff.empty.description') }}</p>
                @if ($canManage)
                    <a href="{{ route('tenant.staff.create') }}" class="sf-btn-primary mt-1">{{ __('staff.actions.add') }}</a>
                @endif
            @endif
        </div>

    @else
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-full text-sm">
                <thead class="app-table-head">
                    <tr>
                        @foreach ([
                            __('staff.table.name'),
                            __('staff.table.role'),
                            __('staff.table.branch'),
                            __('staff.table.phone'),
                            __('staff.table.join_date'),
                            __('staff.table.status'),
                            __('staff.table.last_login'),
                            __('staff.table.actions'),
                        ] as $head)
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em]">{{ $head }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staff as $member)
                        @php
                            $lastLogin = $member->user?->last_login_at;
                            $lateLogin = !$lastLogin || $lastLogin->lt(now()->subDays(30));
                            $roleColors = [
                                'receptionist'   => 'bg-cyan-500/10 text-cyan-300 border border-cyan-400/20',
                                'trainer'        => 'bg-purple-500/10 text-purple-300 border border-purple-400/20',
                                'accountant'     => 'bg-amber-500/10 text-amber-300 border border-amber-400/20',
                                'pos'            => 'bg-blue-500/10 text-blue-300 border border-blue-400/20',
                                'branch_manager' => 'bg-emerald-500/10 text-emerald-300 border border-emerald-400/20',
                            ];
                            $roleCls = $roleColors[$member->role] ?? 'bg-[var(--app-brand-soft)] text-[var(--app-brand)] border border-[var(--app-brand)]/20';
                        @endphp
                        <tr class="border-t border-[var(--app-border)] transition-colors hover:bg-white/[0.02]">

                            {{-- Name + avatar --}}
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($member->photo_url)
                                        <img src="{{ asset('storage/'.$member->photo_url) }}"
                                             alt="{{ $member->name }}"
                                             class="h-10 w-10 rounded-full object-cover ring-2 ring-[var(--app-border)]">
                                    @else
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[var(--app-brand-soft)] text-sm font-bold text-[var(--app-brand)]">
                                            {{ $member->initials }}
                                        </span>
                                    @endif
                                    <div>
                                        <a href="{{ route('tenant.staff.show', $member) }}"
                                           class="font-semibold hover:text-orange-400 hover:underline">
                                            {{ $member->name }}
                                        </a>
                                        <p class="app-muted mt-0.5 text-xs">{{ $member->phone }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Role badge --}}
                            <td class="px-4 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $roleCls }}">
                                    {{ str($member->role)->replace('_', ' ')->title() }}
                                </span>
                            </td>

                            <td class="px-4 py-4 text-sm">{{ $member->branch?->name ?? '—' }}</td>
                            <td class="px-4 py-4 text-sm tabular-nums">{{ $member->phone }}</td>
                            <td class="px-4 py-4 text-sm tabular-nums">{{ $member->join_date?->format('d M Y') }}</td>

                            {{-- Status badge --}}
                            <td class="px-4 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold
                                    {{ $member->status === 'active'
                                        ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-400/20'
                                        : 'bg-red-500/10 text-red-300 border border-red-400/20' }}">
                                    {{ __('staff.statuses.'.$member->status) }}
                                </span>
                            </td>

                            {{-- Last login --}}
                            <td class="px-4 py-4 text-sm {{ $lateLogin ? 'text-red-300' : 'app-muted' }}">
                                {{ $lastLogin ? $lastLogin->diffForHumans() : __('staff.show.never') }}
                            </td>

                            {{-- Actions dropdown --}}
                            <td class="px-4 py-4">
                                <div class="relative">
                                    <button type="button"
                                            data-sf-menu-btn
                                            data-id="{{ $member->id }}"
                                            onclick="sfToggleMenu(this)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl border text-lg leading-none hover:opacity-80">
                                        &#8942;
                                    </button>
                                    <div id="sf-menu-{{ $member->id }}"
                                         class="sf-action-menu app-panel min-w-[164px] rounded-2xl border py-1 shadow-xl"
                                         style="position:fixed;z-index:9999;display:none">

                                        <a href="{{ route('tenant.staff.show', $member) }}"
                                           class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-white/5">
                                            <svg class="h-4 w-4 opacity-60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                            {{ __('staff.actions.view') }}
                                        </a>

                                        @if ($canManage)
                                            <a href="{{ route('tenant.staff.edit', $member) }}"
                                               class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-white/5">
                                                <svg class="h-4 w-4 opacity-60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                                                {{ __('staff.actions.edit') }}
                                            </a>

                                            @if ($member->status === 'active')
                                                <div class="my-1 border-t border-[var(--app-border)]"></div>
                                                <form method="POST" action="{{ route('tenant.staff.deactivate', $member) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-red-300 hover:bg-red-500/10">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M4.93 4.93l14.14 14.14"/></svg>
                                                        {{ __('staff.actions.deactivate') }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-[var(--app-border)] px-4 py-3">
            {{ $staff->withQueryString()->links() }}
        </div>
    @endif

</div>

<style>
.sf-empty-icon {
    background: var(--app-panel-strong);
    border: 1px solid var(--app-border);
    border-radius: 999px;
    color: var(--app-text-muted);
    display: inline-flex;
    height: 4.5rem;
    width: 4.5rem;
    align-items: center;
    justify-content: center;
}
.sf-empty-icon svg { height: 2rem; width: 2rem; }
.sf-btn-primary {
    display: inline-flex; align-items: center; gap: 0.375rem;
    background: #f97316; color: #0f172a;
    border-radius: 1rem; padding: 0.625rem 1.25rem;
    font-size: 0.875rem; font-weight: 600;
    text-decoration: none;
}
.sf-btn-primary:hover { background: #fb923c; }
.sf-btn-ghost {
    display: inline-flex; align-items: center;
    border: 1px solid var(--app-border); border-radius: 1rem;
    padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600;
    text-decoration: none;
}
.sf-btn-ghost:hover { opacity: 0.8; }
</style>

<script>
function sfToggleMenu(btn) {
    const menu = document.getElementById('sf-menu-' + btn.dataset.id);
    document.querySelectorAll('.sf-action-menu').forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    if (menu.style.display === 'block') { menu.style.display = 'none'; return; }
    menu.style.display = 'block';
    const rect = btn.getBoundingClientRect();
    const menuW = menu.offsetWidth;
    let left = rect.right - menuW + window.scrollX;
    let top  = rect.bottom + 4 + window.scrollY;
    if (left < 8) left = 8;
    menu.style.top  = top + 'px';
    menu.style.left = left + 'px';
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('[data-sf-menu-btn]')) {
        document.querySelectorAll('.sf-action-menu').forEach(m => m.style.display = 'none');
    }
});
</script>

</x-layouts.admin>
