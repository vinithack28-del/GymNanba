<x-layouts.admin
    title="{{ __('branches.title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('branches.title') }}"
>

@php
    $atLimit = $planLimit > 0 && $activeCount >= $planLimit;
    $amenityIcons = [
        'pool'      => '🏊',
        'steam'     => '💨',
        'parking'   => '🅿',
        'locker'    => '🔒',
        'cafeteria' => '☕',
        'ac'        => '❄',
        'wifi'      => '📶',
    ];
    $days = ['mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun'];
@endphp

{{-- ── Limit Banner ─────────────────────────────────────────────────────── --}}
@if ($atLimit)
    <div class="branch-limit-banner mb-5">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 flex-none"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <p class="text-sm">
            {{ __('branches.limit_banner', ['limit' => $planLimit, 'plan' => $planName]) }}
        </p>
    </div>
@endif

{{-- ── Toolbar ───────────────────────────────────────────────────────────── --}}
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
        <span class="branch-count-pill">
            @if ($planLimit > 0)
                {{ __('branches.toolbar.of_limit', ['active' => $activeCount, 'limit' => $planLimit]) }}
            @else
                {{ __('branches.toolbar.unlimited', ['active' => $activeCount]) }}
            @endif
        </span>
        @if ($branches->where('status', 'inactive')->count())
            <span class="branch-count-pill-muted">{{ __('branches.toolbar.inactive', ['count' => $branches->where('status', 'inactive')->count()]) }}</span>
        @endif
    </div>
    @if ($atLimit)
        <span class="branch-btn-primary opacity-50 cursor-not-allowed" title="{{ __('branches.toolbar.limit_tip') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            {{ __('branches.add_branch') }}
        </span>
    @else
        <a href="{{ route('tenant.branches.create') }}" class="branch-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            {{ __('branches.add_branch') }}
        </a>
    @endif
</div>

@if ($errors->has('limit') || $errors->has('deactivate'))
    <div class="branch-flash mb-5 error">{{ $errors->first('limit') ?: $errors->first('deactivate') }}</div>
@endif

{{-- ── Branch admin credentials banner (shown once after creation) ──────── --}}
@if (session('branch_credentials'))
    @php $cred = session('branch_credentials'); @endphp
    <div class="branch-cred-banner mb-5" id="cred-banner">
        <div class="branch-cred-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold mb-1">{{ __('branches.credentials.title') }}</p>
            <p class="text-xs app-muted mb-2">{{ __('branches.credentials.description') }}</p>
            <div class="branch-cred-fields">
                <div class="branch-cred-field">
                    <span class="branch-cred-key">{{ __('branches.credentials.email') }}</span>
                    <code class="branch-cred-val" id="cred-email">{{ $cred['email'] }}</code>
                    <button type="button" onclick="copyText('cred-email', this)" class="branch-cred-copy" data-copy="{{ __('branches.credentials.copy') }}" data-copied="{{ __('branches.credentials.copied') }}">{{ __('branches.credentials.copy') }}</button>
                </div>
                <div class="branch-cred-field">
                    <span class="branch-cred-key">{{ __('branches.credentials.password') }}</span>
                    <code class="branch-cred-val" id="cred-pass">{{ $cred['password'] }}</code>
                    <button type="button" onclick="copyText('cred-pass', this)" class="branch-cred-copy" data-copy="{{ __('branches.credentials.copy') }}" data-copied="{{ __('branches.credentials.copied') }}">{{ __('branches.credentials.copy') }}</button>
                </div>
            </div>
        </div>
        <button type="button" onclick="document.getElementById('cred-banner').remove()" class="branch-cred-dismiss" aria-label="Dismiss">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
@endif

{{-- ── Branch Cards Grid ─────────────────────────────────────────────────── --}}
@if ($branches->isEmpty())
    <div class="app-panel flex flex-col items-center gap-4 rounded-[2rem] border py-20 text-center">
        <div class="branch-empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M15 9h.01"/><path d="M15 13h.01"/></svg>
        </div>
        <p class="text-base font-semibold">{{ __('branches.empty.no_branches') }}</p>
        <p class="app-muted text-sm">{{ __('branches.empty.get_started') }}</p>
        <a href="{{ route('tenant.branches.create') }}" class="branch-btn-primary mt-2">{{ __('branches.add_branch') }}</a>
    </div>
@else
    <div class="branch-grid">
        @foreach ($branches as $branch)
            @php
                $isActive = $branch->status === 'active';
                $memberCount = $branch->members_count ?? 0;
                $activeMemberCount = $branch->active_members_count;
            @endphp
            <div class="branch-card {{ !$isActive ? 'branch-card-inactive' : '' }}">

                {{-- Card header --}}
                <div class="branch-card-header">
                    <div class="branch-card-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h.01"/><path d="M9 13h.01"/><path d="M15 9h.01"/><path d="M15 13h.01"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="branch-name {{ !$isActive ? 'opacity-60' : '' }}">
                                {{ $branch->name }}
                                @if ($branch->is_primary)
                                    <span class="branch-primary-badge">{{ __('branches.card.primary') }}</span>
                                @endif
                            </h3>
                            <span class="branch-status-badge" style="{{ $isActive ? 'background:rgba(29,158,117,0.15);color:#1D9E75' : 'background:rgba(136,135,128,0.15);color:#888780' }}">
                                <span class="branch-status-dot" style="background:{{ $isActive ? '#1D9E75' : '#888780' }};"></span>
                                {{ $isActive ? __('branches.card.active') : __('branches.card.inactive') }}
                            </span>
                        </div>
                        <p class="branch-address">{{ $branch->address1 }}{{ $branch->address2 ? ', '.$branch->address2 : '' }}</p>
                        <p class="branch-city">{{ $branch->city }}, {{ $branch->state }} — {{ $branch->pin }}</p>
                    </div>
                </div>

                {{-- Contact & Manager --}}
                <div class="branch-info-row">
                    <a href="tel:{{ $branch->phone }}" class="branch-info-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 flex-none"><path d="M22 16.9v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.8 19.8 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.2h3a2 2 0 0 1 2 1.72c.127.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.79a16 16 0 0 0 6.3 6.3l.96-.96a2 2 0 0 1 2.11-.45c.91.34 1.85.573 2.81.7A2 2 0 0 1 22 16.9z"/></svg>
                        {{ $branch->phone }}
                    </a>
                    <span class="branch-info-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 flex-none"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="3"/></svg>
                        {{ $branch->manager_name ?? __('branches.card.no_manager') }}
                    </span>
                </div>

                {{-- Stats --}}
                <div class="branch-stats-row">
                    <div class="branch-stat">
                        <span class="branch-stat-value">{{ number_format($memberCount) }}</span>
                        <span class="branch-stat-label">{{ __('branches.card.members') }}</span>
                    </div>
                    <div class="branch-stat">
                        <span class="branch-stat-value" style="color:#1D9E75">{{ number_format($activeMemberCount) }}</span>
                        <span class="branch-stat-label">{{ __('branches.card.active_members') }}</span>
                    </div>
                    <div class="branch-stat">
                        <span class="branch-stat-value app-muted">—</span>
                        <span class="branch-stat-label">{{ __('branches.card.checkins_today') }}</span>
                    </div>
                    <div class="branch-stat">
                        <span class="branch-stat-value app-muted">—</span>
                        <span class="branch-stat-label">{{ __('branches.card.revenue_mo') }}</span>
                    </div>
                </div>

                {{-- Amenities --}}
                @if (!empty($branch->amenities_list))
                    <div class="branch-amenities">
                        @foreach ($branch->amenities_list as $amenity)
                            <span class="branch-amenity-tag" title="{{ $amenityOpts[$amenity] ?? $amenity }}">
                                {{ $amenityIcons[$amenity] ?? '✓' }} {{ $amenityOpts[$amenity] ?? $amenity }}
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- Actions --}}
                <div class="branch-card-actions">
                    <a href="{{ route('tenant.branches.edit', $branch) }}"
                       class="branch-action-btn">{{ __('branches.card.edit') }}</a>
                    <a href="{{ route('tenant.members.index', ['branch_id' => $branch->id]) }}"
                       class="branch-action-btn">{{ __('branches.card.members_link') }}</a>
                    @if ($isActive)
                        <button
                            type="button"
                            class="branch-action-btn branch-action-danger"
                            onclick="openDeactivateDialog({{ $branch->id }}, '{{ addslashes($branch->name) }}')"
                        >{{ __('branches.card.deactivate') }}</button>
                    @else
                        <form method="POST" action="{{ route('tenant.branches.reactivate', $branch) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="branch-action-btn branch-action-success">{{ __('branches.card.reactivate') }}</button>
                        </form>
                    @endif
                </div>
            </div>

        @endforeach
    </div>
@endif

{{-- ── Deactivate Dialog ─────────────────────────────────────────────────── --}}
<div id="deactivate-overlay" class="branch-modal-overlay" aria-hidden="true">
    <div class="branch-modal" role="dialog" aria-modal="true">
        <h3 class="text-base font-semibold mb-1">{{ __('branches.deactivate_modal.title') }}</h3>
        <p id="deactivate-msg" class="app-muted text-sm mb-4"></p>

        @php $activeBranches = $branches->where('status', 'active'); @endphp
        @if ($activeBranches->count() > 1)
            <div class="mb-4">
                <label class="branch-label mb-1.5 block">{{ __('branches.deactivate_modal.reassign_to') }}</label>
                <select id="reassign-select" class="branch-input">
                    <option value="">{{ __('branches.deactivate_modal.leave_unassigned') }}</option>
                    @foreach ($activeBranches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <form id="deactivate-form" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="reassign_branch_id" id="reassign-hidden">
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeDeactivateDialog()" class="branch-btn-ghost">{{ __('branches.deactivate_modal.cancel') }}</button>
                <button type="submit" class="branch-btn-danger">{{ __('branches.deactivate_modal.confirm') }}</button>
            </div>
        </form>
    </div>
</div>


{{-- ── CSS ───────────────────────────────────────────────────────────────── --}}
@push('styles')
<style>
/* Layout */
.branch-grid { display: grid; gap: 1.25rem; grid-template-columns: repeat(1, 1fr); }
@media (min-width: 640px)  { .branch-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .branch-grid { grid-template-columns: repeat(3, 1fr); } }

/* Toolbar */
.branch-count-pill { background: color-mix(in srgb, var(--app-brand-soft) 80%, transparent); border: 1px solid color-mix(in srgb, var(--app-brand) 20%, var(--app-border)); border-radius: 999px; color: var(--app-brand); font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.75rem; }
.branch-count-pill-muted { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; color: var(--app-text-muted); font-size: 0.75rem; font-weight: 500; padding: 0.25rem 0.75rem; }

/* Limit banner */
.branch-limit-banner { align-items: center; background: rgba(249,115,22,0.08); border: 1px solid rgba(249,115,22,0.25); border-radius: 1rem; color: var(--app-brand); display: flex; gap: 0.75rem; padding: 0.75rem 1rem; }

/* Flash */
.branch-flash { border-radius: 1rem; font-size: 0.875rem; padding: 0.75rem 1rem; }
.branch-flash.success { background: rgba(29,158,117,0.1); border: 1px solid rgba(29,158,117,0.3); color: #1D9E75; }
.branch-flash.error   { background: rgba(226,75,74,0.1);  border: 1px solid rgba(226,75,74,0.3);  color: #E24B4A; }

/* Card */
.branch-card { background: var(--app-panel); border: 1px solid var(--app-border); border-radius: 1.5rem; display: flex; flex-direction: column; gap: 0; overflow: hidden; transition: box-shadow 180ms; }
.branch-card:hover { box-shadow: 0 4px 24px rgba(0,0,0,0.18); }
.branch-card-inactive { opacity: 0.72; }
.branch-card-header { display: flex; gap: 0.85rem; padding: 1.1rem 1.1rem 0.75rem; }
.branch-card-icon { align-items: center; background: color-mix(in srgb, var(--app-brand-soft) 60%, transparent); border: 1px solid color-mix(in srgb, var(--app-brand) 20%, var(--app-border)); border-radius: 0.75rem; color: var(--app-brand); display: inline-flex; flex: none; height: 2.4rem; justify-content: center; width: 2.4rem; }
.branch-card-icon svg { height: 1.1rem; width: 1.1rem; }
.branch-name { font-size: 0.95rem; font-weight: 600; line-height: 1.3; }
.branch-address { color: var(--app-text-muted); font-size: 0.78rem; margin-top: 0.15rem; }
.branch-city { color: var(--app-text-muted); font-size: 0.72rem; margin-top: 0.1rem; }
.branch-primary-badge { background: color-mix(in srgb, var(--app-brand-soft) 90%, transparent); border: 1px solid color-mix(in srgb, var(--app-brand) 25%, transparent); border-radius: 999px; color: var(--app-brand); font-size: 0.6rem; font-weight: 700; letter-spacing: 0.05em; margin-left: 0.4rem; padding: 0.1rem 0.4rem; text-transform: uppercase; vertical-align: middle; }
.branch-status-badge { align-items: center; border-radius: 999px; display: inline-flex; font-size: 0.68rem; font-weight: 600; gap: 0.3rem; padding: 0.2rem 0.5rem; white-space: nowrap; flex: none; }
.branch-status-dot { border-radius: 999px; flex: none; height: 0.35rem; width: 0.35rem; }

/* Info row */
.branch-info-row { border-top: 1px solid color-mix(in srgb, var(--app-border) 60%, transparent); display: flex; flex-wrap: wrap; gap: 0.5rem 1.25rem; padding: 0.6rem 1.1rem; }
.branch-info-item { align-items: center; color: var(--app-text-muted); display: flex; font-size: 0.78rem; gap: 0.35rem; }

/* Stats */
.branch-stats-row { border-top: 1px solid color-mix(in srgb, var(--app-border) 60%, transparent); display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
.branch-stat { border-right: 1px solid color-mix(in srgb, var(--app-border) 60%, transparent); display: flex; flex-direction: column; gap: 0.2rem; padding: 0.65rem 0.75rem; text-align: center; }
.branch-stat:last-child { border-right: none; }
.branch-stat-value { font-size: 1rem; font-weight: 700; line-height: 1; }
.branch-stat-label { color: var(--app-text-muted); font-size: 0.62rem; letter-spacing: 0.05em; text-transform: uppercase; }

/* Amenities */
.branch-amenities { border-top: 1px solid color-mix(in srgb, var(--app-border) 60%, transparent); display: flex; flex-wrap: wrap; gap: 0.3rem; padding: 0.6rem 1.1rem; }
.branch-amenity-tag { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; font-size: 0.68rem; padding: 0.2rem 0.55rem; }

/* Card actions */
.branch-card-actions { border-top: 1px solid color-mix(in srgb, var(--app-border) 60%, transparent); display: flex; gap: 0; margin-top: auto; }
.branch-action-btn { background: transparent; border: none; border-right: 1px solid color-mix(in srgb, var(--app-border) 60%, transparent); color: var(--app-text-muted); cursor: pointer; flex: 1; font-size: 0.78rem; font-weight: 500; padding: 0.6rem 0.5rem; text-align: center; text-decoration: none; transition: background 140ms, color 140ms; display: inline-flex; align-items: center; justify-content: center; }
.branch-action-btn:last-child { border-right: none; }
.branch-action-btn:hover { background: color-mix(in srgb, var(--app-border) 50%, transparent); color: var(--app-text); }
.branch-action-danger:hover { background: rgba(226,75,74,0.08); color: #E24B4A; }
.branch-action-success:hover { background: rgba(29,158,117,0.08); color: #1D9E75; }

/* Empty */
.branch-empty-icon { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; color: var(--app-text-muted); display: inline-flex; height: 4.5rem; justify-content: center; width: 4.5rem; }
.branch-empty-icon svg { height: 2rem; width: 2rem; }

/* Buttons */
.branch-btn-primary { align-items: center; background: var(--app-brand); border-radius: 0.75rem; color: #0f172a; display: inline-flex; font-size: 0.82rem; font-weight: 600; gap: 0.35rem; padding: 0.45rem 0.9rem; transition: opacity 160ms; white-space: nowrap; border: none; cursor: pointer; }
.branch-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.branch-btn-ghost { align-items: center; background: transparent; border: 1px solid var(--app-border); border-radius: 0.75rem; color: var(--app-text-muted); cursor: pointer; display: inline-flex; font-size: 0.82rem; font-weight: 500; gap: 0.35rem; padding: 0.45rem 0.9rem; transition: background 160ms; }
.branch-btn-ghost:hover { background: color-mix(in srgb, var(--app-border) 50%, transparent); }
.branch-btn-danger { align-items: center; background: rgba(226,75,74,0.15); border: 1px solid rgba(226,75,74,0.3); border-radius: 0.75rem; color: #E24B4A; cursor: pointer; display: inline-flex; font-size: 0.82rem; font-weight: 600; padding: 0.45rem 0.9rem; transition: background 160ms; }
.branch-btn-danger:hover { background: rgba(226,75,74,0.25); }

/* Deactivate modal */
.branch-modal-overlay { align-items: center; background: rgba(0,0,0,0.55); backdrop-filter: blur(2px); display: none; inset: 0; justify-content: center; position: fixed; z-index: 60; }
.branch-modal-overlay.open { display: flex; }
.branch-modal { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 1.5rem; box-shadow: 0 16px 64px rgba(0,0,0,0.4); max-width: 420px; padding: 1.5rem; width: 100%; }

/* Credentials banner */
.branch-cred-banner { align-items: flex-start; background: rgba(55,138,221,0.08); border: 1px solid rgba(55,138,221,0.3); border-radius: 1.25rem; display: flex; gap: 1rem; padding: 1rem 1.1rem; }
.branch-cred-icon { align-items: center; background: rgba(55,138,221,0.15); border-radius: 0.65rem; color: #378ADD; display: inline-flex; flex: none; height: 2.2rem; justify-content: center; width: 2.2rem; }
.branch-cred-icon svg { height: 1rem; width: 1rem; }
.branch-cred-fields { display: flex; flex-direction: column; gap: 0.4rem; }
.branch-cred-field { align-items: center; display: flex; gap: 0.6rem; flex-wrap: wrap; }
.branch-cred-key { color: var(--app-text-muted); font-size: 0.72rem; font-weight: 600; letter-spacing: 0.06em; min-width: 3.5rem; text-transform: uppercase; }
.branch-cred-val { background: var(--app-panel); border: 1px solid var(--app-border); border-radius: 0.4rem; color: #378ADD; font-family: monospace; font-size: 0.85rem; padding: 0.15rem 0.5rem; }
.branch-cred-copy { background: rgba(55,138,221,0.12); border: 1px solid rgba(55,138,221,0.3); border-radius: 0.4rem; color: #378ADD; cursor: pointer; font-size: 0.7rem; font-weight: 600; padding: 0.1rem 0.45rem; transition: background 140ms; }
.branch-cred-copy:hover { background: rgba(55,138,221,0.25); }
.branch-cred-dismiss { align-items: center; background: transparent; border: none; color: var(--app-text-muted); cursor: pointer; display: inline-flex; flex: none; padding: 0.25rem; transition: color 140ms; }
.branch-cred-dismiss:hover { color: var(--app-text); }
.branch-cred-dismiss svg { height: 0.95rem; width: 0.95rem; }
.branch-label { color: var(--app-text-muted); font-size: 0.78rem; font-weight: 500; }
.branch-input { background: var(--app-panel); border: 1px solid var(--app-border); border-radius: 0.65rem; color: var(--app-text); font-size: 0.875rem; outline: none; padding: 0.5rem 0.7rem; width: 100%; }
</style>
@endpush

{{-- ── JS ────────────────────────────────────────────────────────────────── --}}
<script>
function copyText(elId, btn) {
    const text = document.getElementById(elId)?.textContent ?? '';
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.textContent;
        btn.textContent = btn.dataset.copied || 'Copied!';
        setTimeout(() => btn.textContent = orig, 1800);
    });
}

(function () {
    const deactivateOverlay = document.getElementById('deactivate-overlay');
    const deactivateForm    = document.getElementById('deactivate-form');
    const deactivateMsg     = document.getElementById('deactivate-msg');
    const reassignSelect    = document.getElementById('reassign-select');
    const reassignHidden    = document.getElementById('reassign-hidden');

    const deactivateMsgTemplate = @json(__('branches.deactivate_modal.message'));
    window.openDeactivateDialog = (id, name) => {
        deactivateForm.action = `/branches/${id}/deactivate`;
        deactivateMsg.textContent = deactivateMsgTemplate.replace(':name', name);
        deactivateOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    };

    window.closeDeactivateDialog = () => {
        deactivateOverlay.classList.remove('open');
        document.body.style.overflow = '';
    };

    deactivateOverlay.addEventListener('click', e => {
        if (e.target === deactivateOverlay) closeDeactivateDialog();
    });

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDeactivateDialog(); });

    deactivateForm.addEventListener('submit', () => {
        if (reassignSelect && reassignHidden) {
            reassignHidden.value = reassignSelect.value;
        }
    });
})();
</script>

</x-layouts.admin>
