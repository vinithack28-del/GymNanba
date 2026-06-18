@php
    $editing    = isset($plan);
    $formAction = $editing ? route('tenant.plans.update', $plan) : route('tenant.plans.store');
    $pageTitle  = $editing ? 'Edit plan' : 'Create plan';
    $pageSub    = $editing ? "Update details for {$plan->name}." : 'Define a new membership plan for your members.';

    $selectedBranchIds = old('branch_ids', $editing ? $plan->branches->pluck('id')->toArray() : []);
@endphp

<x-layouts.admin
    title="{{ $pageTitle }}"
    eyebrow="Memberships / Plans"
    heading="{{ $pageTitle }}"
    subheading="{{ $pageSub }}"
>
@slot('headerAction')
    <a href="{{ route('tenant.plans.index') }}" class="plf-back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        Back to plans
    </a>
@endslot

<form method="POST" action="{{ $formAction }}" id="plan-form">
    @csrf
    @if ($editing) @method('PUT') @endif

    @if ($errors->any())
        <div class="plf-error-box mb-6">
            @foreach ($errors->all() as $e)
                <p class="text-sm">{{ $e }}</p>
            @endforeach
        </div>
    @endif

    <div class="plf-layout">

        {{-- ── LEFT ───────────────────────────────────────────────────────── --}}
        <div class="plf-main">

            {{-- Plan details --}}
            <div class="plf-card">
                <h3 class="plf-card-title">Plan details</h3>

                <div class="plf-field">
                    <label class="plf-label" for="plf-name">Plan name <span class="plf-req">*</span></label>
                    <input id="plf-name" type="text" name="name"
                        value="{{ old('name', $plan->name ?? '') }}"
                        placeholder="e.g. Monthly Premium"
                        class="plf-input" required maxlength="80">
                </div>

                <div class="plf-field">
                    <label class="plf-label" for="plf-desc">Description</label>
                    <textarea id="plf-desc" name="description" rows="3" maxlength="500"
                        placeholder="Short description shown to members…"
                        class="plf-input plf-textarea">{{ old('description', $plan->description ?? '') }}</textarea>
                </div>
            </div>

            {{-- Duration --}}
            <div class="plf-card">
                <h3 class="plf-card-title">Duration</h3>

                <div class="plf-row">
                    <div class="plf-field">
                        <label class="plf-label" for="plf-dur-val">Value <span class="plf-req">*</span></label>
                        <input id="plf-dur-val" type="number" name="duration_value"
                            value="{{ old('duration_value', $plan->duration_value ?? 1) }}"
                            min="1" max="730" placeholder="e.g. 30"
                            class="plf-input" required>
                    </div>
                    <div class="plf-field">
                        <label class="plf-label" for="plf-dur-type">Type <span class="plf-req">*</span></label>
                        <select id="plf-dur-type" name="duration_type" class="plf-input" required>
                            <option value="days"   {{ old('duration_type', $plan->duration_type ?? 'days') === 'days'   ? 'selected' : '' }}>Days</option>
                            <option value="months" {{ old('duration_type', $plan->duration_type ?? '') === 'months' ? 'selected' : '' }}>Months</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="plf-card">
                <h3 class="plf-card-title">Pricing</h3>

                <div class="plf-row">
                    <div class="plf-field">
                        <label class="plf-label" for="plf-price">Price (Rs.) <span class="plf-req">*</span></label>
                        <div class="plf-prefix-wrap">
                            <span class="plf-prefix">Rs.</span>
                            <input id="plf-price" type="number" name="price_paise"
                                value="{{ old('price_paise') !== null ? old('price_paise') / 100 : ($editing ? $plan->price_paise / 100 : '') }}"
                                min="0" max="999999" step="0.01" placeholder="0.00"
                                class="plf-input plf-with-prefix" required>
                        </div>
                    </div>
                    <div class="plf-field">
                        <label class="plf-label">GST applicable</label>
                        <label class="plf-toggle">
                            <input type="checkbox" name="gst_applicable" id="plf-gst-toggle" value="1"
                                class="plf-toggle-input"
                                {{ old('gst_applicable', $editing ? $plan->gst_applicable : false) ? 'checked' : '' }}>
                            <span class="plf-toggle-track"><span class="plf-toggle-thumb"></span></span>
                            <span class="plf-toggle-label">Yes</span>
                        </label>
                    </div>
                </div>

                <div id="gst-rate-row" class="plf-field {{ old('gst_applicable', $editing ? $plan->gst_applicable : false) ? '' : 'plf-hidden' }}">
                    <label class="plf-label" for="plf-gst-rate">GST rate <span class="plf-req">*</span></label>
                    <select id="plf-gst-rate" name="gst_rate" class="plf-input">
                        @foreach ([0, 5, 12, 18, 28] as $rate)
                            <option value="{{ $rate }}"
                                {{ old('gst_rate', $editing ? (int)$plan->gst_rate : 18) == $rate ? 'selected' : '' }}>
                                {{ $rate }}%
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="price-preview" class="plf-price-preview {{ old('gst_applicable', $editing ? $plan->gst_applicable : false) ? '' : 'plf-hidden' }}">
                    Total with GST: <strong id="price-total">—</strong>
                </div>
            </div>

            {{-- Membership rules --}}
            <div class="plf-card">
                <h3 class="plf-card-title">Membership rules</h3>

                <div class="plf-row">
                    <div class="plf-field">
                        <label class="plf-label" for="plf-max">Member cap</label>
                        <input id="plf-max" type="number" name="max_members" min="0"
                            value="{{ old('max_members', $plan->max_members ?? 0) }}"
                            placeholder="0 = unlimited" class="plf-input">
                        <span class="plf-hint">0 = unlimited</span>
                    </div>
                    <div class="plf-field">
                        <label class="plf-label" for="plf-grace">Grace period (days)</label>
                        <input id="plf-grace" type="number" name="grace_days" min="0" max="30"
                            value="{{ old('grace_days', $plan->grace_days ?? 0) }}"
                            placeholder="0" class="plf-input">
                        <span class="plf-hint">Days after expiry access is still allowed</span>
                    </div>
                </div>

                <div class="plf-row plf-items-center">
                    <div class="plf-field mb-0">
                        <label class="plf-label">Allow freeze / pause</label>
                        <label class="plf-toggle mt-1">
                            <input type="checkbox" name="allow_freeze" id="plf-freeze-toggle" value="1"
                                class="plf-toggle-input"
                                {{ old('allow_freeze', $editing ? $plan->allow_freeze : true) ? 'checked' : '' }}>
                            <span class="plf-toggle-track"><span class="plf-toggle-thumb"></span></span>
                            <span class="plf-toggle-label">Yes</span>
                        </label>
                    </div>
                    <div class="plf-field mb-0" id="freeze-days-field">
                        <label class="plf-label" for="plf-freeze-days">Max freeze days/year</label>
                        <input id="plf-freeze-days" type="number" name="max_freeze_days" min="1" max="90"
                            value="{{ old('max_freeze_days', $plan->max_freeze_days ?? 30) }}"
                            placeholder="30" class="plf-input">
                    </div>
                </div>
            </div>

            {{-- Inclusions --}}
            <div class="plf-card">
                <h3 class="plf-card-title">Inclusions</h3>

                <div class="plf-field">
                    <label class="plf-label" for="plf-inclusions">Features / perks</label>
                    <input id="plf-inclusions" type="text" name="inclusions"
                        value="{{ old('inclusions', $editing ? implode(', ', $plan->inclusions ?? []) : '') }}"
                        placeholder="Pool access, Steam room, Personal trainer…"
                        class="plf-input">
                    <span class="plf-hint">Comma-separated list of what's included</span>
                </div>
            </div>

            {{-- Branches --}}
            @if ($branches->count() > 0)
                <div class="plf-card">
                    <h3 class="plf-card-title">Valid at branches</h3>
                    <div class="plf-branch-grid">
                        <label class="plf-branch-check" id="branch-all-wrap">
                            <input type="checkbox" id="branch-all" class="plf-checkbox">
                            <span>All branches</span>
                        </label>
                        @foreach ($branches as $br)
                            <label class="plf-branch-check">
                                <input type="checkbox" name="branch_ids[]" value="{{ $br->id }}"
                                    class="plf-checkbox branch-cb"
                                    {{ in_array($br->id, $selectedBranchIds) ? 'checked' : '' }}>
                                <span>{{ $br->name }}{{ $br->is_primary ? ' ★' : '' }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- ── RIGHT ──────────────────────────────────────────────────────── --}}
        <div class="plf-side">

            {{-- Status --}}
            <div class="plf-card">
                <h3 class="plf-card-title">Status</h3>
                <div class="flex flex-col gap-2">
                    @foreach (['active' => ['label' => 'Active', 'color' => '#1D9E75'], 'inactive' => ['label' => 'Inactive', 'color' => '#888780']] as $val => $cfg)
                        <label class="plf-status-option {{ old('status', $editing ? $plan->status : 'active') === $val ? 'plf-status-selected' : '' }}">
                            <input type="radio" name="status" value="{{ $val }}"
                                class="plf-radio"
                                {{ old('status', $editing ? $plan->status : 'active') === $val ? 'checked' : '' }}>
                            <span style="color:{{ $cfg['color'] }};font-weight:600">● {{ $cfg['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Plan info (edit only) --}}
            @if ($editing)
                <div class="plf-card">
                    <h3 class="plf-card-title">Plan info</h3>
                    <div class="plf-info-row">
                        <span class="plf-info-label">Active members</span>
                        <span class="plf-info-val" style="color:#1D9E75;font-weight:700">{{ $plan->active_member_count }}</span>
                    </div>
                    <div class="plf-info-row">
                        <span class="plf-info-label">Total enrolled</span>
                        <span class="plf-info-val">{{ $plan->members()->count() }}</span>
                    </div>
                    <div class="plf-info-row">
                        <span class="plf-info-label">Current status</span>
                        <span class="plf-info-val">{{ ucfirst($plan->status) }}</span>
                    </div>
                </div>
            @endif

            {{-- Submit --}}
            <div class="plf-actions">
                <a href="{{ route('tenant.plans.index') }}" class="plf-btn-ghost">Cancel</a>
                <button type="submit" class="plf-btn-primary">
                    {{ $editing ? 'Save changes' : 'Create plan' }}
                </button>
            </div>
        </div>

    </div>
</form>

@push('styles')
<style>
.plf-back-btn { align-items: center; background: transparent; border: 1px solid var(--app-border); border-radius: 0.65rem; color: var(--app-text-muted); display: inline-flex; font-size: 0.8rem; font-weight: 500; gap: 0.4rem; padding: 0.35rem 0.75rem; text-decoration: none; transition: background 140ms, color 140ms; }
.plf-back-btn:hover { background: color-mix(in srgb, var(--app-border) 60%, transparent); color: var(--app-text); }

.plf-error-box { background: rgba(226,75,74,0.1); border: 1px solid rgba(226,75,74,0.3); border-radius: 0.85rem; color: #E24B4A; padding: 0.9rem 1rem; }

.plf-layout { display: grid; gap: 1.25rem; grid-template-columns: 1fr; }
@media (min-width: 900px) { .plf-layout { grid-template-columns: 1fr 300px; } }

.plf-main { display: flex; flex-direction: column; gap: 1.25rem; }
.plf-side { display: flex; flex-direction: column; gap: 1.25rem; }
@media (min-width: 900px) { .plf-side { position: sticky; top: 1.5rem; align-self: start; } }

.plf-card { background: var(--app-panel); border: 1px solid var(--app-border); border-radius: 1.5rem; padding: 1.25rem; }
.plf-card-title { color: var(--app-text); font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem; }

.plf-field { display: flex; flex-direction: column; gap: 0.3rem; margin-bottom: 0.85rem; }
.plf-field.mb-0 { margin-bottom: 0; }
.plf-field:last-child { margin-bottom: 0; }
.plf-row { display: grid; gap: 0.85rem; grid-template-columns: 1fr 1fr; margin-bottom: 0.85rem; }
.plf-row .plf-field { margin-bottom: 0; }
.plf-items-center { align-items: center; }
.plf-label { color: var(--app-text-muted); font-size: 0.78rem; font-weight: 500; }
.plf-req { color: #E24B4A; }
.plf-hint { color: var(--app-text-muted); font-size: 0.68rem; }
.plf-input { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.65rem; color: var(--app-text); font-size: 0.875rem; outline: none; padding: 0.5rem 0.75rem; transition: border-color 160ms; width: 100%; }
.plf-input:focus { border-color: color-mix(in srgb, var(--app-brand) 60%, var(--app-border)); }
.plf-textarea { min-height: 4.5rem; resize: vertical; }
.plf-hidden { display: none !important; }

.plf-prefix-wrap { position: relative; }
.plf-prefix { align-items: center; bottom: 0; color: var(--app-text-muted); display: flex; font-size: 0.82rem; left: 0.75rem; pointer-events: none; position: absolute; top: 0; }
.plf-with-prefix { padding-left: 2.8rem; }

.plf-price-preview { background: color-mix(in srgb, var(--app-brand-soft) 40%, transparent); border: 1px solid color-mix(in srgb, var(--app-brand) 25%, var(--app-border)); border-radius: 0.65rem; color: var(--app-text-muted); font-size: 0.8rem; margin-top: 0.5rem; padding: 0.45rem 0.75rem; }
.plf-price-preview strong { color: var(--app-brand); }

.plf-toggle { align-items: center; cursor: pointer; display: inline-flex; gap: 0.5rem; }
.plf-toggle-input { display: none; }
.plf-toggle-track { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; display: inline-flex; height: 1.25rem; padding: 0.15rem; transition: background 200ms; width: 2.25rem; }
.plf-toggle-input:checked ~ .plf-toggle-track { background: var(--app-brand); border-color: var(--app-brand); }
.plf-toggle-thumb { background: var(--app-text-muted); border-radius: 999px; height: 0.85rem; transition: transform 200ms; width: 0.85rem; }
.plf-toggle-input:checked ~ .plf-toggle-track .plf-toggle-thumb { background: #0f172a; transform: translateX(1rem); }
.plf-toggle-label { color: var(--app-text-muted); font-size: 0.82rem; }

.plf-checkbox { accent-color: var(--app-brand); cursor: pointer; }
.plf-radio { accent-color: var(--app-brand); cursor: pointer; }

.plf-branch-grid { display: grid; gap: 0.5rem; grid-template-columns: 1fr 1fr; }
.plf-branch-check { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.6rem; cursor: pointer; display: flex; font-size: 0.8rem; gap: 0.5rem; padding: 0.45rem 0.65rem; transition: border-color 130ms; }
.plf-branch-check:has(input:checked) { border-color: color-mix(in srgb, var(--app-brand) 50%, var(--app-border)); background: color-mix(in srgb, var(--app-brand-soft) 55%, transparent); }

.plf-status-option { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.65rem; cursor: pointer; display: flex; font-size: 0.875rem; gap: 0.6rem; padding: 0.55rem 0.75rem; transition: border-color 130ms; }
.plf-status-selected { border-color: color-mix(in srgb, var(--app-brand) 45%, var(--app-border)); }

.plf-info-row { display: flex; justify-content: space-between; padding: 0.4rem 0; border-bottom: 1px solid color-mix(in srgb, var(--app-border) 55%, transparent); }
.plf-info-row:last-child { border-bottom: none; }
.plf-info-label { color: var(--app-text-muted); font-size: 0.8rem; }
.plf-info-val { font-size: 0.85rem; font-weight: 500; }

.plf-actions { display: flex; gap: 0.75rem; justify-content: flex-end; }
.plf-btn-primary { align-items: center; background: var(--app-brand); border: none; border-radius: 0.75rem; color: #0f172a; cursor: pointer; display: inline-flex; font-size: 0.875rem; font-weight: 600; padding: 0.55rem 1.25rem; transition: opacity 160ms; text-decoration: none; }
.plf-btn-primary:hover { opacity: 0.88; }
.plf-btn-ghost { align-items: center; background: transparent; border: 1px solid var(--app-border); border-radius: 0.75rem; color: var(--app-text-muted); display: inline-flex; font-size: 0.875rem; font-weight: 500; padding: 0.55rem 1.25rem; text-decoration: none; transition: background 140ms, color 140ms; }
.plf-btn-ghost:hover { background: color-mix(in srgb, var(--app-border) 60%, transparent); color: var(--app-text); }
</style>
@endpush

<script>
(function () {
    // Duration type → update max
    document.getElementById('plf-dur-type')?.addEventListener('change', function () {
        const max = this.value === 'months' ? 24 : 730;
        document.getElementById('plf-dur-val').setAttribute('max', max);
    });

    // GST toggle
    function updateGstUI() {
        const on = document.getElementById('plf-gst-toggle')?.checked;
        document.getElementById('gst-rate-row')?.classList.toggle('plf-hidden', !on);
        document.getElementById('price-preview')?.classList.toggle('plf-hidden', !on);
        if (on) updatePricePreview();
    }

    function updatePricePreview() {
        const base  = parseFloat(document.getElementById('plf-price')?.value) || 0;
        const rate  = parseFloat(document.getElementById('plf-gst-rate')?.value) || 0;
        const total = base * (1 + rate / 100);
        const el    = document.getElementById('price-total');
        if (el) el.textContent = 'Rs. ' + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    document.getElementById('plf-gst-toggle')?.addEventListener('change', updateGstUI);
    document.getElementById('plf-price')?.addEventListener('input', updatePricePreview);
    document.getElementById('plf-gst-rate')?.addEventListener('change', updatePricePreview);

    // Freeze toggle
    function updateFreezeUI() {
        const on = document.getElementById('plf-freeze-toggle')?.checked;
        document.getElementById('freeze-days-field')?.classList.toggle('plf-hidden', !on);
    }
    document.getElementById('plf-freeze-toggle')?.addEventListener('change', updateFreezeUI);

    // Branch "All" toggle
    const branchAllCb = document.getElementById('branch-all');
    const branchCbs   = () => document.querySelectorAll('.branch-cb');

    function updateBranchAllState() {
        if (!branchAllCb) return;
        const all     = branchCbs();
        const checked = [...all].filter(c => c.checked).length;
        branchAllCb.checked       = checked === all.length;
        branchAllCb.indeterminate = checked > 0 && checked < all.length;
    }

    branchAllCb?.addEventListener('change', function () {
        branchCbs().forEach(cb => cb.checked = this.checked);
    });
    branchCbs().forEach(cb => cb.addEventListener('change', updateBranchAllState));
    updateBranchAllState();

    // Status radio → highlight card
    document.querySelectorAll('[name="status"]').forEach(r => {
        r.addEventListener('change', function () {
            document.querySelectorAll('.plf-status-option').forEach(el => el.classList.remove('plf-status-selected'));
            this.closest('.plf-status-option')?.classList.add('plf-status-selected');
        });
    });

    // price_paise: convert Rs → paise on submit
    document.getElementById('plan-form')?.addEventListener('submit', function () {
        const priceEl = document.getElementById('plf-price');
        if (priceEl) priceEl.value = Math.round(parseFloat(priceEl.value || 0) * 100);
    });

    // Init
    updateGstUI();
    updateFreezeUI();
})();
</script>

</x-layouts.admin>
