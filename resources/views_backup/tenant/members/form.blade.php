@php
    $editing    = isset($member);
    $member     = $member ?? null;
    $formAction = $editing ? route('tenant.members.update', $member) : route('tenant.members.store');
    $pageTitle  = $editing ? 'Edit member' : 'Add new member';
    $pageSub    = $editing ? "Update details for {$member?->name}." : 'Register a new member at your gym.';
@endphp

<x-layouts.admin
    title="{{ $pageTitle }}"
    eyebrow="Members"
    heading="{{ $pageTitle }}"
    subheading="{{ $pageSub }}"
>
@slot('headerAction')
    <a href="{{ route('tenant.members.index') }}" class="mf-back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        Back to members
    </a>
@endslot

<form method="POST" action="{{ $formAction }}" id="member-form">
    @csrf
    @if ($editing) @method('PUT') @endif

    @if ($errors->any())
        <div class="mf-error-box mb-6">
            @foreach ($errors->all() as $e)
                <p class="text-sm">{{ $e }}</p>
            @endforeach
        </div>
    @endif

    @if (!$editing && $prefill)
    <div id="mf-walkin-banner" style="display:flex;align-items:flex-start;gap:.75rem;padding:.9rem 1.1rem;margin-bottom:1.25rem;border-radius:1rem;background:rgba(234,88,12,.08);border:1px solid rgba(234,88,12,.3)">
        <svg style="flex:none;width:1.1rem;height:1.1rem;margin-top:.15rem;color:#ea580c" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        <div style="flex:1;font-size:.88rem;color:#92400e">
            <strong style="color:#7c2d12">Walk-in inquiry found</strong> — pre-filled with details for <strong>{{ $prefill->name }}</strong> ({{ $prefill->phone }}).
            Fields can still be updated before saving.
        </div>
        <button type="button" onclick="document.getElementById('mf-walkin-banner').remove()" style="border:none;background:none;cursor:pointer;color:#b45309;font-size:1rem;line-height:1;padding:.1rem .25rem">✕</button>
    </div>
    @endif

    @if (!$editing && !$prefill)
    <div id="mf-walkin-hint" style="display:none;align-items:flex-start;gap:.75rem;padding:.75rem 1rem;margin-bottom:1.25rem;border-radius:1rem;background:rgba(234,88,12,.08);border:1px solid rgba(234,88,12,.3)">
        <svg style="flex:none;width:1rem;height:1rem;margin-top:.15rem;color:#ea580c" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        <div style="flex:1;font-size:.86rem;color:#92400e">
            <strong style="color:#7c2d12" id="mf-hint-name"></strong>
            <span id="mf-hint-msg"></span>
            <button type="button" id="mf-hint-fill-btn" style="margin-left:.5rem;border:none;background:rgba(234,88,12,.15);color:#9a3412;border-radius:.4rem;padding:.15rem .55rem;font-size:.8rem;font-weight:700;cursor:pointer">
                Pre-fill details
            </button>
        </div>
        <button type="button" onclick="document.getElementById('mf-walkin-hint').style.display='none'" style="border:none;background:none;cursor:pointer;color:#b45309;font-size:1rem;line-height:1;padding:.1rem .25rem">✕</button>
    </div>
    @endif

    <div class="mf-layout">

        {{-- ── LEFT ───────────────────────────────────────────────────────── --}}
        <div class="mf-main">

            {{-- Personal info --}}
            <div class="mf-card">
                <h3 class="mf-card-title">Personal information</h3>

                <div class="mf-field">
                    <label class="mf-label" for="mf-name">Full name <span class="mf-req">*</span></label>
                    <input id="mf-name" type="text" name="name"
                        value="{{ old('name', $member?->name ?? $prefill?->name ?? '') }}"
                        placeholder="e.g. Priya Sharma"
                        class="mf-input" required maxlength="100">
                </div>

                <div class="mf-row">
                    <div class="mf-field">
                        <label class="mf-label" for="mf-phone">Phone <span class="mf-req">*</span></label>
                        <input id="mf-phone" type="tel" name="phone"
                            value="{{ old('phone', $member?->phone ?? $prefill?->phone ?? '') }}"
                            placeholder="+91 98000 00000"
                            class="mf-input" required maxlength="20">
                    </div>
                    <div class="mf-field">
                        <label class="mf-label" for="mf-email">Email</label>
                        <input id="mf-email" type="email" name="email"
                            value="{{ old('email', $member?->email ?? '') }}"
                            placeholder="Optional"
                            class="mf-input" maxlength="255">
                    </div>
                </div>

                <div class="mf-row">
                    <div class="mf-field">
                        <label class="mf-label">Gender</label>
                        <div class="flex gap-4 pt-1">
                            @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $lbl)
                                <label class="mf-radio-label">
                                    <input type="radio" name="gender" value="{{ $val }}"
                                        class="mf-radio"
                                        {{ old('gender', $member?->gender ?? '') === $val ? 'checked' : '' }}>
                                    {{ $lbl }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mf-field">
                        <label class="mf-label" for="mf-dob">Date of birth</label>
                        <input id="mf-dob" type="date" name="dob"
                            value="{{ old('dob', $member?->dob?->toDateString() ?? '') }}"
                            max="{{ now()->subYears(5)->toDateString() }}"
                            class="mf-input">
                    </div>
                </div>

                <div class="mf-field">
                    <label class="mf-label" for="mf-address">Address</label>
                    <textarea id="mf-address" name="address" rows="2" maxlength="300"
                        placeholder="Optional" class="mf-input mf-textarea">{{ old('address', $member?->address ?? '') }}</textarea>
                </div>

                <div class="mf-row">
                    <div class="mf-field">
                        <label class="mf-label" for="mf-id-type">ID proof type</label>
                        <select id="mf-id-type" name="id_proof_type" class="mf-input">
                            <option value="">Select…</option>
                            @foreach (['aadhaar' => 'Aadhaar', 'pan' => 'PAN', 'passport' => 'Passport', 'voter_id' => 'Voter ID', 'dl' => 'Driving Licence'] as $val => $lbl)
                                <option value="{{ $val }}"
                                    {{ old('id_proof_type', $member?->id_proof_type ?? '') === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mf-field">
                        <label class="mf-label" for="mf-id-num">ID number</label>
                        <input id="mf-id-num" type="text" name="id_proof_number"
                            value="{{ old('id_proof_number', $member?->id_proof_number ?? '') }}"
                            placeholder="Optional" class="mf-input" maxlength="50">
                    </div>
                </div>
            </div>

            {{-- Membership --}}
            <div class="mf-card">
                <h3 class="mf-card-title">Membership</h3>

                <div class="mf-field">
                    <label class="mf-label" for="mf-branch">Branch <span class="mf-req">*</span></label>
                    <select id="mf-branch" name="branch_id" class="mf-input" required>
                        <option value="">— Select branch —</option>
                        @foreach ($branches as $br)
                            <option value="{{ $br->id }}"
                                {{ old('branch_id', $member?->branch_id ?? $prefill?->branch_id ?? $selectedBranchId ?? '') == $br->id ? 'selected' : '' }}>
                                {{ $br->name }}{{ $br->is_primary ? ' (Primary)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mf-row">
                    <div class="mf-field">
                        <label class="mf-label" for="mf-plan">Plan <span class="mf-req">*</span></label>
                        <select id="mf-plan" name="plan_id" class="mf-input" required>
                            <option value="">Select plan…</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}"
                                    data-duration-type="{{ $plan->duration_type }}"
                                    data-duration-value="{{ $plan->duration_value }}"
                                    data-duration-days="{{ $plan->duration_days }}"
                                    data-price="{{ $plan->total_price_paise }}"
                                    {{ old('plan_id', $member?->plan_id ?? '') == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} — ₹{{ number_format($plan->total_price_paise / 100, 2) }}
                                    @if($plan->gst_amount_paise > 0)
                                        (incl. GST)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mf-field">
                        <label class="mf-label" for="mf-start">Start date <span class="mf-req">*</span></label>
                        <input id="mf-start" type="date" name="start_date"
                            value="{{ old('start_date', $member?->start_date?->toDateString() ?? now()->toDateString()) }}"
                            @if (!$editing) max="{{ now()->addDays(30)->toDateString() }}" @endif
                            class="mf-input" required>
                    </div>
                </div>

                <div class="mf-field">
                    <p class="mf-label">Calculated expiry</p>
                    <p id="expiry-preview" class="mf-expiry-preview">
                        @if ($editing && $member?->expiry_date)
                            Current: {{ $member?->expiry_date?->format('d M Y') }}
                        @else
                            — select a plan and start date
                        @endif
                    </p>
                </div>

                @if ($editing)
                    @php
                        $editingPlan     = $member?->plan;
                        $planAllowFreeze = $editingPlan?->allow_freeze ?? true;
                        $planMaxFreeze   = $editingPlan?->max_freeze_days ?? 0;
                        $currentStatus   = old('status', $member?->status ?? 'active');
                        // Compute current freeze days remaining from frozen_until
                        $frozenUntil     = $member?->frozen_until;
                        $defaultFreezeDays = 0;
                        if ($frozenUntil && $frozenUntil->isFuture()) {
                            $defaultFreezeDays = (int) now()->diffInDays($frozenUntil);
                        } elseif ($planMaxFreeze > 0) {
                            $defaultFreezeDays = $planMaxFreeze;
                        } else {
                            $defaultFreezeDays = 30;
                        }
                    @endphp
                    <div class="mf-field">
                        <label class="mf-label">Status</label>
                        <div class="flex gap-4 pt-1">
                            @foreach (['active' => ['label' => 'Active', 'color' => '#1D9E75'], 'inactive' => ['label' => 'Inactive', 'color' => '#888780'], 'frozen' => ['label' => 'Frozen', 'color' => '#378ADD']] as $val => $cfg)
                                <label class="mf-radio-label">
                                    <input type="radio" name="status" value="{{ $val }}"
                                        class="mf-radio" id="mf-status-{{ $val }}"
                                        onchange="mfOnStatusChange()"
                                        {{ $currentStatus === $val ? 'checked' : '' }}>
                                    <span style="color:{{ $cfg['color'] }}">{{ $cfg['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Freeze days (shown only when Frozen is selected) --}}
                    <div id="mf-freeze-row" class="mf-field" style="{{ $currentStatus === 'frozen' ? '' : 'display:none' }}">
                        @if (!$planAllowFreeze)
                            <div style="padding:.65rem .85rem;background:rgba(226,75,74,.08);border:1px solid rgba(226,75,74,.25);border-radius:.75rem;font-size:.82rem;color:#991b1b">
                                This member's plan (<strong>{{ $editingPlan?->name }}</strong>) does not allow freeze. Change status to Active or Inactive.
                            </div>
                        @else
                            <label class="mf-label" for="mf-freeze-days">
                                Freeze days
                                <span style="font-weight:400;color:var(--app-text-muted)">
                                    @if($planMaxFreeze > 0)(max {{ $planMaxFreeze }} days)@endif
                                </span>
                            </label>
                            <input id="mf-freeze-days" type="number" name="freeze_days"
                                   min="1" max="{{ $planMaxFreeze > 0 ? $planMaxFreeze : 3650 }}"
                                   value="{{ old('freeze_days', $defaultFreezeDays) }}"
                                   class="mf-input" style="max-width:160px"
                                   oninput="mfValidateFreezeDays()">
                            @if ($frozenUntil && $frozenUntil->isFuture())
                                <p class="mf-help" style="margin-top:.3rem">
                                    Currently frozen until <strong>{{ $frozenUntil->format('d M Y') }}</strong>
                                    ({{ $defaultFreezeDays }} days remaining).
                                    Update the days to change the freeze end date.
                                </p>
                            @endif
                            <p id="mf-freeze-error" style="display:none;font-size:.75rem;color:#ef4444;margin-top:.2rem"></p>
                        @endif
                    </div>
                @endif
            </div>

        </div>

        {{-- ── RIGHT ──────────────────────────────────────────────────────── --}}
        <div class="mf-side">

            @if (!$editing)
                {{-- Payment (create only) --}}
                <div class="mf-card" id="mf-pay-card">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="mf-card-title" style="margin-bottom:0">Payment</h3>
                        <span id="mf-plan-total" class="text-xs font-semibold" style="color:var(--app-text-muted)"></span>
                    </div>

                    {{-- Split rows --}}
                    <div id="mf-splits" class="space-y-2 mb-3"></div>
                    <button type="button" onclick="mfAddSplit()"
                        class="text-xs font-medium px-3 py-1.5 rounded-lg border"
                        style="border-color:var(--app-border);color:var(--app-text-muted)">
                        + Add Method
                    </button>

                    {{-- Collected bar --}}
                    <div id="mf-collected-bar" class="hidden mt-3 flex items-center justify-between text-xs rounded-lg px-3 py-2"
                         style="background:var(--app-panel-strong)">
                        <span style="color:var(--app-text-muted)">Collected: <strong id="mf-collected-val" style="color:var(--app-text)">₹0</strong></span>
                        <span id="mf-shortfall-label" class="font-semibold"></span>
                    </div>

                    {{-- Balance Due --}}
                    <div id="mf-due-wrap" class="hidden mt-3 pt-3" style="border-top:1px solid var(--app-border)">
                        <label class="flex items-center gap-2 text-sm cursor-pointer mb-2" style="color:var(--app-text)">
                            <input type="checkbox" id="mf-is-partial" name="is_partial" value="1"
                                onchange="mfToggleDue()" {{ old('is_partial') ? 'checked' : '' }}>
                            Record Balance Due
                        </label>
                        <div id="mf-due-fields" class="hidden space-y-2">
                            <div class="mf-row">
                                <div class="mf-field" style="margin-bottom:0">
                                    <label class="mf-label">Due Amount (₹)</label>
                                    <input type="number" name="due_amount" id="mf-due-amount"
                                        value="{{ old('due_amount') }}"
                                        min="0" step="0.01" class="mf-input">
                                </div>
                                <div class="mf-field" style="margin-bottom:0">
                                    <label class="mf-label">Due Date</label>
                                    <input type="date" name="due_date" id="mf-due-date"
                                        value="{{ old('due_date') }}" class="mf-input">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Notes --}}
            <div class="mf-card">
                <h3 class="mf-card-title">Notes</h3>
                <div class="mf-field">
                    <label class="mf-label" for="mf-notes">Internal notes</label>
                    <textarea id="mf-notes" name="notes" rows="4" maxlength="500"
                        placeholder="Any internal notes about this member…"
                        class="mf-input mf-textarea">{{ old('notes', $member?->notes ?? '') }}</textarea>
                </div>
            </div>

            {{-- Member code (edit only) --}}
            @if ($editing)
                <div class="mf-card">
                    <h3 class="mf-card-title">Member info</h3>
                    <div class="mf-info-row">
                        <span class="mf-info-label">Member ID</span>
                        <span class="mf-info-val font-mono">{{ $member?->member_code }}</span>
                    </div>
                    <div class="mf-info-row">
                        <span class="mf-info-label">Joined</span>
                        <span class="mf-info-val">{{ $member?->created_at?->format('d M Y') }}</span>
                    </div>
                    @if ($member?->balance_paise < 0)
                        <div class="mf-info-row">
                            <span class="mf-info-label">Balance due</span>
                            <span class="mf-info-val" style="color:#E24B4A;font-weight:600">{{ $member?->balance_rupees }}</span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Submit --}}
            <div class="mf-actions">
                <a href="{{ route('tenant.members.index') }}" class="mf-btn-ghost">Cancel</a>
                <button type="submit" class="mf-btn-primary">
                    {{ $editing ? 'Save changes' : 'Add member' }}
                </button>
            </div>
        </div>

    </div>
</form>

@push('styles')
<style>
.mf-back-btn { align-items: center; background: transparent; border: 1px solid var(--app-border); border-radius: 0.65rem; color: var(--app-text-muted); display: inline-flex; font-size: 0.8rem; font-weight: 500; gap: 0.4rem; padding: 0.35rem 0.75rem; text-decoration: none; transition: background 140ms, color 140ms; }
.mf-back-btn:hover { background: color-mix(in srgb, var(--app-border) 60%, transparent); color: var(--app-text); }

.mf-error-box { background: rgba(226,75,74,0.1); border: 1px solid rgba(226,75,74,0.3); border-radius: 0.85rem; color: #E24B4A; padding: 0.9rem 1rem; }

.mf-layout { display: grid; gap: 1.25rem; grid-template-columns: 1fr; }
@media (min-width: 900px) { .mf-layout { grid-template-columns: 1fr 360px; } }

.mf-main { display: flex; flex-direction: column; gap: 1.25rem; }
.mf-side { display: flex; flex-direction: column; gap: 1.25rem; }
@media (min-width: 900px) { .mf-side { position: sticky; top: 1.5rem; } }

.mf-card { app-panel: var(--app-panel); background: var(--app-panel); border: 1px solid var(--app-border); border-radius: 1.5rem; padding: 1.25rem; }
.mf-card-title { color: var(--app-text); font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem; }

.mf-field { display: flex; flex-direction: column; gap: 0.3rem; margin-bottom: 0.85rem; }
.mf-field:last-child { margin-bottom: 0; }
.mf-row { display: grid; gap: 0.85rem; grid-template-columns: 1fr 1fr; margin-bottom: 0.85rem; }
.mf-row .mf-field { margin-bottom: 0; }
.mf-label { color: var(--app-text-muted); font-size: 0.78rem; font-weight: 500; }
.mf-req { color: #E24B4A; }
.mf-input { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.65rem; color: var(--app-text); font-size: 0.875rem; outline: none; padding: 0.5rem 0.75rem; transition: border-color 160ms; width: 100%; }
.mf-input:focus { border-color: color-mix(in srgb, var(--app-brand) 60%, var(--app-border)); }
.mf-textarea { min-height: 4.5rem; resize: vertical; }
.mf-radio-label { align-items: center; cursor: pointer; display: flex; font-size: 0.875rem; gap: 0.35rem; }
.mf-radio { accent-color: var(--app-brand); }
.mf-expiry-preview { background: color-mix(in srgb, var(--app-brand-soft) 35%, transparent); border: 1px dashed color-mix(in srgb, var(--app-brand) 30%, var(--app-border)); border-radius: 0.65rem; color: var(--app-text-muted); font-size: 0.85rem; padding: 0.5rem 0.75rem; }

.mf-prefix-wrap { position: relative; }
.mf-prefix { align-items: center; bottom: 0; color: var(--app-text-muted); display: flex; font-size: 0.85rem; left: 0.75rem; pointer-events: none; position: absolute; top: 0; }
.mf-with-prefix { padding-left: 1.85rem; }

.mf-info-row { display: flex; justify-content: space-between; padding: 0.4rem 0; border-bottom: 1px solid color-mix(in srgb, var(--app-border) 55%, transparent); }
.mf-info-row:last-child { border-bottom: none; }
.mf-info-label { color: var(--app-text-muted); font-size: 0.8rem; }
.mf-info-val { font-size: 0.85rem; font-weight: 500; }

.mf-actions { display: flex; gap: 0.75rem; justify-content: flex-end; }
.mf-split-row { display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; }
.mf-split-row .mf-input { font-size: 0.82rem; padding: 0.4rem 0.6rem; }
.mf-rm-btn { background: transparent; border: none; color: var(--app-text-muted); cursor: pointer; font-size: 0.85rem; padding: 0.25rem 0.4rem; border-radius: 0.4rem; }
.mf-rm-btn:hover { color: #ef4444; background: rgba(239,68,68,.1); }
.mf-ref-inp { font-size: 0.8rem; }
.mf-btn-primary { align-items: center; background: var(--app-brand); border: none; border-radius: 0.75rem; color: #0f172a; cursor: pointer; display: inline-flex; font-size: 0.875rem; font-weight: 600; padding: 0.55rem 1.25rem; transition: opacity 160ms; }
.mf-btn-primary:hover { opacity: 0.88; }
.mf-btn-ghost { align-items: center; background: transparent; border: 1px solid var(--app-border); border-radius: 0.75rem; color: var(--app-text-muted); display: inline-flex; font-size: 0.875rem; font-weight: 500; padding: 0.55rem 1.25rem; text-decoration: none; transition: background 140ms, color 140ms; }
.mf-btn-ghost:hover { background: color-mix(in srgb, var(--app-border) 60%, transparent); color: var(--app-text); }
</style>
@endpush

<script>
// ── Expiry preview ────────────────────────────────────────────────────────────
(function () {
    function updateExpiryPreview() {
        const planSel   = document.getElementById('mf-plan');
        const startInp  = document.getElementById('mf-start');
        const preview   = document.getElementById('expiry-preview');
        const opt       = planSel?.options[planSel.selectedIndex];
        const durType   = opt?.dataset.durationType;
        const durVal    = parseInt(opt?.dataset.durationValue || '0');
        const durDays   = parseInt(opt?.dataset.durationDays  || '0');
        const startVal  = startInp?.value;

        if (!durDays || !startVal) {
            preview.textContent = '— select a plan and start date';
            return;
        }

        const start = new Date(startVal + 'T00:00:00');
        if (durType === 'months') {
            start.setMonth(start.getMonth() + durVal);
        } else {
            start.setDate(start.getDate() + durDays);
        }
        preview.textContent = start.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    document.getElementById('mf-plan')?.addEventListener('change', updateExpiryPreview);
    document.getElementById('mf-start')?.addEventListener('change', updateExpiryPreview);
    updateExpiryPreview();
})();

// ── Freeze row visibility (edit only) ────────────────────────────────────────
@if ($editing)
window.mfOnStatusChange = function () {
    const isFrozen = document.getElementById('mf-status-frozen')?.checked;
    const row = document.getElementById('mf-freeze-row');
    if (row) row.style.display = isFrozen ? 'flex' : 'none';
};
window.mfValidateFreezeDays = function () {
    const inp    = document.getElementById('mf-freeze-days');
    const errEl  = document.getElementById('mf-freeze-error');
    const maxVal = {{ $planMaxFreeze ?? 0 }};
    if (!inp || !errEl) return;
    const val = parseInt(inp.value || '0');
    if (maxVal > 0 && val > maxVal) {
        errEl.textContent = 'Cannot exceed ' + maxVal + ' days (plan limit).';
        errEl.style.display = 'block';
    } else {
        errEl.style.display = 'none';
    }
};
@endif

// ── Payment splits (add-member only) ─────────────────────────────────────────
@if (!$editing)
const MF_METHODS  = ['cash','upi','card','bank','cheque'];
const MF_LABELS   = { cash:'Cash', upi:'UPI', card:'Card', bank:'Bank Transfer', cheque:'Cheque' };
const MF_REF_REQ  = ['card','bank','cheque'];
let mfSplitIdx    = 0;
let mfPlanPaise   = 0;

function mfBuildSplitRow(idx) {
    const div = document.createElement('div');
    div.className = 'mf-split-row';
    div.dataset.idx = idx;

    const methodSel = document.createElement('select');
    methodSel.name = 'splits[' + idx + '][method]';
    methodSel.className = 'mf-input';
    methodSel.style.flex = '1';
    MF_METHODS.forEach(m => {
        const o = document.createElement('option');
        o.value = m; o.textContent = MF_LABELS[m];
        methodSel.appendChild(o);
    });

    const amtInp = document.createElement('input');
    amtInp.type = 'number'; amtInp.min = '0'; amtInp.step = '0.01';
    amtInp.name = 'splits[' + idx + '][amount]';
    amtInp.placeholder = '0.00';
    amtInp.className = 'mf-input';
    amtInp.style.width = '100px';
    amtInp.oninput = mfRecalc;

    const refInp = document.createElement('input');
    refInp.type = 'text';
    refInp.name = 'splits[' + idx + '][reference]';
    refInp.placeholder = 'Ref (optional)';
    refInp.className = 'mf-input mf-ref-inp hidden';
    refInp.style.width = '110px';

    methodSel.onchange = function () {
        refInp.classList.toggle('hidden', !MF_REF_REQ.includes(this.value));
    };

    const rmBtn = document.createElement('button');
    rmBtn.type = 'button'; rmBtn.textContent = '✕';
    rmBtn.className = 'mf-rm-btn';
    rmBtn.onclick = () => { div.remove(); mfRecalc(); mfCheckRemovable(); };

    div.append(methodSel, amtInp, refInp, rmBtn);
    return div;
}

function mfCheckRemovable() {
    const rows = document.querySelectorAll('#mf-splits .mf-split-row');
    rows.forEach(r => {
        const btn = r.querySelector('.mf-rm-btn');
        if (btn) btn.style.visibility = rows.length > 1 ? 'visible' : 'hidden';
    });
}

function mfAddSplit() {
    document.getElementById('mf-splits').appendChild(mfBuildSplitRow(mfSplitIdx++));
    mfCheckRemovable();
    mfRecalc();
}

function mfSetDueVisibility() {
    const on = document.getElementById('mf-is-partial')?.checked;
    document.getElementById('mf-due-fields')?.classList.toggle('hidden', !on);
}

function mfToggleDue() {
    mfSetDueVisibility();
    mfRecalc();
}

function mfRecalc() {
    const total = mfPlanPaise / 100;
    let collected = 0;
    document.querySelectorAll('#mf-splits input[type=number]').forEach(inp => {
        collected += parseFloat(inp.value) || 0;
    });

    document.getElementById('mf-collected-val').textContent = '₹' + collected.toFixed(2);

    const remaining = total - collected;
    const bar     = document.getElementById('mf-collected-bar');
    const dueWrap = document.getElementById('mf-due-wrap');

    if (total > 0 && Math.abs(remaining) > 0.009) {
        bar.classList.remove('hidden');
        const lbl = document.getElementById('mf-shortfall-label');
        if (remaining > 0) {
            lbl.textContent = '₹' + remaining.toFixed(2) + ' short';
            lbl.style.color = '#ea580c';
        } else {
            lbl.textContent = '₹' + Math.abs(remaining).toFixed(2) + ' excess';
            lbl.style.color = '#16a34a';
        }
        if (remaining > 0) {
            dueWrap.classList.remove('hidden');
            const dueAmt = document.getElementById('mf-due-amount');
            if (dueAmt && !dueAmt.dataset.manual) dueAmt.value = remaining.toFixed(2);
        } else {
            dueWrap.classList.add('hidden');
            if (document.getElementById('mf-is-partial')) document.getElementById('mf-is-partial').checked = false;
            mfSetDueVisibility();
        }
    } else {
        bar.classList.add('hidden');
        dueWrap.classList.add('hidden');
        if (document.getElementById('mf-is-partial')) document.getElementById('mf-is-partial').checked = false;
        mfSetDueVisibility();
    }
}

function mfOnPlanChange() {
    const sel = document.getElementById('mf-plan');
    const opt = sel?.options[sel.selectedIndex];
    mfPlanPaise = parseInt(opt?.dataset.price || '0');
    const totalEl = document.getElementById('mf-plan-total');
    if (totalEl) totalEl.textContent = mfPlanPaise ? 'Plan total: ₹' + (mfPlanPaise/100).toLocaleString('en-IN') : '';
    mfRecalc();
}

document.getElementById('mf-plan')?.addEventListener('change', mfOnPlanChange);
document.getElementById('mf-due-amount')?.addEventListener('input', function () { this.dataset.manual = '1'; });

mfAddSplit();
mfOnPlanChange();

// ── Walk-in phone lookup ──────────────────────────────────────────────────────
@if (! $prefill)
(function () {
    const phoneInp  = document.getElementById('mf-phone');
    const hintBox   = document.getElementById('mf-walkin-hint');
    const hintName  = document.getElementById('mf-hint-name');
    const hintMsg   = document.getElementById('mf-hint-msg');
    const fillBtn   = document.getElementById('mf-hint-fill-btn');
    const LOOKUP_URL = '{{ route("tenant.members.walkin-lookup") }}';

    let timer = null;
    let pendingData = null;

    function dismissHint() {
        if (hintBox) hintBox.style.display = 'none';
        pendingData = null;
    }

    if (fillBtn) {
        fillBtn.addEventListener('click', function () {
            if (!pendingData) return;
            document.getElementById('mf-name').value = pendingData.name || '';
            if (pendingData.branch_id) {
                const sel = document.getElementById('mf-branch');
                if (sel) sel.value = pendingData.branch_id;
            }
            dismissHint();
        });
    }

    if (phoneInp) {
        phoneInp.addEventListener('input', function () {
            clearTimeout(timer);
            const phone = this.value.trim().replace(/\s+/g, '');
            if (phone.length < 7) { dismissHint(); return; }

            timer = setTimeout(function () {
                fetch(LOOKUP_URL + '?phone=' + encodeURIComponent(phone), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(function (data) {
                    if (!data.found) { dismissHint(); return; }
                    pendingData = data;
                    hintName.textContent = data.name;
                    hintMsg.textContent  = ' — walk-in inquiry found for this number.';
                    hintBox.style.display = 'flex';
                })
                .catch(function () { dismissHint(); });
            }, 600);
        });
    }
})();
@endif

@endif
</script>

</x-layouts.admin>
