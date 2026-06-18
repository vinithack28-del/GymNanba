<x-layouts.admin title="{{ __('renewals.title') }}">

@php
    $tabs = [
        'all'     => __('renewals.tabs.all'),
        'expired' => __('renewals.tabs.expired'),
        'today'   => __('renewals.tabs.today'),
        '3days'   => __('renewals.tabs.3days'),
        '7days'   => __('renewals.tabs.7days'),
        '30days'  => __('renewals.tabs.30days'),
        'custom'  => __('renewals.tabs.custom'),
    ];
@endphp

{{-- ── Stats cards ──────────────────────────────────────────────────────────── --}}
<div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
    @php
        $statCards = [
            ['label' => __('renewals.stats.expired'),     'value' => $stats['expired'],     'tab' => 'expired', 'color' => '#E24B4A'],
            ['label' => __('renewals.stats.today'),       'value' => $stats['today'],       'tab' => 'today',   'color' => '#f97316'],
            ['label' => __('renewals.stats.seven_days'),  'value' => $stats['seven_days'],  'tab' => '7days',   'color' => '#EAB308'],
            ['label' => __('renewals.stats.thirty_days'), 'value' => $stats['thirty_days'], 'tab' => '30days',  'color' => 'var(--app-text)'],
        ];
    @endphp
    @foreach ($statCards as $card)
        <a href="{{ route('tenant.renewals.index', ['tab' => $card['tab']]) }}"
           class="app-panel rn-stat-card rounded-2xl border p-4 transition hover:opacity-90 {{ $tab === $card['tab'] ? 'rn-stat-active' : '' }}">
            <p class="app-muted text-xs font-medium uppercase tracking-[0.2em]">{{ $card['label'] }}</p>
            <p class="mt-2 text-2xl font-semibold" style="color: {{ $card['color'] }}">{{ number_format($card['value']) }}</p>
        </a>
    @endforeach
</div>

{{-- ── Toolbar: tabs + filters + export ────────────────────────────────────── --}}
<div class="app-panel mb-4 rounded-2xl border p-3">
    <div class="flex flex-wrap items-center gap-2">

        {{-- Tabs --}}
        <div class="rn-tabs">
            @foreach ($tabs as $val => $label)
                <a href="{{ route('tenant.renewals.index', array_merge(request()->except(['tab','from','to','page']), ['tab' => $val])) }}"
                   class="rn-tab {{ $tab === $val ? 'rn-tab-active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="ml-auto flex flex-wrap items-center gap-2">
            {{-- Custom date range --}}
            @if ($tab === 'custom')
                <form method="GET" action="{{ route('tenant.renewals.index') }}" class="flex items-center gap-1">
                    <input type="hidden" name="tab" value="custom">
                    <input type="date" name="from" value="{{ $from }}" class="rn-filter-select text-xs">
                    <span class="app-muted text-xs">to</span>
                    <input type="date" name="to"   value="{{ $to }}" class="rn-filter-select text-xs">
                    <button type="submit" class="rn-btn-ghost text-xs">Apply</button>
                </form>
            @endif

            {{-- Plan filter --}}
            <form method="GET" action="{{ route('tenant.renewals.index') }}" id="filter-form">
                <input type="hidden" name="tab" value="{{ $tab }}">
                @if($from) <input type="hidden" name="from" value="{{ $from }}"> @endif
                @if($to)   <input type="hidden" name="to"   value="{{ $to }}"> @endif
                <select name="plan_id" onchange="document.getElementById('filter-form').submit()" class="rn-filter-select">
                    <option value="">{{ __('renewals.filters.all_plans') }}</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            {{-- Export --}}
            <a href="{{ route('tenant.renewals.index', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="rn-btn-ghost text-xs">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                {{ __('renewals.export') }}
            </a>
        </div>
    </div>
</div>

{{-- ── Table ────────────────────────────────────────────────────────────────── --}}
<div class="app-panel rounded-[2rem] border overflow-hidden">
    @if ($members->isEmpty())
        <div class="flex flex-col items-center gap-4 py-20 text-center">
            <div class="rn-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
            </div>
            <p class="text-base font-semibold">{{ __('renewals.empty.no_renewals') }}</p>
            <p class="app-muted text-sm">{{ __('renewals.empty.description') }}</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="rn-table">
                <thead>
                    <tr>
                        <th class="rn-th w-9"><input type="checkbox" id="select-all" class="rn-checkbox"></th>
                        <th class="rn-th">{{ __('renewals.table.member') }}</th>
                        <th class="rn-th">{{ __('renewals.table.member_id') }}</th>
                        <th class="rn-th">{{ __('renewals.table.phone') }}</th>
                        <th class="rn-th">{{ __('renewals.table.plan') }}</th>
                        <th class="rn-th">{{ __('renewals.table.expiry') }}</th>
                        <th class="rn-th">{{ __('renewals.table.days_status') }}</th>
                        <th class="rn-th">{{ __('renewals.table.balance') }}</th>
                        <th class="rn-th"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $member)
                        @php
                            $expiry    = $member->expiry_date;
                            $diffDays  = (int) now()->startOfDay()->diffInDays($expiry->startOfDay(), false);
                            $isExpired = $diffDays < 0;
                            $isToday   = $diffDays === 0;

                            if ($isExpired) {
                                $statusText  = __('renewals.status.days_overdue', ['days' => abs($diffDays)]);
                                $statusColor = '#E24B4A';
                            } elseif ($isToday) {
                                $statusText  = __('renewals.status.today');
                                $statusColor = '#f97316';
                            } else {
                                $statusText  = __('renewals.status.days_left', ['days' => $diffDays]);
                                $statusColor = $diffDays <= 7 ? '#EAB308' : '#1D9E75';
                            }

                            // default start = day after expiry, or today if expired
                            $renewStart = $isExpired ? $today : $expiry->addDay()->toDateString();
                        @endphp
                        <tr class="rn-row">
                            <td class="rn-td"><input type="checkbox" class="rn-checkbox row-cb" value="{{ $member->id }}"></td>
                            <td class="rn-td">
                                <div class="flex items-center gap-2.5">
                                    <span class="rn-avatar">{{ $member->initials }}</span>
                                    <div>
                                        <p class="font-medium text-sm">{{ $member->name }}</p>
                                        @if ($member->email)
                                            <p class="app-muted text-xs">{{ $member->email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="rn-td"><span class="rn-code">{{ $member->member_code }}</span></td>
                            <td class="rn-td"><span class="app-muted text-sm">{{ $member->phone }}</span></td>
                            <td class="rn-td">
                                <span class="text-sm">{{ $member->plan_name ?? '—' }}</span>
                            </td>
                            <td class="rn-td">
                                <span class="text-sm {{ $isExpired ? 'font-semibold' : '' }}"
                                      style="{{ $isExpired ? 'color:#E24B4A' : ($isToday ? 'color:#f97316' : '') }}">
                                    @if ($isToday)
                                        <span class="rn-today-badge">Today</span>
                                    @else
                                        {{ $expiry->format('d M Y') }}
                                    @endif
                                </span>
                            </td>
                            <td class="rn-td">
                                <span class="text-xs font-semibold" style="color: {{ $statusColor }}">{{ $statusText }}</span>
                            </td>
                            <td class="rn-td">
                                @if ($member->balance_paise > 0)
                                    <span class="font-semibold text-sm" style="color:#E24B4A">{{ $member->balance_rupees }}</span>
                                @else
                                    <span class="app-muted text-sm">₹0</span>
                                @endif
                            </td>
                            <td class="rn-td text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" class="rn-btn-renew"
                                        data-id="{{ $member->id }}"
                                        data-name="{{ $member->name }}"
                                        data-plan="{{ $member->plan_id }}"
                                        data-start="{{ $renewStart }}"
                                        onclick="openRenewDrawer(this)">
                                        {{ __('renewals.actions.renew') }}
                                    </button>
                                    <div class="rn-actions-wrap">
                                        <button type="button" class="rn-action-btn" aria-label="More">
                                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                                        </button>
                                        <div class="rn-actions-menu">
                                            <a href="{{ route('tenant.members.edit', $member) }}" class="rn-action-item">{{ __('members.actions.edit') }}</a>
                                            <a href="#" class="rn-action-item">{{ __('renewals.actions.view_profile') }}</a>
                                        </div>
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
            {{ $members->links() }}
        </div>
    @endif
</div>

{{-- ── Renew Now Drawer ─────────────────────────────────────────────────────── --}}
<div id="rn-overlay" class="rn-overlay" aria-hidden="true"></div>
<aside id="rn-drawer" class="rn-drawer" role="dialog" aria-modal="true">
    <div class="rn-drawer-header">
        <div>
            <h2 class="text-base font-semibold">{{ __('renewals.drawer.title') }}</h2>
            <p id="rn-member-name" class="app-muted text-xs mt-0.5"></p>
        </div>
        <button type="button" id="rn-close" class="rn-drawer-close" aria-label="Close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <form id="rn-form" method="POST" action="" class="rn-drawer-body">
        @csrf @method('POST')

        {{-- Plan --}}
        <p class="rn-section">{{ __('renewals.drawer.plan') }}</p>
        <div class="rn-field">
            <label class="rn-label" for="rn-plan">{{ __('renewals.drawer.plan') }} <span class="rn-req">*</span></label>
            <select id="rn-plan" name="plan_id" class="rn-input" required>
                <option value="">{{ __('renewals.drawer.select_plan') }}</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}"
                        data-duration-type="{{ $plan->duration_type }}"
                        data-duration-value="{{ $plan->duration_value }}"
                        data-duration-days="{{ $plan->duration_days }}"
                        data-price="{{ $plan->price_paise }}">
                        {{ $plan->name }} — ₹{{ number_format($plan->price_paise / 100, 0) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="rn-form-row">
            <div class="rn-field">
                <label class="rn-label" for="rn-start">{{ __('renewals.drawer.start_date') }} <span class="rn-req">*</span></label>
                <input id="rn-start" type="date" name="start_date" class="rn-input" required>
            </div>
            <div class="rn-field">
                <label class="rn-label">{{ __('renewals.drawer.expiry_preview') }}</label>
                <p id="rn-expiry-preview" class="rn-preview">—</p>
            </div>
        </div>

        {{-- Payment --}}
        <p class="rn-section mt-4">{{ __('members.form.payment') }}</p>

        <div class="rn-form-row">
            <div class="rn-field">
                <label class="rn-label" for="rn-amount">{{ __('renewals.drawer.payment_amount') }}</label>
                <div class="rn-prefix-wrap">
                    <span class="rn-prefix">₹</span>
                    <input id="rn-amount" type="number" name="payment_amount" min="0" step="0.01"
                        class="rn-input rn-with-prefix" placeholder="0.00">
                </div>
                <span id="rn-plan-price" class="rn-hint"></span>
            </div>
            <div class="rn-field">
                <label class="rn-label" for="rn-method">{{ __('renewals.drawer.payment_method') }}</label>
                <select id="rn-method" name="payment_method" class="rn-input">
                    <option value="">{{ __('renewals.drawer.select_method') }}</option>
                    @foreach (['cash', 'upi', 'card', 'bank', 'cheque'] as $val)
                        <option value="{{ $val }}">{{ __('renewals.drawer.methods.'.$val) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Notes --}}
        <div class="rn-field">
            <label class="rn-label" for="rn-notes">{{ __('renewals.drawer.notes') }}</label>
            <textarea id="rn-notes" name="notes" rows="2" maxlength="300"
                placeholder="Optional" class="rn-input rn-textarea"></textarea>
        </div>

        <div class="rn-drawer-footer">
            <button type="button" id="rn-cancel" class="rn-btn-ghost">{{ __('renewals.drawer.btn_cancel') }}</button>
            <button type="submit" class="rn-btn-primary">{{ __('renewals.drawer.btn_renew') }}</button>
        </div>
    </form>
</aside>

{{-- ── CSS ─────────────────────────────────────────────────────────────────── --}}
@push('styles')
<style>
/* Stats */
.rn-stat-card { cursor: pointer; }
.rn-stat-active { box-shadow: inset 0 0 0 2px var(--app-brand); }

/* Tabs */
.rn-tabs { display: flex; gap: 0.1rem; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.9rem; padding: 0.2rem; flex-wrap: wrap; }
.rn-tab { align-items: center; border-radius: 0.65rem; color: var(--app-text-muted); display: inline-flex; font-size: 0.78rem; font-weight: 500; padding: 0.28rem 0.65rem; text-decoration: none; transition: background 140ms, color 140ms; white-space: nowrap; }
.rn-tab:hover { color: var(--app-text); }
.rn-tab-active { background: var(--app-panel); box-shadow: 0 1px 4px rgba(0,0,0,.12); color: var(--app-text); font-weight: 600; }

/* Filter controls */
.rn-filter-select { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.65rem; color: var(--app-text); font-size: 0.8rem; outline: none; padding: 0.42rem 0.7rem; }
.rn-btn-ghost { align-items: center; background: transparent; border: 1px solid var(--app-border); border-radius: 0.65rem; color: var(--app-text-muted); display: inline-flex; font-size: 0.8rem; font-weight: 500; gap: 0.3rem; padding: 0.42rem 0.8rem; text-decoration: none; transition: background 140ms; white-space: nowrap; cursor: pointer; }
.rn-btn-ghost:hover { background: color-mix(in srgb, var(--app-border) 60%, transparent); color: var(--app-text); }

/* Table */
.rn-table { border-collapse: collapse; font-size: 0.82rem; width: 100%; }
.rn-th { border-bottom: 1px solid var(--app-border); color: var(--app-text-muted); font-size: 0.68rem; font-weight: 700; letter-spacing: 0.08em; padding: 0.65rem 0.85rem; text-align: left; text-transform: uppercase; white-space: nowrap; }
.rn-td { border-bottom: 1px solid color-mix(in srgb, var(--app-border) 55%, transparent); padding: 0.6rem 0.85rem; vertical-align: middle; }
.rn-row:last-child .rn-td { border-bottom: none; }
.rn-row:hover { background: color-mix(in srgb, var(--app-brand-soft) 20%, transparent); }
.rn-checkbox { accent-color: var(--app-brand); cursor: pointer; height: 0.9rem; width: 0.9rem; }
.rn-avatar { align-items: center; background: color-mix(in srgb, var(--app-brand-soft) 80%, transparent); border-radius: 999px; color: var(--app-brand); display: inline-flex; flex: none; font-size: 0.7rem; font-weight: 700; height: 2rem; justify-content: center; letter-spacing: 0.04em; width: 2rem; }
.rn-code { color: var(--app-text-muted); font-family: monospace; font-size: 0.75rem; }
.rn-today-badge { background: rgba(249,115,22,0.12); border: 1px solid rgba(249,115,22,0.3); border-radius: 999px; color: #f97316; font-size: 0.7rem; font-weight: 700; padding: 0.1rem 0.45rem; }

/* Row actions */
.rn-btn-renew { align-items: center; background: color-mix(in srgb, var(--app-brand-soft) 70%, transparent); border: 1px solid color-mix(in srgb, var(--app-brand) 30%, var(--app-border)); border-radius: 0.55rem; color: var(--app-brand); cursor: pointer; font-size: 0.75rem; font-weight: 600; padding: 0.3rem 0.65rem; transition: background 130ms; white-space: nowrap; }
.rn-btn-renew:hover { background: color-mix(in srgb, var(--app-brand-soft) 100%, transparent); }
.rn-actions-wrap { display: inline-flex; position: relative; }
.rn-action-btn { align-items: center; background: transparent; border: 1px solid transparent; border-radius: 0.5rem; color: var(--app-text-muted); cursor: pointer; display: inline-flex; padding: 0.25rem 0.4rem; transition: background 120ms; }
.rn-action-btn:hover { background: var(--app-panel-strong); border-color: var(--app-border); }
.rn-actions-menu { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.75rem; box-shadow: 0 8px 32px rgba(0,0,0,.24); display: none; min-width: 148px; padding: 0.3rem; position: fixed; z-index: 200; }
.rn-actions-wrap.open .rn-actions-menu { display: block; }
.rn-action-item { border-radius: 0.45rem; color: var(--app-text); cursor: pointer; display: block; font-size: 0.8rem; padding: 0.42rem 0.6rem; text-decoration: none; transition: background 120ms; }
.rn-action-item:hover { background: color-mix(in srgb, var(--app-border) 65%, transparent); }

/* Empty */
.rn-empty-icon { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; color: var(--app-text-muted); display: inline-flex; height: 4.5rem; justify-content: center; width: 4.5rem; }
.rn-empty-icon svg { height: 2rem; width: 2rem; }

/* Drawer */
.rn-overlay { background: rgba(0,0,0,.48); backdrop-filter: blur(2px); display: none; inset: 0; position: fixed; z-index: 40; }
.rn-overlay.open { display: block; }
.rn-drawer { background: var(--app-panel-strong); border-left: 1px solid var(--app-border); bottom: 0; display: flex; flex-direction: column; overflow: hidden; position: fixed; right: 0; top: 0; transform: translateX(100%); transition: transform 280ms cubic-bezier(.4,0,.2,1); width: 480px; max-width: 100vw; z-index: 50; }
.rn-drawer.open { transform: translateX(0); }
.rn-drawer-header { align-items: center; border-bottom: 1px solid var(--app-border); display: flex; flex: none; justify-content: space-between; padding: 1rem 1.25rem; }
.rn-drawer-body { flex: 1; overflow-y: auto; padding: 1.25rem; }
.rn-drawer-footer { border-top: 1px solid var(--app-border); display: flex; flex: none; gap: 0.75rem; justify-content: flex-end; padding: 1rem 1.25rem; }
.rn-drawer-close { align-items: center; background: transparent; border: 1px solid var(--app-border); border-radius: 0.5rem; color: var(--app-text-muted); cursor: pointer; display: inline-flex; height: 2rem; justify-content: center; transition: background 120ms; width: 2rem; }
.rn-drawer-close:hover { background: color-mix(in srgb, var(--app-border) 65%, transparent); }
.rn-drawer-close svg { height: 1rem; width: 1rem; }

/* Drawer form */
.rn-section { color: var(--app-text-muted); font-size: 0.67rem; font-weight: 700; letter-spacing: 0.15em; margin-bottom: 0.7rem; text-transform: uppercase; }
.rn-field { display: flex; flex-direction: column; gap: 0.28rem; margin-bottom: 0.85rem; }
.rn-form-row { display: grid; gap: 0.8rem; grid-template-columns: 1fr 1fr; margin-bottom: 0.85rem; }
.rn-form-row .rn-field { margin-bottom: 0; }
.rn-label { color: var(--app-text-muted); font-size: 0.78rem; font-weight: 500; }
.rn-req { color: #E24B4A; }
.rn-hint { color: var(--app-text-muted); font-size: 0.68rem; }
.rn-input { background: var(--app-panel); border: 1px solid var(--app-border); border-radius: 0.65rem; color: var(--app-text); font-size: 0.875rem; outline: none; padding: 0.5rem 0.7rem; transition: border-color 150ms; width: 100%; }
.rn-input:focus { border-color: color-mix(in srgb, var(--app-brand) 55%, var(--app-border)); }
.rn-textarea { min-height: 4rem; resize: vertical; }
.rn-preview { background: color-mix(in srgb, var(--app-brand-soft) 35%, transparent); border: 1px dashed color-mix(in srgb, var(--app-brand) 30%, var(--app-border)); border-radius: 0.6rem; color: var(--app-text-muted); font-size: 0.85rem; padding: 0.45rem 0.7rem; }
.rn-prefix-wrap { position: relative; }
.rn-prefix { align-items: center; bottom: 0; color: var(--app-text-muted); display: flex; font-size: 0.85rem; left: 0.7rem; pointer-events: none; position: absolute; top: 0; }
.rn-with-prefix { padding-left: 1.8rem; }
.rn-btn-primary { background: var(--app-brand); border: none; border-radius: 0.75rem; color: #0f172a; cursor: pointer; font-size: 0.875rem; font-weight: 600; padding: 0.55rem 1.25rem; transition: opacity 160ms; }
.rn-btn-primary:hover { opacity: 0.88; }
</style>
@endpush

{{-- ── JS ─────────────────────────────────────────────────────────────────── --}}
<script>
(function () {
    const overlay = document.getElementById('rn-overlay');
    const drawer  = document.getElementById('rn-drawer');
    const form    = document.getElementById('rn-form');

    function openDrawer() {
        overlay.classList.add('open');
        drawer.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        overlay.classList.remove('open');
        drawer.classList.remove('open');
        document.body.style.overflow = '';
    }

    document.getElementById('rn-close').addEventListener('click', closeDrawer);
    document.getElementById('rn-cancel').addEventListener('click', closeDrawer);
    overlay.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrawer(); });

    window.openRenewDrawer = function (btn) {
        const memberId   = btn.dataset.id;
        const memberName = btn.dataset.name;
        const planId     = btn.dataset.plan;
        const startDate  = btn.dataset.start;

        form.action = `/renewals/${memberId}/renew`;
        document.getElementById('rn-member-name').textContent = memberName;
        document.getElementById('rn-start').value = startDate;

        // Pre-select plan
        const planSel = document.getElementById('rn-plan');
        planSel.value = planId || '';
        updatePlanPrice();
        updateExpiryPreview();

        openDrawer();
    };

    function updatePlanPrice() {
        const sel  = document.getElementById('rn-plan');
        const opt  = sel.options[sel.selectedIndex];
        const paise = parseInt(opt?.dataset.price || '0');
        const hint  = document.getElementById('rn-plan-price');
        const amtEl = document.getElementById('rn-amount');
        if (paise > 0) {
            const rs = (paise / 100).toFixed(0);
            hint.textContent = 'Plan price: ₹' + Number(rs).toLocaleString('en-IN');
            amtEl.value = (paise / 100).toFixed(2);
        } else {
            hint.textContent = '';
            amtEl.value = '';
        }
    }

    function updateExpiryPreview() {
        const sel       = document.getElementById('rn-plan');
        const opt       = sel.options[sel.selectedIndex];
        const durType   = opt?.dataset.durationType;
        const durVal    = parseInt(opt?.dataset.durationValue || '0');
        const durDays   = parseInt(opt?.dataset.durationDays || '0');
        const startVal  = document.getElementById('rn-start').value;
        const preview   = document.getElementById('rn-expiry-preview');

        if (!durDays || !startVal) { preview.textContent = '—'; return; }

        const d = new Date(startVal + 'T00:00:00');
        if (durType === 'months') d.setMonth(d.getMonth() + durVal);
        else d.setDate(d.getDate() + durDays);

        preview.textContent = d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    document.getElementById('rn-plan').addEventListener('change', () => { updatePlanPrice(); updateExpiryPreview(); });
    document.getElementById('rn-start').addEventListener('change', updateExpiryPreview);

    // Select-all checkbox
    document.getElementById('select-all')?.addEventListener('change', function () {
        document.querySelectorAll('.row-cb').forEach(cb => cb.checked = this.checked);
    });

    // Row 3-dot menus — position:fixed to escape overflow clipping
    document.querySelectorAll('.rn-actions-wrap').forEach(wrap => {
        const btn  = wrap.querySelector('.rn-action-btn');
        const menu = wrap.querySelector('.rn-actions-menu');
        btn.addEventListener('click', e => {
            e.stopPropagation();
            const isOpen = wrap.classList.contains('open');
            document.querySelectorAll('.rn-actions-wrap.open').forEach(w => w.classList.remove('open'));
            if (!isOpen) {
                const rect = btn.getBoundingClientRect();
                menu.style.top   = (rect.bottom + 4) + 'px';
                menu.style.right = (window.innerWidth - rect.right) + 'px';
                wrap.classList.add('open');
            }
        });
    });
    document.addEventListener('click', () => {
        document.querySelectorAll('.rn-actions-wrap.open').forEach(w => w.classList.remove('open'));
    });
})();
</script>

</x-layouts.admin>
