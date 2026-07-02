<x-layouts.admin
    title="{{ __('attendance.checkins.title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('attendance.checkins.title') }}"
    subheading="{{ __('attendance.checkins.subtitle') }}"
>

<style>
/* ── Attendance page ───────────────────────────────────────────── */

.atc-stat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.75rem; margin-bottom:1.25rem; }
@media(max-width:640px){ .atc-stat-grid{ grid-template-columns:1fr; } }
.atc-stat { border:1px solid var(--app-border); border-radius:1rem; padding:1rem 1.25rem; }
.atc-stat-label { font-size:.7rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--app-text-muted); }
.atc-stat-val   { font-size:1.6rem; font-weight:700; margin-top:.3rem; }

.atc-filter { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; margin-bottom:1rem; }
.atc-input {
    border:1px solid var(--app-border); border-radius:.6rem; padding:.45rem .8rem; font-size:.85rem;
    background:transparent; color:var(--app-text); outline:none;
}
.atc-input:focus { border-color:var(--app-brand); }
.atc-select { appearance:none; padding-right:2rem; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%23888' stroke-width='2'%3E%3Cpolyline points='4 6 8 10 12 6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right .5rem center; }
.atc-btn-brand { border:none; background:var(--app-brand); color:#fff; border-radius:.6rem; padding:.5rem 1rem; font-size:.85rem; font-weight:600; cursor:pointer; }
.atc-btn-outline { border:1px solid var(--app-border); background:transparent; color:var(--app-text); border-radius:.6rem; padding:.5rem 1rem; font-size:.85rem; font-weight:600; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:.4rem; }
.atc-btn-outline:hover { background:var(--app-panel-strong); }

/* Badge chips */
.atc-badge { display:inline-flex; align-items:center; font-size:.7rem; font-weight:700; padding:.2rem .6rem; border-radius:999px; text-transform:uppercase; letter-spacing:.05em; }
.atc-badge-manual   { background:#dbeafe; color:#1d4ed8; }
.atc-badge-qr       { background:#d1fae5; color:#065f46; }
.atc-badge-biometric{ background:#ede9fe; color:#5b21b6; }

/* Check-out/delete action btn */
.atc-act-btn { border:1px solid var(--app-border); border-radius:.5rem; padding:.25rem .6rem; font-size:.75rem; font-weight:600; cursor:pointer; background:transparent; color:var(--app-text-muted); }
.atc-act-btn:hover { background:var(--app-panel-strong); color:var(--app-text); }
.atc-act-btn-danger:hover { border-color:#ef4444; color:#ef4444; background:#fef2f2; }

/* Empty state */
.atc-empty { display:flex; flex-direction:column; align-items:center; padding:4rem 1rem; text-align:center; }
.atc-empty-icon {
    background:var(--app-panel-strong); border:1px solid var(--app-border); border-radius:999px;
    color:var(--app-text-muted); height:4.5rem; width:4.5rem;
    display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;
}
.atc-empty-title    { font-size:1.1rem; font-weight:700; }
.atc-empty-subtitle { font-size:.85rem; color:var(--app-text-muted); margin-top:.4rem; max-width:22rem; }

/* ── Check-in Drawer ──────────────────────────────────────────── */
.atc-overlay { position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:40; display:none; }
.atc-overlay.open { display:block; }
.atc-drawer {
    position:fixed; top:0; right:0; height:100dvh; width:min(26rem,100%);
    background:var(--app-panel); border-left:1px solid var(--app-border);
    box-shadow:-4px 0 30px rgba(0,0,0,.15); z-index:50;
    display:flex; flex-direction:column; transform:translateX(100%); transition:transform .25s ease;
}
.atc-drawer.open { transform:translateX(0); }
.atc-drawer-head {
    padding:1.25rem 1.5rem; border-bottom:1px solid var(--app-border);
    display:flex; align-items:center; justify-content:space-between;
}
.atc-drawer-head h2 { font-size:1rem; font-weight:700; }
.atc-drawer-close { border:none; background:none; cursor:pointer; color:var(--app-text-muted); padding:.25rem; }
.atc-drawer-body { flex:1; overflow-y:auto; padding:1.25rem 1.5rem; display:flex; flex-direction:column; gap:1rem; }
.atc-drawer-foot { padding:1rem 1.5rem; border-top:1px solid var(--app-border); }

/* Member search results */
.atc-search-results { border:1px solid var(--app-border); border-radius:.75rem; overflow:hidden; margin-top:.5rem; }
.atc-member-row { display:flex; align-items:center; gap:.75rem; padding:.75rem 1rem; cursor:pointer; transition:background .12s; }
.atc-member-row:hover, .atc-member-row.selected { background:var(--app-panel-strong); }
.atc-member-avatar {
    width:2.5rem; height:2.5rem; border-radius:999px; object-fit:cover;
    background:var(--app-brand-soft); display:flex; align-items:center; justify-content:center;
    font-size:.8rem; font-weight:700; color:var(--app-brand); flex-shrink:0;
}
.atc-member-name  { font-size:.875rem; font-weight:600; }
.atc-member-meta  { font-size:.75rem; color:var(--app-text-muted); }
.atc-member-badge-ok      { font-size:.65rem; font-weight:700; padding:.15rem .45rem; border-radius:999px; background:#d1fae5; color:#065f46; }
.atc-member-badge-expired { font-size:.65rem; font-weight:700; padding:.15rem .45rem; border-radius:999px; background:#fee2e2; color:#b91c1c; }

/* Selected member preview card */
.atc-selected-card { border:1.5px solid var(--app-brand); border-radius:1rem; padding:.875rem 1rem; display:flex; gap:.75rem; align-items:center; background:var(--app-brand-soft)/20; }

.atc-field label { display:block; font-size:.8rem; font-weight:600; margin-bottom:.35rem; color:var(--app-text-muted); }
.atc-field input, .atc-field select, .atc-field textarea {
    width:100%; border:1px solid var(--app-border); border-radius:.6rem; padding:.5rem .75rem;
    font-size:.85rem; background:transparent; color:var(--app-text); outline:none;
}
.atc-field input:focus, .atc-field select:focus { border-color:var(--app-brand); }

/* ── Sheet view ────────────────────────────────────────────────────────── */
.atc-view-toggle { display:inline-flex; border:1px solid var(--app-border); border-radius:.6rem; overflow:hidden; }
.atc-view-toggle a { display:inline-flex; align-items:center; gap:.35rem; padding:.45rem .85rem; font-size:.8rem; font-weight:600; text-decoration:none; color:var(--app-text-muted); transition:background .12s,color .12s; }
.atc-view-toggle a:hover { background:var(--app-panel-strong); color:var(--app-text); }
.atc-view-toggle a.active { background:var(--app-brand); color:#0f172a; }
.atc-view-toggle a + a { border-left:1px solid var(--app-border); }

.sheet-wrap { overflow-x:auto; width:100%; }
.sheet-table { border-collapse:collapse; white-space:nowrap; font-size:.78rem; }
.sheet-table th, .sheet-table td { padding:.45rem .55rem; border:1px solid var(--app-border); }
.sheet-table thead tr:first-child th { background:var(--app-panel-strong); font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--app-text-muted); text-align:center; }
.sheet-table tbody td { background:var(--app-panel); }
.sheet-table tbody tr:hover td { background:var(--app-panel-strong); }
.sheet-th-name { text-align:left !important; min-width:120px; position:sticky; left:0; z-index:2; }
.sheet-th-id   { text-align:left !important; min-width:90px;  position:sticky; left:120px; z-index:2; }
.sheet-td-name { position:sticky; left:0; z-index:1; font-weight:600; }
.sheet-td-id   { position:sticky; left:120px; z-index:1; font-size:.72rem; color:var(--app-text-muted); }
.sheet-th-p  { background:rgba(34,197,94,.15) !important; color:#15803d !important; font-weight:800 !important; }
.sheet-th-a  { background:rgba(239,68,68,.12) !important; color:#b91c1c !important; font-weight:800 !important; }
.sheet-cell { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:.3rem; font-size:.65rem; font-weight:800; }
.sheet-cell-p { background:rgba(34,197,94,.18); color:#15803d; }
.sheet-cell-a { background:rgba(239,68,68,.14); color:#dc2626; }
.sheet-cell-f { background:var(--app-panel-strong); color:var(--app-text-muted); }
.sheet-total-p { font-weight:700; color:#15803d; text-align:center; }
.sheet-total-a { font-weight:700; color:#dc2626; text-align:center; }
</style>


@if($viewMode === 'list')
{{-- ── Stats ─────────────────────────────────────────────────────────────── --}}
<div class="atc-stat-grid">
    <div class="app-panel atc-stat">
        <div class="atc-stat-label">{{ __('attendance.stats.total') }}</div>
        <div class="atc-stat-val">{{ number_format($stats['total']) }}</div>
    </div>
    <div class="app-panel atc-stat">
        <div class="atc-stat-label">{{ __('attendance.stats.unique') }}</div>
        <div class="atc-stat-val">{{ number_format($stats['unique']) }}</div>
    </div>
    <div class="app-panel atc-stat">
        <div class="atc-stat-label">{{ __('attendance.stats.peak_hour') }}</div>
        <div class="atc-stat-val text-base">{{ $stats['peak_hour'] }}</div>
    </div>
</div>

{{-- ── Filter bar ────────────────────────────────────────────────────────── --}}
<form id="atc-filter-form" method="GET" action="{{ route('tenant.attendance.checkins') }}">
    <div class="atc-filter">
        {{-- Date --}}
        <input type="date" name="date" value="{{ $date }}" class="atc-input" onchange="this.form.submit()">

        {{-- Branch --}}
        @if($branches->isNotEmpty())
        <select name="branch_id" class="atc-input atc-select" onchange="this.form.submit()">
            <option value="">{{ __('attendance.checkins.all_branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        @endif

        {{-- Method --}}
        <select name="method" class="atc-input atc-select" onchange="this.form.submit()">
            <option value="">{{ __('attendance.checkins.all_methods') }}</option>
            @foreach ($methods as $m)
                <option value="{{ $m }}" @selected(request('method') === $m)>{{ __('attendance.methods.'.$m) }}</option>
            @endforeach
        </select>

        {{-- Search --}}
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('attendance.checkins.search_ph') }}" class="atc-input flex-1 min-w-40">
        <button type="submit" class="atc-btn-brand">Search</button>

        {{-- View toggle --}}
        <div class="atc-view-toggle">
            <a href="{{ route('tenant.attendance.checkins', request()->except('view')) }}" class="active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                List
            </a>
            <a href="{{ route('tenant.attendance.checkins', array_merge(request()->only(['branch_id']), ['view'=>'sheet'])) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg>
                Sheet
            </a>
        </div>

        {{-- Export --}}
        <a href="{{ route('tenant.attendance.checkins.export', request()->only(['date','branch_id','method'])) }}" class="atc-btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M12 15V3m0 12-4-4m4 4 4-4"/><path d="M2 17l.621 2.485A2 2 0 0 0 4.561 21h14.878a2 2 0 0 0 1.94-1.515L22 17"/></svg>
            {{ __('attendance.checkins.export_csv') }}
        </a>

        {{-- Check in button --}}
        @if($canCheckin)
        <button type="button" onclick="atcOpenDrawer()" class="atc-btn-brand flex items-center gap-1.5">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4"><path d="M12 5v14M5 12h14"/></svg>
            {{ __('attendance.checkins.checkin_btn') }}
        </button>
        @endif
    </div>
</form>

{{-- ── Table / empty state ───────────────────────────────────────────────── --}}
@if ($logs->isEmpty())
    <div class="app-panel mt-2 w-full rounded-[2rem] border">
        <div class="atc-empty">
            <div class="atc-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-7 w-7"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            @if(request()->hasAny(['search','method','branch_id']))
                <p class="atc-empty-title">{{ __('attendance.empty.no_match') }}</p>
                <p class="atc-empty-subtitle">{{ __('attendance.empty.try_adjusting') }}</p>
                <a href="{{ route('tenant.attendance.checkins', ['date' => $date]) }}" class="mt-4 atc-btn-outline">{{ __('attendance.empty.clear_all') }}</a>
            @else
                <p class="atc-empty-title">{{ __('attendance.empty.title') }}</p>
                <p class="atc-empty-subtitle">{{ __('attendance.empty.subtitle') }}</p>
                @if($canCheckin)
                    <button onclick="atcOpenDrawer()" class="mt-4 atc-btn-brand">{{ __('attendance.empty.checkin_now') }}</button>
                @endif
            @endif
        </div>
    </div>
@else
    <div class="app-panel mt-2 w-full overflow-hidden rounded-[2rem] border">
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[var(--app-border)] bg-[var(--app-panel-strong)]">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.table.member') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.table.plan') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.table.branch') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.table.method') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.table.time_in') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.table.time_out') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.table.duration') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--app-border)]">
                    @foreach ($logs as $log)
                        <tr class="transition hover:bg-[var(--app-panel-strong)]">
                            {{-- Member --}}
                            <td class="whitespace-nowrap px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    @if($log->member?->photo_url)
                                        <img src="{{ $log->member->photo_url }}" alt="" class="h-8 w-8 rounded-full object-cover flex-shrink-0">
                                    @else
                                        <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-[var(--app-brand-soft)] text-xs font-bold text-[var(--app-brand)]">
                                            {{ strtoupper(substr($log->member?->name ?? '?', 0, 1)) }}
                                        </span>
                                    @endif
                                    <div>
                                        <p class="font-semibold">{{ $log->member?->name }}</p>
                                        <p class="text-xs text-[var(--app-text-muted)]">{{ $log->member?->member_code }}</p>
                                    </div>
                                </div>
                            </td>
                            {{-- Plan --}}
                            <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">
                                {{ $log->member?->plan_name ?? '—' }}
                            </td>
                            {{-- Branch --}}
                            <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">
                                {{ $log->branch?->name ?? '—' }}
                            </td>
                            {{-- Method --}}
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="atc-badge atc-badge-{{ $log->method }}">{{ __('attendance.methods.'.$log->method) }}</span>
                            </td>
                            {{-- Time in --}}
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs">
                                {{ $log->checked_in_at->format('H:i') }}
                            </td>
                            {{-- Time out --}}
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-[var(--app-text-muted)]">
                                @if($log->checked_out_at)
                                    {{ $log->checked_out_at->format('H:i') }}
                                @else
                                    <form method="POST" action="{{ route('tenant.attendance.checkins.checkout', $log) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="atc-act-btn">Check out</button>
                                    </form>
                                @endif
                            </td>
                            {{-- Duration --}}
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-[var(--app-text-muted)]">
                                {{ $log->duration ?? '—' }}
                            </td>
                            {{-- Actions --}}
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                @if($canManage)
                                    <form method="POST" action="{{ route('tenant.attendance.checkins.destroy', $log) }}"
                                          onsubmit="return confirm('Delete this check-in?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="atc-act-btn atc-act-btn-danger">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="inline h-3.5 w-3.5"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
            <div class="border-t border-[var(--app-border)] px-5 py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endif

@endif {{-- end @if($viewMode === 'list') --}}

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- ── Sheet View ─────────────────────────────────────────────────────── --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
@if($viewMode === 'sheet')

{{-- ── Sheet Stats ──────────────────────────────────────────────────────── --}}
<div class="atc-stat-grid">
    <div class="app-panel atc-stat">
        <div class="atc-stat-label">Total Members</div>
        <div class="atc-stat-val">{{ number_format($sheetStats['total_members']) }}</div>
    </div>
    <div class="app-panel atc-stat">
        <div class="atc-stat-label">Check-ins This Month</div>
        <div class="atc-stat-val">{{ number_format($sheetStats['total_checkins']) }}</div>
    </div>
    <div class="app-panel atc-stat">
        <div class="atc-stat-label">Attendance Rate</div>
        <div class="atc-stat-val text-base">
            {{ $sheetStats['attendance_rate'] }}%
            <span class="text-xs font-normal" style="color:var(--app-text-muted)">
                ({{ $sheetStats['past_days'] }} days tracked)
            </span>
        </div>
    </div>
</div>

{{-- Sheet filter bar --}}
<form method="GET" action="{{ route('tenant.attendance.checkins') }}" id="sheet-filter-form">
    <input type="hidden" name="view" value="sheet">
    <div class="atc-filter">

        {{-- Month (mirrors date input in list view) --}}
        <input type="month" name="month" value="{{ $month }}" class="atc-input" onchange="this.form.submit()">

        {{-- Branch --}}
        @if($branches->isNotEmpty())
        <select name="branch_id" class="atc-input atc-select" onchange="this.form.submit()">
            <option value="">All Branches</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        @endif

        {{-- Status (mirrors method filter in list view) --}}
        <select name="status" class="atc-input atc-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="active"   @selected(request('status')==='active')>Active</option>
            <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
            <option value="expired"  @selected(request('status')==='expired')>Expired</option>
            <option value="frozen"   @selected(request('status')==='frozen')>Frozen</option>
        </select>

        {{-- Per page --}}
        <select name="per_page" class="atc-input atc-select" onchange="this.form.submit()">
            @foreach([10, 20, 50, 100] as $n)
                <option value="{{ $n }}" @selected($perPage == $n)>{{ $n }} per page</option>
            @endforeach
        </select>

        {{-- Search --}}
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search member..." class="atc-input flex-1 min-w-40">
        <button type="submit" class="atc-btn-brand">Search</button>

        {{-- View toggle --}}
        <div class="atc-view-toggle">
            <a href="{{ route('tenant.attendance.checkins', request()->only(['branch_id'])) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                List
            </a>
            <a href="#" class="active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg>
                Sheet
            </a>
        </div>

        {{-- Export CSV --}}
        <a href="{{ route('tenant.attendance.checkins.export', array_merge(request()->only(['branch_id']), ['month' => $month])) }}" class="atc-btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M12 15V3m0 12-4-4m4 4 4-4"/><path d="M2 17l.621 2.485A2 2 0 0 0 4.561 21h14.878a2 2 0 0 0 1.94-1.515L22 17"/></svg>
            {{ __('attendance.checkins.export_csv') }}
        </a>
    </div>
</form>

{{-- Sheet grid --}}
@php
    $sheetYear  = substr($month, 0, 4);
    $sheetMonthN= (int) substr($month, 5, 2);
    $today      = today();
@endphp
<div class="app-panel mt-2 w-full rounded-[2rem] border overflow-hidden">
    @if($members->isEmpty())
        <div class="atc-empty">
            <div class="atc-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-7 w-7"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <p class="atc-empty-title">No members found</p>
            <p class="atc-empty-subtitle">Try adjusting your filters.</p>
        </div>
    @else
        <div class="sheet-wrap">
            <table class="sheet-table">
                <thead>
                    {{-- Group header --}}
                    <tr>
                        <th class="sheet-th-name" rowspan="2">Name</th>
                        <th class="sheet-th-id"   rowspan="2">Member ID</th>
                        <th colspan="2" style="text-align:center;background:var(--app-panel-strong)">Total</th>
                        <th colspan="{{ $daysCount }}" style="text-align:center;background:var(--app-panel-strong)">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
                        </th>
                    </tr>
                    {{-- Day headers --}}
                    <tr>
                        <th class="sheet-th-p">P</th>
                        <th class="sheet-th-a">A</th>
                        @for($d = 1; $d <= $daysCount; $d++)
                            @php
                                $colDate = \Carbon\Carbon::createFromDate($sheetYear, $sheetMonthN, $d);
                                $isToday = $colDate->isToday();
                                $isFuture = $colDate->gt($today);
                            @endphp
                            <th style="text-align:center;min-width:30px;{{ $isToday ? 'background:rgba(var(--brand-rgb,99,102,241),.12);color:var(--app-brand);' : ($isFuture ? 'color:var(--app-text-muted);opacity:.5;' : '') }}">
                                {{ $d }}
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $member)
                        @php $row = $grid[$member->id] ?? ['present'=>0,'absent'=>0,'cells'=>[]]; @endphp
                        <tr>
                            <td class="sheet-td-name">{{ $member->name }}</td>
                            <td class="sheet-td-id">{{ $member->member_code }}<br><span style="font-size:.68rem">{{ $member->phone }}</span></td>
                            <td class="sheet-total-p">{{ $row['present'] }}</td>
                            <td class="sheet-total-a">{{ $row['absent'] }}</td>
                            @for($d = 1; $d <= $daysCount; $d++)
                                @php $cell = $row['cells'][$d] ?? '-'; @endphp
                                <td style="text-align:center;padding:.3rem .4rem;">
                                    @if($cell === 'P')
                                        <span class="sheet-cell sheet-cell-p">✓</span>
                                    @elseif($cell === 'A')
                                        <span class="sheet-cell sheet-cell-a">✗</span>
                                    @else
                                        <span class="sheet-cell sheet-cell-f">—</span>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($members->hasPages())
            <div class="border-t border-[var(--app-border)] px-5 py-3">
                {{ $members->links() }}
            </div>
        @endif
    @endif
</div>

@endif {{-- end @if($viewMode === 'sheet') --}}

{{-- ── Check-in Drawer ───────────────────────────────────────────────────── --}}
@if(isset($canCheckin) && $canCheckin)
<div class="atc-overlay" id="atc-overlay" onclick="atcCloseDrawer()"></div>
<aside class="atc-drawer" id="atc-drawer" role="dialog" aria-modal="true">
    <div class="atc-drawer-head">
        <h2>{{ __('attendance.checkin_drawer.title') }}</h2>
        <button class="atc-drawer-close" onclick="atcCloseDrawer()" aria-label="Close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <div class="atc-drawer-body">
        {{-- Member search --}}
        <div class="atc-field">
            <label>{{ __('attendance.checkin_drawer.search_ph') }}</label>
            <input type="text" id="atc-member-search" autocomplete="off"
                   placeholder="{{ __('attendance.checkin_drawer.search_ph') }}"
                   oninput="atcSearch(this.value)">
        </div>

        <div id="atc-search-results" class="atc-search-results" style="display:none"></div>

        {{-- Selected member card --}}
        <div id="atc-selected-card" class="atc-selected-card" style="display:none">
            <span id="atc-sel-avatar" class="atc-member-avatar"></span>
            <div>
                <p id="atc-sel-name" class="atc-member-name"></p>
                <p id="atc-sel-meta" class="atc-member-meta"></p>
            </div>
        </div>
    </div>

    {{-- Confirm form --}}
    <form method="POST" action="{{ route('tenant.attendance.checkins.store') }}" id="atc-checkin-form">
        @csrf
        <input type="hidden" name="member_id" id="atc-member-id">

        @if($branches->isNotEmpty())
        <div class="px-6 pb-2">
            <div class="atc-field">
                <label>{{ __('attendance.checkin_drawer.method') }}</label>
                <select name="method" class="atc-input atc-select">
                    @foreach($methods as $m)
                        <option value="{{ $m }}">{{ __('attendance.methods.'.$m) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        <div class="px-6 pb-2">
            <div class="atc-field">
                <label>{{ __('attendance.checkin_drawer.reason') }}</label>
                <input type="text" name="reason" placeholder="{{ __('attendance.checkin_drawer.reason_ph') }}">
            </div>
        </div>

        <div class="atc-drawer-foot">
            <button type="submit" id="atc-confirm-btn" disabled class="atc-btn-brand w-full opacity-50" style="transition:opacity .15s">
                {{ __('attendance.checkin_drawer.confirm') }}
            </button>
        </div>
    </form>
</aside>
@endif

{{-- ── Auto-refresh every 30 s (list view only) ────────────────────────── --}}
@if($viewMode === 'list')
<script>
    let atcRefreshTimer = setInterval(() => {
        if (!document.getElementById('atc-drawer')?.classList.contains('open')) {
            window.location.reload();
        }
    }, 30000);

    function atcOpenDrawer() {
        document.getElementById('atc-overlay').classList.add('open');
        document.getElementById('atc-drawer').classList.add('open');
        document.getElementById('atc-member-search')?.focus();
        clearInterval(atcRefreshTimer);
    }

    function atcCloseDrawer() {
        document.getElementById('atc-overlay').classList.remove('open');
        document.getElementById('atc-drawer').classList.remove('open');
        atcRefreshTimer = setInterval(() => window.location.reload(), 30000);
    }

    let atcSearchTimer;
    function atcSearch(q) {
        clearTimeout(atcSearchTimer);
        const box = document.getElementById('atc-search-results');
        if (q.length < 2) { box.style.display = 'none'; return; }
        atcSearchTimer = setTimeout(async () => {
            const res  = await fetch(`{{ route('tenant.attendance.member-search') }}?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.length) {
                box.innerHTML = `<div class="atc-member-row" style="cursor:default;color:var(--app-text-muted);font-size:.85rem">{{ __('attendance.checkin_drawer.no_results') }}</div>`;
            } else {
                box.innerHTML = data.map(m => `
                    <div class="atc-member-row" onclick="atcSelectMember(${m.id},'${escAttr(m.name)}','${escAttr(m.member_code)}','${escAttr(m.plan_name??'')}','${escAttr(m.expiry_date??'')}','${escAttr(m.status??'')}')">
                        <span class="atc-member-avatar" style="background:var(--app-brand-soft);color:var(--app-brand)">
                            ${m.photo_url ? `<img src="${m.photo_url}" style="width:100%;height:100%;border-radius:999px;object-fit:cover">` : m.name.charAt(0).toUpperCase()}
                        </span>
                        <div style="flex:1">
                            <p class="atc-member-name">${escHtml(m.name)}</p>
                            <p class="atc-member-meta">${escHtml(m.member_code)} · ${escHtml(m.phone)}</p>
                        </div>
                        <span class="atc-member-badge-${m.status === 'active' ? 'ok' : 'expired'}">${escHtml(m.status)}</span>
                    </div>`).join('');
            }
            box.style.display = 'block';
        }, 280);
    }

    function atcSelectMember(id, name, code, plan, expiry, status) {
        document.getElementById('atc-member-id').value = id;
        document.getElementById('atc-search-results').style.display = 'none';
        document.getElementById('atc-member-search').value = name;

        const avatar = document.getElementById('atc-sel-avatar');
        avatar.textContent = name.charAt(0).toUpperCase();
        document.getElementById('atc-sel-name').textContent = name;
        document.getElementById('atc-sel-meta').textContent = `${code} · ${plan} · Expires ${expiry}`;
        document.getElementById('atc-selected-card').style.display = 'flex';

        const btn = document.getElementById('atc-confirm-btn');
        btn.disabled = false;
        btn.classList.remove('opacity-50');
    }

    function escHtml(s) { const d = document.createElement('div'); d.textContent = String(s ?? ''); return d.innerHTML; }
    function escAttr(s) { return String(s ?? '').replace(/'/g, '&#39;').replace(/"/g, '&quot;'); }
</script>
@endif {{-- end @if($viewMode === 'list') script block --}}

</x-layouts.admin>
