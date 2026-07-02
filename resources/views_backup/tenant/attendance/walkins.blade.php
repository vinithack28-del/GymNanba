<x-layouts.admin
    title="{{ __('attendance.walkins.title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('attendance.walkins.title') }}"
    subheading="{{ __('attendance.walkins.subtitle') }}"
>
@php
    $selectedDayPassPlanId = old('plan_id');
@endphp

<style>
/* ── Walk-in page ────────────────────────────────────────────── */
.wki-grid   { display:grid; grid-template-columns:1fr 26rem; gap:1.5rem; align-items:start; }
@media(max-width:900px){ .wki-grid{ grid-template-columns:1fr; } }

.wki-stat-grid { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; margin-bottom:1.25rem; }
.wki-stat { border:1px solid var(--app-border); border-radius:1rem; padding:1rem 1.25rem; }
.wki-stat-label { font-size:.7rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--app-text-muted); }
.wki-stat-val   { font-size:1.6rem; font-weight:700; margin-top:.3rem; }

.wki-filter { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; margin-bottom:1rem; }
.wki-input {
    border:1px solid var(--app-border); border-radius:.6rem; padding:.45rem .8rem; font-size:.85rem;
    background:transparent; color:var(--app-text); outline:none;
}
.wki-input:focus { border-color:var(--app-brand); }
.wki-input-active { border-color:var(--app-brand); background:rgba(234,88,12,.06); }
.wki-select { appearance:none; padding-right:2rem; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%23888' stroke-width='2'%3E%3Cpolyline points='4 6 8 10 12 6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right .5rem center; }

.wki-btn-brand   { border:none; background:var(--app-brand); color:#fff; border-radius:.6rem; padding:.5rem 1rem; font-size:.85rem; font-weight:600; cursor:pointer; }
.wki-btn-brand:disabled { opacity:.55; cursor:not-allowed; filter:saturate(.7); }
.wki-btn-outline { border:1px solid var(--app-border); background:transparent; color:var(--app-text-muted); border-radius:.5rem; padding:.25rem .6rem; font-size:.75rem; font-weight:600; cursor:pointer; }
.wki-btn-outline:hover { background:var(--app-panel-strong); color:var(--app-text); }

/* Purpose badges */
.wki-badge { display:inline-flex; align-items:center; font-size:.7rem; font-weight:700; padding:.2rem .6rem; border-radius:999px; text-transform:capitalize; letter-spacing:.03em; }
.wki-badge-day_pass    { background:#dbeafe; color:#1d4ed8; }
.wki-badge-free_trial  { background:#d1fae5; color:#065f46; }
.wki-badge-inquiry     { background:#fef9c3; color:#854d0e; }
.wki-badge-guest       { background:#f3e8ff; color:#6b21a8; }

/* Enquiry status badges */
.enq-badge { display:inline-flex; align-items:center; font-size:.66rem; font-weight:700; padding:.15rem .5rem; border-radius:999px; letter-spacing:.02em; margin-top:.25rem; }
.enq-open          { background:#fef9c3; color:#854d0e; }
.enq-followed_up   { background:#dbeafe; color:#1d4ed8; }
.enq-converted     { background:#d1fae5; color:#065f46; }
.enq-closed        { background:#f1f5f9; color:#64748b; }

/* Enquiry action buttons */
.enq-btn { display:inline-flex; align-items:center; gap:.25rem; border:none; border-radius:.4rem; padding:.22rem .55rem; font-size:.72rem; font-weight:700; cursor:pointer; }
.enq-btn-followup { background:rgba(234,88,12,.12); color:#ea580c; }
.enq-btn-followup:hover { background:rgba(234,88,12,.22); }
.enq-fup-chip {
    display:inline-flex; align-items:center; gap:.2rem; font-size:.7rem; font-weight:700;
    border-radius:999px; padding:.15rem .5rem; cursor:pointer;
    background:var(--app-panel-strong); border:1px solid var(--app-border); color:var(--app-text-muted);
    transition:.12s;
}
.enq-fup-chip:hover { border-color:var(--app-brand); color:var(--app-brand); }
.enq-fup-chip.has { background:rgba(234,88,12,.1); border-color:rgba(234,88,12,.3); color:#ea580c; }

/* Today's follow-up filter button */
.wki-fup-today-btn {
    display:inline-flex; align-items:center; gap:.4rem; padding:.42rem .9rem;
    border-radius:.6rem; font-size:.82rem; font-weight:600; text-decoration:none;
    border:1px solid var(--app-border); color:var(--app-text-muted);
    background:transparent; transition:.15s; white-space:nowrap;
}
.wki-fup-today-btn:hover { border-color:var(--app-brand); color:var(--app-brand); }
.wki-fup-today-btn.active { background:rgba(234,88,12,.1); border-color:rgba(234,88,12,.4); color:#ea580c; }
.wki-fup-today-count {
    background:#ea580c; color:#fff; border-radius:999px;
    font-size:.68rem; font-weight:700; padding:.05rem .42rem; min-width:1.2rem; text-align:center;
}

/* Form card */
.wki-form-card { position:sticky; top:1.5rem; }
.wki-form-head { padding:1.25rem 1.5rem; border-bottom:1px solid var(--app-border); }
.wki-form-head h3 { font-size:1rem; font-weight:700; }
.wki-form-body { padding:1.25rem 1.5rem; display:flex; flex-direction:column; gap:.875rem; }
.wki-form-foot { padding:1rem 1.5rem; border-top:1px solid var(--app-border); }
.wki-field label { display:block; font-size:.8rem; font-weight:600; margin-bottom:.3rem; color:var(--app-text-muted); }
.wki-field input, .wki-field select, .wki-field textarea {
    width:100%; border:1px solid var(--app-border); border-radius:.6rem; padding:.5rem .75rem;
    font-size:.85rem; background:transparent; color:var(--app-text); outline:none;
}
.wki-field input:focus, .wki-field select:focus, .wki-field textarea:focus { border-color:var(--app-brand); }
.wki-field .wki-error { font-size:.75rem; color:#ef4444; margin-top:.2rem; }
.wki-pay-line { display:grid; grid-template-columns:auto 1fr minmax(110px,140px) minmax(140px,1fr); gap:.6rem; align-items:center; border:1px solid var(--app-border); border-radius:.8rem; padding:.7rem .8rem; }
@media(max-width:720px){ .wki-pay-line{ grid-template-columns:auto 1fr; } .wki-pay-line input[data-role="amount"], .wki-pay-line input[data-role="reference"]{ grid-column:1 / -1; } }
.wki-pay-check { width:auto !important; }
.wki-pay-label { font-size:.82rem; font-weight:600; color:var(--app-text); }
.wki-pay-summary { font-size:.78rem; color:var(--app-text-muted); margin-top:.45rem; }
.wki-inline-alert {
    display:none; align-items:flex-start; gap:.65rem; margin-top:.75rem; padding:.8rem .9rem;
    border:1px solid rgba(220,38,38,.18); border-radius:.9rem; background:#fff7f7; color:#991b1b;
}
.wki-inline-alert.open { display:flex; }
.wki-inline-alert svg { flex:none; width:1rem; height:1rem; margin-top:.1rem; }
.wki-inline-alert strong { display:block; font-size:.78rem; font-weight:700; }
.wki-inline-alert span { display:block; font-size:.76rem; line-height:1.45; color:#b42318; }

/* Empty state */
.wki-empty { display:flex; flex-direction:column; align-items:center; padding:4rem 1rem; text-align:center; }
.wki-empty-icon {
    background:var(--app-panel-strong); border:1px solid var(--app-border); border-radius:999px;
    color:var(--app-text-muted); height:4.5rem; width:4.5rem;
    display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;
}
.wki-empty-title    { font-size:1.1rem; font-weight:700; }
.wki-empty-subtitle { font-size:.85rem; color:var(--app-text-muted); margin-top:.4rem; max-width:22rem; }

/* Modals */
.wki-modal-backdrop {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:500;
    align-items:center; justify-content:center; padding:1rem;
}
.wki-modal-backdrop.open { display:flex; }
.wki-modal {
    background:var(--app-panel); border:1px solid var(--app-border); border-radius:1.5rem;
    width:100%; max-width:480px; max-height:90vh; overflow-y:auto;
    box-shadow:0 8px 40px rgba(0,0,0,.18);
}
.wki-modal-head { padding:1.25rem 1.5rem; border-bottom:1px solid var(--app-border); display:flex; align-items:center; justify-content:space-between; }
.wki-modal-head h3 { font-size:1rem; font-weight:700; }
.wki-modal-close { border:none; background:none; cursor:pointer; color:var(--app-text-muted); padding:.25rem; border-radius:.4rem; }
.wki-modal-close:hover { background:var(--app-panel-strong); color:var(--app-text); }
.wki-modal-body { padding:1.25rem 1.5rem; display:flex; flex-direction:column; gap:.875rem; }
.wki-modal-foot { padding:1rem 1.5rem; border-top:1px solid var(--app-border); display:flex; gap:.6rem; justify-content:flex-end; }

/* History list */
.wki-hist-item { padding:.75rem 0; border-bottom:1px solid var(--app-border); }
.wki-hist-item:last-child { border-bottom:none; }
.wki-hist-meta  { font-size:.72rem; color:var(--app-text-muted); margin-top:.25rem; }
.wki-hist-notes { font-size:.82rem; color:var(--app-text); margin-top:.3rem; line-height:1.5; }
.wki-hist-next  { font-size:.72rem; font-weight:600; color:#ea580c; margin-top:.2rem; }
.wki-hist-empty { text-align:center; color:var(--app-text-muted); font-size:.85rem; padding:2rem 0; }
</style>


<div class="wki-grid">

    {{-- ── Left: filters + list ─────────────────────────────────────────── --}}
    <div>

        {{-- Stats --}}
        <div class="wki-stat-grid">
            <div class="app-panel wki-stat">
                <div class="wki-stat-label">{{ __('attendance.walkin_stats.total') }}</div>
                <div class="wki-stat-val">{{ number_format($todayTotal) }}</div>
            </div>
            <div class="app-panel wki-stat">
                <div class="wki-stat-label">{{ __('attendance.walkin_stats.revenue') }}</div>
                <div class="wki-stat-val">₹{{ number_format($todayRevenue / 100, 2) }}</div>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('tenant.attendance.walkins') }}" id="wki-filter-form">
            <div class="wki-filter">
                @if(!$todayFollowup && !$followupDate)
                    <input type="date" name="date" value="{{ $date }}" class="wki-input" onchange="this.form.submit()">
                @endif
                @if($branches->isNotEmpty())
                    <select name="branch_id" class="wki-input wki-select" onchange="this.form.submit()">
                        <option value="">{{ __('attendance.walkins.all_branches') }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Follow-up date filter --}}
                <div style="display:flex;align-items:center;gap:.3rem">
                    <label style="font-size:.75rem;font-weight:600;color:var(--app-text-muted);white-space:nowrap">Follow-up date</label>
                    <input type="date" name="followup_date" value="{{ $followupDate }}"
                           class="wki-input {{ $followupDate ? 'wki-input-active' : '' }}"
                           onchange="this.form.submit()">
                    @if($followupDate)
                        <a href="{{ route('tenant.attendance.walkins', array_filter(['branch_id' => request('branch_id'), 'date' => $date])) }}"
                           style="font-size:.8rem;font-weight:600;color:var(--app-brand);text-decoration:none;white-space:nowrap">✕</a>
                    @endif
                </div>

                {{-- Today's follow-up filter button --}}
                <a href="{{ $todayFollowup
                    ? route('tenant.attendance.walkins', array_filter(['branch_id' => request('branch_id')]))
                    : route('tenant.attendance.walkins', array_filter(['today_followup' => 1, 'branch_id' => request('branch_id')])) }}"
                   class="wki-fup-today-btn {{ $todayFollowup ? 'active' : '' }}"
                   title="Follow-ups due today">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Today's Follow-ups
                    @if($todayFollowupCount > 0)
                        <span class="wki-fup-today-count">{{ $todayFollowupCount }}</span>
                    @endif
                </a>
            </div>
        </form>

        @if($todayFollowup || $followupDate)
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;font-size:.82rem;color:var(--app-text-muted)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:#ea580c"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                @if($todayFollowup)
                    Showing enquiries with a follow-up due today
                @else
                    Showing enquiries with follow-up on {{ \Carbon\Carbon::parse($followupDate)->format('d M Y') }}
                @endif
                <a href="{{ route('tenant.attendance.walkins', array_filter(['branch_id' => request('branch_id')])) }}"
                   style="color:var(--app-brand);font-weight:600;text-decoration:none">Clear</a>
            </div>
        @endif

        {{-- Table / empty state --}}
        @if ($logs->isEmpty())
            <div class="app-panel w-full rounded-[2rem] border">
                <div class="wki-empty">
                    <div class="wki-empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-7 w-7"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <p class="wki-empty-title">{{ __('attendance.walkin_empty.title') }}</p>
                    <p class="wki-empty-subtitle">{{ __('attendance.walkin_empty.subtitle') }}</p>
                </div>
            </div>
        @else
            <div class="app-panel w-full overflow-hidden rounded-[2rem] border">
                <div class="w-full overflow-x-auto">
                    <table class="w-full min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-[var(--app-border)] bg-[var(--app-panel-strong)]">
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.walkin_table.visitor') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.walkin_table.purpose') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.walkin_table.fee') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.walkin_table.payment') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.walkin_table.branch') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.walkin_table.time') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">Follow-Up</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--app-border)]">
                            @foreach ($logs as $log)
                                @php $isInquiry = $log->purpose === 'inquiry'; @endphp
                                <tr class="transition hover:bg-[var(--app-panel-strong)]">
                                    <td class="whitespace-nowrap px-5 py-3">
                                        <p class="font-semibold">{{ $log->name }}</p>
                                        <p class="text-xs text-[var(--app-text-muted)]">{{ $log->phone }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="wki-badge wki-badge-{{ $log->purpose }}">{{ __('attendance.purposes.'.$log->purpose) }}</span>
                                        @if($isInquiry)
                                            <span class="enq-badge enq-{{ $log->enquiry_status }}">{{ ucfirst(str_replace('_',' ',$log->enquiry_status)) }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">
                                        @if($log->fee_paise > 0) ₹{{ number_format($log->fee_paise / 100, 2) }} @else — @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">
                                        @if($log->payment_meta)
                                            {{ collect($log->payment_meta)->map(fn ($row) => __('attendance.payment_methods.' . $row['method']) . (!empty($row['reference']) ? ' (' . $row['reference'] . ')' : ''))->implode(', ') }}
                                        @elseif($log->payment_method)
                                            {{ str_contains($log->payment_method, ',')
                                                ? collect(explode(',', $log->payment_method))->map(fn ($method) => __('attendance.payment_methods.' . trim($method)))->implode(', ')
                                                : __('attendance.payment_methods.'.$log->payment_method) }}
                                            @if($log->reference)<span class="text-xs">({{ $log->reference }})</span>@endif
                                        @else — @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">{{ $log->branch?->name ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-[var(--app-text-muted)]">{{ $log->created_at->format('H:i') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        @if($isInquiry)
                                            @php $fupCount = $log->followups->count(); @endphp
                                            <div style="display:flex;flex-direction:column;gap:.3rem;align-items:flex-start">
                                                @if(!in_array($log->enquiry_status, ['converted','closed']))
                                                    <button type="button"
                                                        class="enq-btn enq-btn-followup"
                                                        onclick="openFollowup({{ $log->id }}, '{{ addslashes($log->name) }}', '{{ $log->phone }}')">
                                                        + Follow Up
                                                    </button>
                                                    @if(auth()->user()->canAccess('members.add'))
                                                    <a href="{{ route('tenant.members.create', ['walkin_id' => $log->id]) }}"
                                                       class="enq-btn"
                                                       style="background:rgba(34,197,94,.12);color:#15803d;text-decoration:none">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:10px;height:10px"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 11h-6M19 8v6"/></svg>
                                                        Convert to Member
                                                    </a>
                                                    @endif
                                                @endif
                                                <button type="button"
                                                    class="enq-fup-chip {{ $fupCount > 0 ? 'has' : '' }}"
                                                    onclick="openHistory({{ $log->id }}, '{{ addslashes($log->name) }}')">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:11px;height:11px"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                                    {{ $fupCount }} log{{ $fupCount !== 1 ? 's' : '' }}
                                                </button>
                                                @php $nextDate = $log->followups->whereNotNull('next_followup_date')->first()?->next_followup_date; @endphp
                                                @if($nextDate)
                                                    <span style="font-size:.68rem;font-weight:600;color:{{ $nextDate->isPast() && !$nextDate->isToday() ? '#dc2626' : '#ea580c' }}">
                                                        Next follow-up: {{ $nextDate->isToday() ? 'Today' : ($nextDate->isPast() ? 'Overdue '.$nextDate->format('d M') : $nextDate->format('d M')) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[var(--app-text-muted)]">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="border-t border-[var(--app-border)] px-5 py-3">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>

    {{-- ── Right: add walk-in form ──────────────────────────────────────── --}}
    <div class="app-panel wki-form-card overflow-hidden rounded-[2rem] border">
        <div class="wki-form-head">
            <h3>{{ __('attendance.walkin_form.title') }}</h3>
        </div>

        <form method="POST" action="{{ route('tenant.attendance.walkins.store') }}" id="wki-form">
            @csrf

            <div class="wki-form-body">

                {{-- Name --}}
                <div class="wki-field">
                    <label>{{ __('attendance.walkin_form.name') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('attendance.walkin_form.name_ph') }}" required>
                    @error('name')<p class="wki-error">{{ $message }}</p>@enderror
                </div>

                {{-- Phone --}}
                <div class="wki-field">
                    <label>{{ __('attendance.walkin_form.phone') }} <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="{{ __('attendance.walkin_form.phone_ph') }}" required minlength="10" maxlength="20" inputmode="numeric" pattern="[0-9]{10,20}">
                    @error('phone')<p class="wki-error">{{ $message }}</p>@enderror
                </div>

                {{-- Purpose --}}
                <div class="wki-field">
                    <label>{{ __('attendance.walkin_form.purpose') }} <span class="text-red-500">*</span></label>
                    <select name="purpose" id="wki-purpose" required onchange="wkiPurposeChange()">
                        <option value="" @selected(old('purpose', request('purpose', '')) === '')>Select purpose…</option>
                        @foreach ($purposes as $p)
                            <option value="{{ $p }}" @selected(old('purpose', request('purpose', '')) === $p)>{{ __('attendance.purposes.'.$p) }}</option>
                        @endforeach
                    </select>
                    @error('purpose')<p class="wki-error">{{ $message }}</p>@enderror
                </div>

                {{-- Day pass plan --}}
                <div class="wki-field" id="wki-plan-row" style="display:none">
                    <label>Day Pass Plan <span class="text-red-500">*</span></label>
                    <select name="plan_id" id="wki-plan" class="wki-select" onchange="wkiDayPassPlanChange()">
                        <option value="">Select a one day plan…</option>
                        @foreach ($dayPassPlans as $plan)
                            <option value="{{ $plan->id }}"
                                data-fee="{{ $plan->total_price_paise }}"
                                @selected((string) $selectedDayPassPlanId === (string) $plan->id)>
                                {{ $plan->name }} - ₹{{ number_format($plan->total_price_paise / 100, 2) }}
                                @if($plan->gst_amount_paise > 0)
                                    (incl. GST)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @if($dayPassPlans->isEmpty())
                        <p class="wki-error">No active one day membership plan found.</p>
                    @endif
                    @error('plan_id')<p class="wki-error">{{ $message }}</p>@enderror
                </div>

                {{-- Guest of (conditional) --}}
                <div class="wki-field" id="wki-guest-row" style="display:none">
                    <label>{{ __('attendance.walkin_form.guest_of') }}</label>
                    <select name="guest_of_id" class="wki-select">
                        <option value="">{{ __('attendance.walkin_form.guest_of_ph') }}</option>
                        @foreach ($members as $m)
                            <option value="{{ $m->id }}" @selected(old('guest_of_id') == $m->id)>{{ $m->name }} ({{ $m->member_code }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Fee --}}
                <div class="wki-field" id="wki-fee-row" style="display:none">
                    <label>{{ __('attendance.walkin_form.fee') }}</label>
                    <input type="number" id="wki-fee-display" name="_fee_display" value="{{ old('_fee_display', 0) }}" min="0" step="0.01" placeholder="{{ __('attendance.walkin_form.fee_ph') }}">
                    <input type="hidden" name="fee_paise" id="wki-fee-paise" value="{{ old('fee_paise', 0) }}">
                    @error('fee_paise')<p class="wki-error">{{ $message }}</p>@enderror
                </div>

                {{-- Payment method (day pass only) --}}
                <div class="wki-field" id="wki-payment-row" style="display:none">
                    <label>{{ __('attendance.walkin_form.payment_method') }}</label>
                    <div style="display:flex;flex-direction:column;gap:.6rem">
                        @foreach ($methods as $m)
                            <div class="wki-pay-line">
                                <input class="wki-pay-check" type="checkbox" name="payment_methods[]" value="{{ $m }}"
                                        onchange="wkiTogglePaymentLine(this)"
                                        @checked(in_array($m, old('payment_methods', []), true))>
                                <span class="wki-pay-label">{{ __('attendance.payment_methods.'.$m) }}</span>
                                <input type="number"
                                    name="amounts[{{ $m }}]"
                                    value="{{ old('amounts.'.$m) }}"
                                    min="0"
                                    step="0.01"
                                    placeholder="Amount"
                                    data-role="amount"
                                    data-amount-for="{{ $m }}"
                                    {{ in_array($m, old('payment_methods', []), true) ? '' : 'disabled' }}
                                    oninput="wkiUpdatePaymentSummary()">
                                <input type="text"
                                    name="references[{{ $m }}]"
                                    value="{{ old('references.'.$m) }}"
                                    placeholder="{{ __('attendance.walkin_form.reference_ph') }}"
                                    data-ref-for="{{ $m }}"
                                    data-role="reference"
                                    {{ in_array($m, old('payment_methods', []), true) ? '' : 'disabled' }}
                                    style="display:{{ in_array($m, old('payment_methods', []), true) ? 'block' : 'none' }}">
                            </div>
                        @endforeach
                    </div>
                    <p id="wki-payment-summary" class="wki-pay-summary">Selected total: ₹0.00 / Fee: ₹0.00</p>
                    <div id="wki-payment-alert" class="wki-inline-alert" role="alert" aria-live="polite">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.5a.75.75 0 0 0-1.5 0v4.25a.75.75 0 0 0 1.5 0V6.5Zm0 7a.75.75 0 1 0-1.5 0 .75.75 0 0 0 1.5 0Z" clip-rule="evenodd"/></svg>
                        <div>
                            <strong>Payment total mismatch</strong>
                            <span>The combined amount across selected payment methods must exactly match the day pass fee.</span>
                        </div>
                    </div>
                    @error('payment_methods')<p class="wki-error">{{ $message }}</p>@enderror
                    @error('payment_methods.*')<p class="wki-error">{{ $message }}</p>@enderror
                    @error('amounts.*')<p class="wki-error">{{ $message }}</p>@enderror
                </div>

                {{-- Branch --}}
                @if($branches->count() > 1)
                <div class="wki-field">
                    <label>{{ __('attendance.walkin_form.branch') }}</label>
                    <select name="branch_id" class="wki-select">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Notes --}}
                <div class="wki-field">
                    <label>{{ __('attendance.walkin_form.notes') }}</label>
                    <textarea name="notes" rows="2" placeholder="{{ __('attendance.walkin_form.notes_ph') }}">{{ old('notes') }}</textarea>
                </div>

            </div>

            <div class="wki-form-foot">
                <button type="submit" id="wki-submit" class="wki-btn-brand w-full">
                    {{ __('attendance.walkin_form.submit') }}
                </button>
            </div>
        </form>
    </div>

</div>

{{-- ── Follow-up Log Modal ─────────────────────────────────────────────────── --}}
<div id="fup-modal" class="wki-modal-backdrop" onclick="if(event.target===this)closeFup()">
    <div class="wki-modal">
        <div class="wki-modal-head">
            <div>
                <h3>Log Follow-Up</h3>
                <p id="fup-visitor-name" style="font-size:.8rem;color:var(--app-text-muted);margin-top:.15rem"></p>
            </div>
            <button type="button" class="wki-modal-close" onclick="closeFup()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="fup-form" method="POST">
            @csrf
            <div class="wki-modal-body">
                <div class="wki-field">
                    <label>Outcome <span class="text-red-500">*</span></label>
                    <select name="outcome" required class="wki-select">
                        <option value="">Select outcome…</option>
                        <option value="called">Called — Spoke with them</option>
                        <option value="visited">Visited — They came in</option>
                        <option value="messaged">Messaged — WhatsApp / SMS</option>
                        <option value="no_answer">No Answer — Didn't pick up</option>
                        <option value="not_interested">Not Interested — Closed</option>
                        <option value="converted">Converted — Joined as member</option>
                    </select>
                </div>
                <div class="wki-field">
                    <label>Notes</label>
                    <textarea name="notes" rows="3" placeholder="What was discussed, any commitment, etc."></textarea>
                </div>
                <div class="wki-field">
                    <label>Next Follow-Up Date <span style="font-weight:400;color:var(--app-text-muted)">(optional)</span></label>
                    <input type="date" name="next_followup_date" min="{{ now()->toDateString() }}">
                </div>
            </div>
            <div class="wki-modal-foot">
                <button type="button" class="wki-btn-outline" onclick="closeFup()">Cancel</button>
                <button type="submit" class="wki-btn-brand">Save Follow-Up</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Follow-up History Modal ─────────────────────────────────────────────── --}}
<div id="hist-modal" class="wki-modal-backdrop" onclick="if(event.target===this)closeHist()">
    <div class="wki-modal" style="max-width:520px">
        <div class="wki-modal-head">
            <div>
                <h3>Follow-Up History</h3>
                <p id="hist-visitor-name" style="font-size:.8rem;color:var(--app-text-muted);margin-top:.15rem"></p>
            </div>
            <button type="button" class="wki-modal-close" onclick="closeHist()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="wki-modal-body" id="hist-body" style="gap:0;padding-top:.75rem;padding-bottom:.75rem">
            <p class="wki-hist-empty">Loading…</p>
        </div>
    </div>
</div>

<script>
    const WKI_DAY_PASS_PLANS = @json(
        $dayPassPlans->map(fn ($plan) => [
            'id' => (string) $plan->id,
            'fee_paise' => $plan->total_price_paise,
        ])->values()
    );

    // ── Walk-in form ──────────────────────────────────────────────────────────
    function wkiPurposeChange() {
        const p = document.getElementById('wki-purpose').value;
        document.getElementById('wki-guest-row').style.display = p === 'guest' ? 'block' : 'none';
        document.getElementById('wki-plan-row').style.display = p === 'day_pass' ? 'block' : 'none';
        document.getElementById('wki-fee-row').style.display = p === 'day_pass' ? 'block' : 'none';

        const feeInput = document.getElementById('wki-fee-display');
        const planSelect = document.getElementById('wki-plan');

        if (p === 'day_pass') {
            feeInput.readOnly = true;
            if (WKI_DAY_PASS_PLANS.length === 1 && !planSelect.value) {
                planSelect.value = WKI_DAY_PASS_PLANS[0].id;
            }
            wkiDayPassPlanChange();
            return;
        }

        feeInput.readOnly = false;
        feeInput.value = '0.00';
        if (planSelect) {
            planSelect.value = '';
        }
        document.querySelectorAll('input[name="payment_methods[]"]').forEach((checkbox) => {
            checkbox.checked = false;
        });
        document.querySelectorAll('[data-amount-for]').forEach((input) => {
            input.value = '';
            input.disabled = true;
        });
        document.querySelectorAll('[data-ref-for]').forEach((input) => {
            input.style.display = 'none';
            input.value = '';
            input.disabled = true;
        });
        wkiSyncFeeFields();
    }

    function wkiSyncFeeFields() {
        const v = parseFloat(document.getElementById('wki-fee-display').value) || 0;
        document.getElementById('wki-fee-paise').value = Math.round(v * 100);
        document.getElementById('wki-payment-row').style.display = document.getElementById('wki-purpose').value === 'day_pass' && v > 0 ? 'block' : 'none';
        wkiUpdatePaymentSummary();
    }

    function wkiDayPassPlanChange() {
        const planSelect = document.getElementById('wki-plan');
        const opt = planSelect?.options[planSelect.selectedIndex];
        const feePaise = parseInt(opt?.dataset.fee || 0, 10);
        document.getElementById('wki-fee-display').value = feePaise > 0 ? (feePaise / 100).toFixed(2) : '0.00';
        wkiSyncFeeFields();
    }

    document.getElementById('wki-fee-display')?.addEventListener('input', function () {
        if (document.getElementById('wki-purpose').value === 'day_pass') {
            wkiDayPassPlanChange();
            return;
        }
        wkiSyncFeeFields();
    });
    function wkiTogglePaymentLine(checkbox) {
        const amountInput = document.querySelector('[data-amount-for="' + checkbox.value + '"]');
        const refInput = document.querySelector('[data-ref-for="' + checkbox.value + '"]');
        if (amountInput) {
            amountInput.disabled = !checkbox.checked;
            if (!checkbox.checked) {
                amountInput.value = '';
            }
        }
        if (!refInput) {
            wkiUpdatePaymentSummary();
            return;
        }
        refInput.disabled = !checkbox.checked;
        refInput.style.display = checkbox.checked ? 'block' : 'none';
        if (!checkbox.checked) {
            refInput.value = '';
        }
        wkiUpdatePaymentSummary();
    }
    function wkiUpdatePaymentSummary() {
        let selectedTotal = 0;
        document.querySelectorAll('input[name="payment_methods[]"]:checked').forEach((checkbox) => {
            const amountInput = document.querySelector('[data-amount-for="' + checkbox.value + '"]');
            selectedTotal += parseFloat(amountInput?.value || 0) || 0;
        });
        const fee = parseFloat(document.getElementById('wki-fee-display').value) || 0;
        const summary = document.getElementById('wki-payment-summary');
        const submitButton = document.getElementById('wki-submit');
        const isDayPass = document.getElementById('wki-purpose').value === 'day_pass';
        const checkedCount = document.querySelectorAll('input[name="payment_methods[]"]:checked').length;
        const matched = Math.abs(selectedTotal - fee) < 0.009;
        if (summary) {
            summary.textContent = 'Selected total: ₹' + selectedTotal.toFixed(2) + ' / Fee: ₹' + fee.toFixed(2);
            summary.style.color = matched ? '#16a34a' : 'var(--app-text-muted)';
        }
        const hasMismatch = isDayPass && fee > 0 && checkedCount > 0 && !matched;
        if (submitButton) {
            submitButton.disabled = hasMismatch;
        }
        wkiTogglePaymentAlert(hasMismatch);
    }
    function wkiTogglePaymentAlert(show) {
        const alertBox = document.getElementById('wki-payment-alert');
        if (!alertBox) return;
        alertBox.classList.toggle('open', !!show);
    }
    document.getElementById('wki-form')?.addEventListener('submit', function (event) {
        wkiSyncFeeFields();
        if (document.getElementById('wki-purpose').value === 'day_pass') {
            let selectedTotal = 0;
            document.querySelectorAll('input[name="payment_methods[]"]:checked').forEach((checkbox) => {
                const amountInput = document.querySelector('[data-amount-for="' + checkbox.value + '"]');
                selectedTotal += parseFloat(amountInput?.value || 0) || 0;
            });
            const fee = parseFloat(document.getElementById('wki-fee-display').value) || 0;
            if (Math.abs(selectedTotal - fee) >= 0.009) {
                event.preventDefault();
                wkiTogglePaymentAlert(true);
                document.getElementById('wki-payment-row')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    wkiPurposeChange();

    // ── Follow-up modal ───────────────────────────────────────────────────────
    const fupBaseUrl = '{{ url("attendance/walkins") }}';

    function openFollowup(walkInId, name, phone) {
        document.getElementById('fup-visitor-name').textContent = name + ' · ' + phone;
        const form = document.getElementById('fup-form');
        form.action = fupBaseUrl + '/' + walkInId + '/followup';
        form.querySelector('select[name="outcome"]').value = '';
        form.querySelector('textarea[name="notes"]').value = '';
        form.querySelector('input[name="next_followup_date"]').value = '';
        document.getElementById('fup-modal').classList.add('open');
    }
    function closeFup() { document.getElementById('fup-modal').classList.remove('open'); }

    // ── History modal ─────────────────────────────────────────────────────────
    const OUTCOME_ICONS = { called:'📞', visited:'🏋️', messaged:'💬', no_answer:'🔕', not_interested:'❌', converted:'✅' };

    function openHistory(walkInId, name) {
        document.getElementById('hist-visitor-name').textContent = name;
        document.getElementById('hist-body').innerHTML = '<p class="wki-hist-empty">Loading…</p>';
        document.getElementById('hist-modal').classList.add('open');

        fetch(fupBaseUrl + '/' + walkInId + '/followup-history', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const body = document.getElementById('hist-body');
            if (!data.history.length) {
                body.innerHTML = '<p class="wki-hist-empty">No follow-ups logged yet.</p>';
                return;
            }
            body.innerHTML = data.history.map(f => {
                const icon  = OUTCOME_ICONS[f.outcome] || '•';
                const label = f.outcome.replace(/_/g, ' ');
                const notes = f.notes ? '<p class="wki-hist-notes">' + esc(f.notes) + '</p>' : '';
                const next  = f.next_followup_date ? '<p class="wki-hist-next">Next: ' + esc(f.next_followup_date) + '</p>' : '';
                return '<div class="wki-hist-item">'
                    + '<div style="display:flex;align-items:center;gap:.4rem">'
                    + '<span style="font-size:1rem">' + icon + '</span>'
                    + '<span style="font-weight:700;font-size:.85rem;text-transform:capitalize">' + esc(label) + '</span>'
                    + '</div>'
                    + notes + next
                    + '<p class="wki-hist-meta">' + esc(f.logged_by) + ' · ' + esc(f.created_at) + '</p>'
                    + '</div>';
            }).join('');
        })
        .catch(() => {
            document.getElementById('hist-body').innerHTML = '<p class="wki-hist-empty">Failed to load.</p>';
        });
    }
    function closeHist() { document.getElementById('hist-modal').classList.remove('open'); }
    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>

</x-layouts.admin>
