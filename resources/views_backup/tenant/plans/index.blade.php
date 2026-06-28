<x-layouts.admin
    title="{{ __('gym_plans.title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('gym_plans.title') }}"
>

{{-- ── Filter tabs + Search + Create ─────────────────────────────────────── --}}
<div class="plan-toolbar mb-5">
    <div class="plan-tabs">
        @php
        $planTabLabels = ['' => 'All', 'active' => __('gym_plans.status.active'), 'inactive' => __('gym_plans.status.inactive'), 'archived' => __('gym_plans.status.archived')];
    @endphp
    @foreach ($planTabLabels as $val => $label)
            <a href="{{ route('tenant.plans.index', array_merge(request()->query(), ['status' => $val])) }}"
               class="plan-tab {{ request('status', '') === $val ? 'plan-tab-active' : '' }}">
                {{ $label }}
                <span class="plan-tab-count">{{ $counts[$val === '' ? 'all' : $val] }}</span>
            </a>
        @endforeach
    </div>
    <div class="flex items-center gap-2">
        <form method="GET" action="{{ route('tenant.plans.index') }}" class="plan-search-form">
            @if (request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="plan-search-icon"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('gym_plans.filters.search_placeholder') }}" class="plan-search-input">
        </form>
        <a href="{{ route('tenant.plans.create') }}" class="plan-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            {{ __('gym_plans.create_plan') }}
        </a>
    </div>
</div>

{{-- ── Archive error ──────────────────────────────────────────────────────── --}}
@if ($errors->has('archive'))
    <div class="plan-archive-warn mb-5" id="archive-warn">
        <div class="flex-1 text-sm">{{ $errors->first('archive') }}</div>
        <form method="POST" action="{{ route('tenant.plans.archive', $errors->first('archive_plan_id')) }}">
            @csrf
            <input type="hidden" name="confirm" value="1">
            <button type="submit" class="plan-btn-danger-sm">Archive anyway</button>
        </form>
        <button type="button" onclick="document.getElementById('archive-warn').remove()" class="plan-warn-dismiss">✕</button>
    </div>
@endif

{{-- ── Plan grid ───────────────────────────────────────────────────────────── --}}
@if ($plans->isEmpty())
    <div class="app-panel flex flex-col items-center gap-4 rounded-[2rem] border py-20 text-center">
        <div class="plan-empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 7h6"/><path d="M9 11h6"/><path d="M9 15h4"/></svg>
        </div>
        <p class="text-base font-semibold">{{ __('gym_plans.empty.no_plans') }}</p>
        <p class="app-muted text-sm">{{ __('gym_plans.empty.get_started') }}</p>
        <a href="{{ route('tenant.plans.create') }}" class="plan-btn-primary mt-2">{{ __('gym_plans.create_plan') }}</a>
    </div>
@else
    <div class="plan-grid">
        @foreach ($plans as $plan)
            @php
                $isArchived = $plan->status === 'archived';
                $isActive   = $plan->status === 'active';
                $gstTotal   = $plan->total_price_paise;
                $statusColor = match($plan->status) {
                    'active'   => ['bg' => 'rgba(29,158,117,0.12)', 'fg' => '#1D9E75'],
                    'inactive' => ['bg' => 'rgba(136,135,128,0.12)', 'fg' => '#888780'],
                    default    => ['bg' => 'rgba(226,75,74,0.10)',   'fg' => '#E24B4A'],
                };
            @endphp
            <div class="plan-card {{ $isArchived ? 'plan-card-archived' : '' }}">

                {{-- Header --}}
                <div class="plan-card-head">
                    <div class="plan-card-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 7h6"/><path d="M9 11h6"/><path d="M9 15h4"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="plan-name {{ $isArchived ? 'line-through opacity-50' : '' }}">{{ $plan->name }}</h3>
                            <span class="plan-status-badge" style="background:{{ $statusColor['bg'] }};color:{{ $statusColor['fg'] }}">
                                {{ ucfirst($plan->status) }}
                            </span>
                        </div>
                        <span class="plan-duration-badge">{{ $plan->duration_label }}</span>
                    </div>
                </div>

                {{-- Price --}}
                <div class="plan-price-row">
                    <span class="plan-price">Rs. {{ number_format($plan->price_paise / 100, 2) }}</span>
                    @if ($plan->gst_applicable && $plan->gst_rate > 0)
                        <span class="plan-gst-note">+{{ number_format($plan->gst_rate, 0) }}% GST
                            → Rs. {{ number_format($gstTotal / 100, 2) }} total</span>
                    @endif
                </div>

                {{-- Description --}}
                @if ($plan->description)
                    <p class="plan-desc">{{ Str::limit($plan->description, 80) }}</p>
                @endif

                {{-- Stats --}}
                <div class="plan-stats">
                    <div class="plan-stat">
                        <span class="plan-stat-val" style="color:#1D9E75">{{ number_format($plan->active_members_count) }}</span>
                        <span class="plan-stat-lbl">{{ __('members.stats.active') }}</span>
                    </div>
                    <div class="plan-stat">
                        <span class="plan-stat-val">{{ number_format($plan->total_members_count) }}</span>
                        <span class="plan-stat-lbl">{{ __('members.stats.total') }}</span>
                    </div>
                    @if ($plan->max_members > 0)
                        <div class="plan-stat">
                            <span class="plan-stat-val">{{ $plan->max_members }}</span>
                            <span class="plan-stat-lbl">Cap</span>
                        </div>
                    @endif
                </div>

                {{-- Inclusions --}}
                @if (!empty($plan->inclusions))
                    <div class="plan-inclusions">
                        @foreach (array_slice($plan->inclusions, 0, 4) as $inc)
                            <span class="plan-tag">{{ $inc }}</span>
                        @endforeach
                        @if (count($plan->inclusions) > 4)
                            <span class="plan-tag plan-tag-more">+{{ count($plan->inclusions) - 4 }} more</span>
                        @endif
                    </div>
                @endif

                {{-- Plan Tags --}}
                @if (!empty($plan->tags))
                    @php
                        $tagPalette = [
                            ['bg' => 'rgba(139,92,246,0.13)', 'fg' => '#A78BFA'],
                            ['bg' => 'rgba(14,165,233,0.13)',  'fg' => '#38BDF8'],
                            ['bg' => 'rgba(16,185,129,0.13)', 'fg' => '#34D399'],
                            ['bg' => 'rgba(245,158,11,0.13)', 'fg' => '#FCD34D'],
                            ['bg' => 'rgba(244,63,94,0.13)',  'fg' => '#FB7185'],
                        ];
                    @endphp
                    <div class="plan-tags-row">
                        @foreach ($plan->tags as $ti => $tag)
                            @php $c = $tagPalette[$ti % count($tagPalette)]; @endphp
                            <span class="plan-tag-pill" style="background:{{ $c['bg'] }};color:{{ $c['fg'] }}">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                {{-- Freeze badge --}}
                @if ($plan->allow_freeze)
                    <div class="plan-freeze-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3 w-3"><path d="M12 2v20M2 12h20M4.93 4.93l14.14 14.14M19.07 4.93 4.93 19.07"/></svg>
                        Freeze allowed · {{ $plan->max_freeze_days }}d/yr
                    </div>
                @else
                    <div class="plan-freeze-badge plan-freeze-badge-off">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3 w-3"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        Freeze not allowed
                    </div>
                @endif

                {{-- Actions --}}
                <div class="plan-actions">
                    @if (!$isArchived)
                        <a href="{{ route('tenant.plans.edit', $plan) }}" class="plan-action">{{ __('gym_plans.card.edit') }}</a>
                    @endif
                    <form method="POST" action="{{ route('tenant.plans.duplicate', $plan) }}" class="contents">
                        @csrf
                        <button type="submit" class="plan-action">{{ __('gym_plans.card.duplicate') }}</button>
                    </form>
                    @if (!$isArchived)
                        <form method="POST" action="{{ route('tenant.plans.archive', $plan) }}" class="contents"
                              onsubmit="return confirm('Archive this plan? Existing members are unaffected.')">
                            @csrf
                            <button type="submit" class="plan-action plan-action-warn">{{ __('gym_plans.card.archive') }}</button>
                        </form>
                    @endif
                </div>
            </div>

        @endforeach
    </div>
@endif


{{-- ── CSS ────────────────────────────────────────────────────────────────── --}}
@push('styles')
<style>
/* Toolbar */
.plan-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; justify-content: space-between; }
.plan-tabs { display: flex; gap: 0.15rem; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.9rem; padding: 0.2rem; }
.plan-tab { align-items: center; border-radius: 0.65rem; color: var(--app-text-muted); display: inline-flex; font-size: 0.8rem; font-weight: 500; gap: 0.3rem; padding: 0.3rem 0.7rem; text-decoration: none; transition: background 140ms, color 140ms; white-space: nowrap; }
.plan-tab:hover { color: var(--app-text); }
.plan-tab-active { background: var(--app-panel); box-shadow: 0 1px 4px rgba(0,0,0,.12); color: var(--app-text); font-weight: 600; }
.plan-tab-count { background: var(--app-panel-strong); border-radius: 999px; font-size: 0.65rem; min-width: 1.2rem; padding: 0.05rem 0.3rem; text-align: center; }
.plan-tab-active .plan-tab-count { background: color-mix(in srgb, var(--app-brand-soft) 80%, transparent); color: var(--app-brand); }
.plan-search-form { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.75rem; display: flex; gap: 0.4rem; padding: 0.35rem 0.75rem; }
.plan-search-icon { color: var(--app-text-muted); flex: none; height: 0.9rem; width: 0.9rem; }
.plan-search-input { background: transparent; border: none; color: var(--app-text); font-size: 0.84rem; outline: none; width: 200px; }

/* Archive warning */
.plan-archive-warn { align-items: center; background: rgba(249,115,22,0.08); border: 1px solid rgba(249,115,22,0.28); border-radius: 1rem; display: flex; gap: 0.75rem; padding: 0.75rem 1rem; }
.plan-warn-dismiss { background: transparent; border: none; color: var(--app-text-muted); cursor: pointer; font-size: 0.85rem; }
.plan-btn-danger-sm { background: rgba(226,75,74,0.12); border: 1px solid rgba(226,75,74,0.3); border-radius: 0.5rem; color: #E24B4A; cursor: pointer; font-size: 0.75rem; font-weight: 600; padding: 0.3rem 0.65rem; white-space: nowrap; }

/* Grid */
.plan-grid { display: grid; gap: 1.1rem; grid-template-columns: repeat(1, 1fr); }
@media (min-width: 640px)  { .plan-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1100px) { .plan-grid { grid-template-columns: repeat(3, 1fr); } }

/* Card */
.plan-card { background: var(--app-panel); border: 1px solid var(--app-border); border-radius: 1.5rem; display: flex; flex-direction: column; overflow: hidden; transition: box-shadow 180ms; }
.plan-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.15); }
.plan-card-archived { opacity: 0.65; }
.plan-card-head { display: flex; gap: 0.8rem; padding: 1rem 1rem 0.6rem; }
.plan-card-icon { align-items: center; background: color-mix(in srgb, var(--app-brand-soft) 55%, transparent); border: 1px solid color-mix(in srgb, var(--app-brand) 20%, var(--app-border)); border-radius: 0.65rem; color: var(--app-brand); display: inline-flex; flex: none; height: 2.2rem; justify-content: center; width: 2.2rem; }
.plan-card-icon svg { height: 1rem; width: 1rem; }
.plan-name { font-size: 0.95rem; font-weight: 600; line-height: 1.3; }
.plan-status-badge { border-radius: 999px; font-size: 0.65rem; font-weight: 700; padding: 0.15rem 0.5rem; white-space: nowrap; flex: none; }
.plan-duration-badge { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; color: var(--app-text-muted); display: inline-block; font-size: 0.68rem; font-weight: 600; margin-top: 0.3rem; padding: 0.1rem 0.45rem; }

/* Price */
.plan-price-row { align-items: baseline; display: flex; flex-wrap: wrap; gap: 0.4rem; padding: 0.5rem 1rem 0; }
.plan-price { font-size: 1.35rem; font-weight: 700; letter-spacing: -0.02em; }
.plan-gst-note { color: var(--app-text-muted); font-size: 0.72rem; }

.plan-desc { color: var(--app-text-muted); font-size: 0.78rem; line-height: 1.5; padding: 0.35rem 1rem 0; }

/* Stats */
.plan-stats { border-top: 1px solid color-mix(in srgb, var(--app-border) 55%, transparent); display: flex; gap: 0; margin-top: 0.75rem; }
.plan-stat { border-right: 1px solid color-mix(in srgb, var(--app-border) 55%, transparent); display: flex; flex-direction: column; flex: 1; gap: 0.15rem; padding: 0.55rem 0.65rem; text-align: center; }
.plan-stat:last-child { border-right: none; }
.plan-stat-val { font-size: 1rem; font-weight: 700; line-height: 1; }
.plan-stat-lbl { color: var(--app-text-muted); font-size: 0.6rem; letter-spacing: 0.05em; text-transform: uppercase; }

/* Tags */
.plan-inclusions { display: flex; flex-wrap: wrap; gap: 0.3rem; padding: 0.5rem 1rem 0; }
.plan-tag { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; font-size: 0.67rem; padding: 0.15rem 0.5rem; }
.plan-tag-more { color: var(--app-text-muted); }

/* Plan Tags */
.plan-tags-row { display: flex; flex-wrap: wrap; gap: 0.28rem; padding: 0.45rem 1rem 0; }
.plan-tag-pill { border-radius: 999px; font-size: 0.66rem; font-weight: 600; letter-spacing: 0.01em; padding: 0.18rem 0.55rem; }

/* Freeze badge */
.plan-freeze-badge { align-items: center; color: #378ADD; display: flex; font-size: 0.68rem; font-weight: 500; gap: 0.3rem; padding: 0.4rem 1rem 0; }
.plan-freeze-badge-off { color: #E24B4A; }

/* Actions */
.plan-actions { border-top: 1px solid color-mix(in srgb, var(--app-border) 55%, transparent); display: flex; margin-top: auto; }
.plan-actions > form { display: contents; }
.plan-action { background: transparent; border: none; border-right: 1px solid color-mix(in srgb, var(--app-border) 55%, transparent); color: var(--app-text-muted); cursor: pointer; flex: 1; font-size: 0.78rem; font-weight: 500; padding: 0.6rem 0.4rem; text-align: center; text-decoration: none; transition: background 130ms, color 130ms; }
.plan-action:last-child { border-right: none; }
.plan-action:hover { background: color-mix(in srgb, var(--app-border) 50%, transparent); color: var(--app-text); }
.plan-action-warn:hover { background: rgba(249,115,22,0.08); color: #f97316; }

/* Empty */
.plan-empty-icon { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; color: var(--app-text-muted); display: inline-flex; height: 4.5rem; justify-content: center; width: 4.5rem; }
.plan-empty-icon svg { height: 2rem; width: 2rem; }

/* Buttons */
.plan-btn-primary { align-items: center; background: var(--app-brand); border: none; border-radius: 0.75rem; color: #0f172a; cursor: pointer; display: inline-flex; font-size: 0.82rem; font-weight: 600; gap: 0.35rem; padding: 0.48rem 0.95rem; text-decoration: none; transition: opacity 160ms; white-space: nowrap; }
.plan-btn-primary:hover { opacity: 0.88; }
.plan-btn-ghost { align-items: center; background: transparent; border: 1px solid var(--app-border); border-radius: 0.75rem; color: var(--app-text-muted); cursor: pointer; display: inline-flex; font-size: 0.875rem; font-weight: 500; padding: 0.5rem 1rem; text-decoration: none; transition: background 140ms; }
.plan-btn-ghost:hover { background: color-mix(in srgb, var(--app-border) 60%, transparent); color: var(--app-text); }

/* Error box */
.plan-error-box { background: rgba(226,75,74,0.1); border: 1px solid rgba(226,75,74,0.3); border-radius: 0.75rem; color: #E24B4A; padding: 0.75rem; }
</style>
@endpush


</x-layouts.admin>
