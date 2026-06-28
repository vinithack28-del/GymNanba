<x-layouts.admin
    title="{{ __('classes.timetable.title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('classes.title') }}"
    subheading="{{ __('classes.subtitle') }}"
>

<style>
/* ── Timetable ─────────────────────────────────────────────────── */
.ttb-nav { display:flex; flex-wrap:wrap; align-items:center; gap:.75rem; margin-bottom:1.25rem; }
.ttb-week-label { font-size:.95rem; font-weight:700; }
.ttb-btn { border:1px solid var(--app-border); border-radius:.6rem; padding:.4rem .85rem; font-size:.8rem; font-weight:600; cursor:pointer; background:transparent; color:var(--app-text); text-decoration:none; display:inline-flex; align-items:center; gap:.35rem; }
.ttb-btn:hover { background:var(--app-panel-strong); }
.ttb-btn-brand { background:var(--app-brand); border-color:var(--app-brand); color:#fff; }
.ttb-btn-brand:hover { opacity:.9; background:var(--app-brand); }
.ttb-select { border:1px solid var(--app-border); border-radius:.6rem; padding:.4rem .85rem; font-size:.8rem; background:transparent; color:var(--app-text); }

/* Calendar grid */
.ttb-grid { display:grid; grid-template-columns:3.5rem repeat(7,1fr); gap:0; border:1px solid var(--app-border); border-radius:1.5rem; overflow:hidden; }
.ttb-head-cell { padding:.6rem .25rem; text-align:center; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; background:var(--app-panel-strong); border-bottom:1px solid var(--app-border); color:var(--app-text-muted); }
.ttb-head-cell.today { color:var(--app-brand); background:color-mix(in srgb, var(--app-brand) 8%, transparent); }
.ttb-time-col { font-size:.7rem; color:var(--app-text-muted); text-align:right; padding:.35rem .5rem 0 0; line-height:1; border-right:1px solid var(--app-border); }
.ttb-day-col { position:relative; border-right:1px solid var(--app-border); min-height:4rem; }
.ttb-day-col:last-child { border-right:none; }
.ttb-hour-row { border-bottom:1px solid color-mix(in srgb, var(--app-border) 40%, transparent); height:4rem; }
.ttb-hour-row:last-child { border-bottom:none; }
.ttb-time-row { border-bottom:1px solid color-mix(in srgb, var(--app-border) 40%, transparent); height:4rem; }
.ttb-time-row:last-child { border-bottom:none; }

/* Class block */
.ttb-class { position:absolute; left:.15rem; right:.15rem; border-radius:.6rem; padding:.25rem .4rem; font-size:.7rem; line-height:1.3; overflow:hidden; cursor:pointer; border:none; text-align:left; }
.ttb-class-name  { font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ttb-class-meta  { opacity:.8; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

/* Status colours */
.ttb-status-scheduled { background:#d1fae5; color:#065f46; border-left:3px solid #059669; }
.ttb-status-full      { background:#dbeafe; color:#1e40af; border-left:3px solid #3b82f6; }
.ttb-status-cancelled { background:#fef3c7; color:#92400e; border-left:3px solid #f59e0b; }
.ttb-status-completed { background:#f3f4f6; color:#6b7280; border-left:3px solid #9ca3af; }

/* List view */
.ttb-list-table { width:100%; }

/* Legend */
.ttb-legend { display:flex; flex-wrap:wrap; gap:.75rem; margin-bottom:1rem; }
.ttb-legend-item { display:flex; align-items:center; gap:.4rem; font-size:.75rem; }
.ttb-legend-dot { width:.6rem; height:.6rem; border-radius:999px; }

/* Type badge */
.ttb-type-badge { display:inline-block; font-size:.65rem; font-weight:700; padding:.1rem .45rem; border-radius:999px; background:var(--app-panel-strong); color:var(--app-text-muted); text-transform:uppercase; }

/* Empty */
.ttb-empty { display:flex; flex-direction:column; align-items:center; padding:5rem 1rem; text-align:center; }
.ttb-empty-icon { background:var(--app-panel-strong); border:1px solid var(--app-border); border-radius:999px; color:var(--app-text-muted); height:4.5rem; width:4.5rem; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem; }
</style>

{{-- ── Header nav ────────────────────────────────────────────────────────── --}}
<div class="ttb-nav">
    {{-- Week nav --}}
    <a href="{{ route('tenant.classes.timetable', ['week' => $prevWeek, 'branch_id' => $branchId, 'view' => $view]) }}" class="ttb-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><polyline points="15 18 9 12 15 6"/></svg>
        {{ __('classes.timetable.prev_week') }}
    </a>

    <span class="ttb-week-label">
        {{ __('classes.timetable.week_of') }}
        {{ $weekStart->format('d M') }} – {{ $weekEnd->format('d M Y') }}
    </span>

    <a href="{{ route('tenant.classes.timetable', ['week' => $nextWeek, 'branch_id' => $branchId, 'view' => $view]) }}" class="ttb-btn">
        {{ __('classes.timetable.next_week') }}
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5"><polyline points="9 18 15 12 9 6"/></svg>
    </a>

    <a href="{{ route('tenant.classes.timetable', ['branch_id' => $branchId, 'view' => $view]) }}" class="ttb-btn">
        {{ __('classes.timetable.today') }}
    </a>

    {{-- Branch filter --}}
    @if($branches->isNotEmpty())
    <form method="GET" action="{{ route('tenant.classes.timetable') }}" style="display:contents">
        <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">
        <input type="hidden" name="view" value="{{ $view }}">
        <select name="branch_id" class="ttb-select" onchange="this.form.submit()">
            <option value="">{{ __('classes.timetable.all_branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </form>
    @endif

    {{-- View toggle --}}
    <a href="{{ route('tenant.classes.timetable', ['week' => $weekStart->toDateString(), 'branch_id' => $branchId, 'view' => $view === 'calendar' ? 'list' : 'calendar']) }}"
       class="ttb-btn {{ $view === 'list' ? 'ttb-btn-brand' : '' }}">
        @if($view === 'calendar')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            {{ __('classes.timetable.list_view') }}
        @else
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            {{ __('classes.timetable.calendar_view') }}
        @endif
    </a>

    <div class="ml-auto"></div>

    {{-- Create class --}}
    @if($canManage)
    <a href="{{ route('tenant.classes.create') }}" class="ttb-btn ttb-btn-brand">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4"><path d="M12 5v14M5 12h14"/></svg>
        {{ __('classes.timetable.create_class') }}
    </a>
    @endif
</div>

{{-- ── Legend ────────────────────────────────────────────────────────────── --}}
<div class="ttb-legend">
    @foreach(['scheduled'=>'#059669','full'=>'#3b82f6','cancelled'=>'#f59e0b','completed'=>'#9ca3af'] as $status => $color)
        <span class="ttb-legend-item">
            <span class="ttb-legend-dot" style="background:{{ $color }}"></span>
            {{ __('classes.statuses.'.$status) }}
        </span>
    @endforeach
</div>

@php
    $allEmpty = $byDay->every(fn ($d) => $d->isEmpty());
    // Time range: 6:00 to 22:00 = 16 hours
    $dayStart = 6 * 60;   // 360 minutes from midnight
    $dayRange = 16 * 60;  // 960 minutes
    $rowH     = 64;       // px per hour row
    $today    = now()->isoWeekday(); // 1=Mon..7=Sun
@endphp

@if($view === 'list')
    {{-- ── List View ─────────────────────────────────────────────────────── --}}
    @if($allEmpty)
        <div class="app-panel rounded-[2rem] border">
            <div class="ttb-empty">
                <div class="ttb-empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-7 w-7"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                </div>
                <p class="font-bold text-lg">{{ __('classes.timetable.no_classes') }}</p>
                <p class="text-sm text-[var(--app-text-muted)] mt-1">{{ __('classes.timetable.no_classes_sub') }}</p>
                @if($canManage)
                    <a href="{{ route('tenant.classes.create') }}" class="ttb-btn ttb-btn-brand mt-4">{{ __('classes.timetable.create_class') }}</a>
                @endif
            </div>
        </div>
    @else
        <div class="app-panel w-full overflow-hidden rounded-[2rem] border">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-[var(--app-border)] bg-[var(--app-panel-strong)]">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">Class</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">Trainer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">Branch</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">Bookings</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--app-border)]">
                        @foreach(range(1,7) as $dow)
                            @foreach($byDay[$dow] as $class)
                                <tr class="transition hover:bg-[var(--app-panel-strong)]">
                                    <td class="whitespace-nowrap px-5 py-3">
                                        <p class="font-semibold">{{ $class->name }}</p>
                                        <span class="ttb-type-badge">{{ __('classes.types.'.$class->type) }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">{{ $class->class_date->format('D, d M') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs">{{ substr($class->start_time,0,5) }}–{{ substr($class->end_time,0,5) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">{{ $class->trainer?->name ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">{{ $class->branch?->name ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        {{ $class->booking_count }}/{{ $class->max_capacity }}
                                        @if($class->waitlist_count > 0)
                                            <span class="ml-1 text-xs text-[var(--app-text-muted)]">(+{{ $class->waitlist_count }})</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        @php $st = $class->is_full ? 'full' : $class->status; @endphp
                                        <span class="ttb-class ttb-status-{{ $st }}" style="position:static;border-radius:.4rem;padding:.15rem .5rem;font-size:.7rem">{{ __('classes.statuses.'.$st) }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <a href="{{ route('tenant.classes.show', $class) }}" class="ttb-btn text-xs py-1">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@else
    {{-- ── Calendar View ─────────────────────────────────────────────────── --}}
    <div class="app-panel w-full overflow-hidden rounded-[2rem] border overflow-x-auto">
        <div style="min-width:700px">
            {{-- Day headers --}}
            <div class="ttb-grid" style="grid-template-rows:auto">
                <div class="ttb-head-cell" style="border-right:1px solid var(--app-border)"></div>
                @foreach(range(1,7) as $dow)
                    @php $day = $weekStart->copy()->addDays($dow-1); @endphp
                    <div class="ttb-head-cell {{ $dow === $today && $weekStart->isSameWeek(now()) ? 'today' : '' }}"
                         style="{{ $dow < 7 ? 'border-right:1px solid var(--app-border)' : '' }}">
                        <div>{{ __('classes.days.'.$dow) }}</div>
                        <div class="text-base font-bold">{{ $day->format('d') }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Time slots + classes --}}
            <div style="display:grid; grid-template-columns:3.5rem repeat(7,1fr);">
                {{-- Time column --}}
                <div style="border-right:1px solid var(--app-border)">
                    @for($h = 6; $h <= 21; $h++)
                        <div class="ttb-time-row flex items-start justify-end pr-1.5 pt-1" style="height:{{ $rowH }}px">
                            <span style="font-size:.65rem; color:var(--app-text-muted)">{{ sprintf('%02d:00', $h) }}</span>
                        </div>
                    @endfor
                </div>

                {{-- Day columns --}}
                @foreach(range(1,7) as $dow)
                    <div class="ttb-day-col" style="height:{{ $rowH * 16 }}px; {{ $dow < 7 ? 'border-right:1px solid var(--app-border)' : '' }}">
                        {{-- Hour lines --}}
                        @for($h = 0; $h < 16; $h++)
                            <div style="position:absolute;top:{{ $h * $rowH }}px;left:0;right:0;border-bottom:1px solid color-mix(in srgb, var(--app-border) 40%, transparent);height:{{ $rowH }}px;"></div>
                        @endfor

                        {{-- Class blocks --}}
                        @foreach($byDay[$dow] as $class)
                            @php
                                $startMin = $class->start_minutes;
                                $durMin   = $class->duration_minutes;
                                $topPx    = (($startMin - $dayStart) / 60) * $rowH;
                                $heightPx = max(($durMin / 60) * $rowH, 28);
                                $status   = $class->is_full ? 'full' : $class->status;
                            @endphp
                            <a href="{{ route('tenant.classes.show', $class) }}"
                               class="ttb-class ttb-status-{{ $status }}"
                               style="top:{{ $topPx }}px; height:{{ $heightPx }}px;"
                               title="{{ $class->name }}">
                                <div class="ttb-class-name">{{ $class->name }}</div>
                                @if($heightPx >= 42)
                                    <div class="ttb-class-meta">{{ substr($class->start_time,0,5) }} · {{ $class->booking_count }}/{{ $class->max_capacity }}</div>
                                @endif
                                @if($heightPx >= 58 && $class->trainer)
                                    <div class="ttb-class-meta">{{ $class->trainer->name }}</div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

</x-layouts.admin>
