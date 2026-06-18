<x-layouts.admin
    title="{{ __('attendance.walkins.title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('attendance.walkins.title') }}"
    subheading="{{ __('attendance.walkins.subtitle') }}"
>

<style>
/* ── Walk-in page ────────────────────────────────────────────── */
.wki-tab { display:flex; gap:.5rem; margin-bottom:1.25rem; }
.wki-tab a {
    padding:.45rem 1.1rem; border-radius:999px; font-size:.8rem; font-weight:600;
    border:1px solid var(--app-border); color:var(--app-text-muted); text-decoration:none;
    transition:background .15s,color .15s;
}
.wki-tab a:hover  { background:var(--app-panel-strong); color:var(--app-text); }
.wki-tab a.active { background:var(--app-brand); border-color:var(--app-brand); color:#fff; }

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
.wki-select { appearance:none; padding-right:2rem; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%23888' stroke-width='2'%3E%3Cpolyline points='4 6 8 10 12 6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right .5rem center; }

.wki-btn-brand { border:none; background:var(--app-brand); color:#fff; border-radius:.6rem; padding:.5rem 1rem; font-size:.85rem; font-weight:600; cursor:pointer; }
.wki-btn-outline { border:1px solid var(--app-border); background:transparent; color:var(--app-text-muted); border-radius:.5rem; padding:.25rem .6rem; font-size:.75rem; font-weight:600; cursor:pointer; }
.wki-btn-outline:hover { background:var(--app-panel-strong); color:var(--app-text); }

/* Purpose badges */
.wki-badge { display:inline-flex; align-items:center; font-size:.7rem; font-weight:700; padding:.2rem .6rem; border-radius:999px; text-transform:capitalize; letter-spacing:.03em; }
.wki-badge-day_pass    { background:#dbeafe; color:#1d4ed8; }
.wki-badge-free_trial  { background:#d1fae5; color:#065f46; }
.wki-badge-inquiry     { background:#fef9c3; color:#854d0e; }
.wki-badge-guest       { background:#f3e8ff; color:#6b21a8; }

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

/* Empty state */
.wki-empty { display:flex; flex-direction:column; align-items:center; padding:4rem 1rem; text-align:center; }
.wki-empty-icon {
    background:var(--app-panel-strong); border:1px solid var(--app-border); border-radius:999px;
    color:var(--app-text-muted); height:4.5rem; width:4.5rem;
    display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;
}
.wki-empty-title    { font-size:1.1rem; font-weight:700; }
.wki-empty-subtitle { font-size:.85rem; color:var(--app-text-muted); margin-top:.4rem; max-width:22rem; }
</style>

{{-- ── Tab navigation ────────────────────────────────────────────────────── --}}
<div class="wki-tab">
    <a href="{{ route('tenant.attendance.checkins') }}">
        {{ __('attendance.nav.checkins') }}
    </a>
    <a href="{{ route('tenant.attendance.walkins') }}" class="active">
        {{ __('attendance.nav.walkins') }}
    </a>
</div>

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
        <form method="GET" action="{{ route('tenant.attendance.walkins') }}">
            <div class="wki-filter">
                <input type="date" name="date" value="{{ $date }}" class="wki-input" onchange="this.form.submit()">
                @if($branches->isNotEmpty())
                    <select name="branch_id" class="wki-input wki-select" onchange="this.form.submit()">
                        <option value="">{{ __('attendance.walkins.all_branches') }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </form>

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
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.walkin_table.guest_of') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.walkin_table.branch') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[var(--app-text-muted)]">{{ __('attendance.walkin_table.time') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--app-border)]">
                            @foreach ($logs as $log)
                                <tr class="transition hover:bg-[var(--app-panel-strong)]">
                                    <td class="whitespace-nowrap px-5 py-3">
                                        <p class="font-semibold">{{ $log->name }}</p>
                                        <p class="text-xs text-[var(--app-text-muted)]">{{ $log->phone }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="wki-badge wki-badge-{{ $log->purpose }}">{{ __('attendance.purposes.'.$log->purpose) }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">
                                        @if($log->fee_paise > 0)
                                            ₹{{ number_format($log->fee_paise / 100, 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">
                                        @if($log->payment_method)
                                            {{ __('attendance.payment_methods.'.$log->payment_method) }}
                                            @if($log->reference)
                                                <span class="text-xs">({{ $log->reference }})</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">
                                        {{ $log->guestOf?->name ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-[var(--app-text-muted)]">
                                        {{ $log->branch?->name ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-[var(--app-text-muted)]">
                                        {{ $log->created_at->format('H:i') }}
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
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="{{ __('attendance.walkin_form.phone_ph') }}" required>
                    @error('phone')<p class="wki-error">{{ $message }}</p>@enderror
                </div>

                {{-- Purpose --}}
                <div class="wki-field">
                    <label>{{ __('attendance.walkin_form.purpose') }} <span class="text-red-500">*</span></label>
                    <select name="purpose" id="wki-purpose" required onchange="wkiPurposeChange()">
                        @foreach ($purposes as $p)
                            <option value="{{ $p }}" @selected(old('purpose') === $p)>{{ __('attendance.purposes.'.$p) }}</option>
                        @endforeach
                    </select>
                    @error('purpose')<p class="wki-error">{{ $message }}</p>@enderror
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
                <div class="wki-field">
                    <label>{{ __('attendance.walkin_form.fee') }}</label>
                    <input type="number" id="wki-fee-display" name="_fee_display" value="{{ old('_fee_display', 0) }}" min="0" step="0.01" placeholder="{{ __('attendance.walkin_form.fee_ph') }}">
                    <input type="hidden" name="fee_paise" id="wki-fee-paise" value="{{ old('fee_paise', 0) }}">
                    @error('fee_paise')<p class="wki-error">{{ $message }}</p>@enderror
                </div>

                {{-- Payment method (shown when fee > 0) --}}
                <div class="wki-field" id="wki-payment-row" style="display:{{ old('_fee_display', 0) > 0 ? 'block' : 'none' }}">
                    <label>{{ __('attendance.walkin_form.payment_method') }}</label>
                    <select name="payment_method" class="wki-select">
                        <option value="">—</option>
                        @foreach ($methods as $m)
                            <option value="{{ $m }}" @selected(old('payment_method') === $m)>{{ __('attendance.payment_methods.'.$m) }}</option>
                        @endforeach
                    </select>
                    @error('payment_method')<p class="wki-error">{{ $message }}</p>@enderror
                </div>

                {{-- Reference --}}
                <div class="wki-field" id="wki-ref-row" style="display:{{ old('_fee_display', 0) > 0 ? 'block' : 'none' }}">
                    <label>{{ __('attendance.walkin_form.reference') }}</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" placeholder="{{ __('attendance.walkin_form.reference_ph') }}">
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
                <button type="submit" class="wki-btn-brand w-full">
                    {{ __('attendance.walkin_form.submit') }}
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function wkiPurposeChange() {
        const p = document.getElementById('wki-purpose').value;
        document.getElementById('wki-guest-row').style.display = p === 'guest' ? 'block' : 'none';
    }

    document.getElementById('wki-fee-display')?.addEventListener('input', function () {
        const v = parseFloat(this.value) || 0;
        document.getElementById('wki-fee-paise').value = Math.round(v * 100);
        const show = v > 0;
        document.getElementById('wki-payment-row').style.display = show ? 'block' : 'none';
        document.getElementById('wki-ref-row').style.display = show ? 'block' : 'none';
    });

    document.getElementById('wki-form')?.addEventListener('submit', function () {
        const display = document.getElementById('wki-fee-display');
        const paise   = document.getElementById('wki-fee-paise');
        const rupees  = parseFloat(display.value) || 0;
        paise.value   = Math.round(rupees * 100);
    });

    // Init guest row
    wkiPurposeChange();
</script>

</x-layouts.admin>
