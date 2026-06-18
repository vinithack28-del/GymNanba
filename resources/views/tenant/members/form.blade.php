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

    <div class="mf-layout">

        {{-- ── LEFT ───────────────────────────────────────────────────────── --}}
        <div class="mf-main">

            {{-- Personal info --}}
            <div class="mf-card">
                <h3 class="mf-card-title">Personal information</h3>

                <div class="mf-field">
                    <label class="mf-label" for="mf-name">Full name <span class="mf-req">*</span></label>
                    <input id="mf-name" type="text" name="name"
                        value="{{ old('name', $member?->name ?? '') }}"
                        placeholder="e.g. Priya Sharma"
                        class="mf-input" required maxlength="100">
                </div>

                <div class="mf-row">
                    <div class="mf-field">
                        <label class="mf-label" for="mf-phone">Phone <span class="mf-req">*</span></label>
                        <input id="mf-phone" type="tel" name="phone"
                            value="{{ old('phone', $member?->phone ?? '') }}"
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

                @if ($branches->count() > 1)
                    <div class="mf-field">
                        <label class="mf-label" for="mf-branch">Branch</label>
                        <select id="mf-branch" name="branch_id" class="mf-input">
                            <option value="">— All branches —</option>
                            @foreach ($branches as $br)
                                <option value="{{ $br->id }}"
                                    {{ old('branch_id', $member?->branch_id ?? $selectedBranchId ?? '') == $br->id ? 'selected' : '' }}>
                                    {{ $br->name }}{{ $br->is_primary ? ' (Primary)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

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
                                    {{ old('plan_id', $member?->plan_id ?? '') == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} — ₹{{ number_format($plan->price_paise / 100, 0) }}
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
                    <div class="mf-field">
                        <label class="mf-label">Status</label>
                        <div class="flex gap-4 pt-1">
                            @foreach (['active' => ['label' => 'Active', 'color' => '#1D9E75'], 'inactive' => ['label' => 'Inactive', 'color' => '#888780'], 'frozen' => ['label' => 'Frozen', 'color' => '#378ADD']] as $val => $cfg)
                                <label class="mf-radio-label">
                                    <input type="radio" name="status" value="{{ $val }}"
                                        class="mf-radio"
                                        {{ old('status', $member?->status ?? 'active') === $val ? 'checked' : '' }}>
                                    <span style="color:{{ $cfg['color'] }}">{{ $cfg['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

        </div>

        {{-- ── RIGHT ──────────────────────────────────────────────────────── --}}
        <div class="mf-side">

            @if (!$editing)
                {{-- Payment (create only) --}}
                <div class="mf-card">
                    <h3 class="mf-card-title">Payment</h3>

                    <div class="mf-field">
                        <label class="mf-label" for="mf-amount">Amount collected (₹)</label>
                        <div class="mf-prefix-wrap">
                            <span class="mf-prefix">₹</span>
                            <input id="mf-amount" type="number" name="payment_amount"
                                value="{{ old('payment_amount', 0) }}"
                                min="0" step="0.01" class="mf-input mf-with-prefix">
                        </div>
                    </div>

                    <div class="mf-field">
                        <label class="mf-label" for="mf-method">Payment method</label>
                        <select id="mf-method" name="payment_method" class="mf-input">
                            <option value="">— if amount > 0 —</option>
                            @foreach (['cash' => 'Cash', 'upi' => 'UPI', 'card' => 'Card', 'bank' => 'Bank transfer'] as $val => $lbl)
                                <option value="{{ $val }}" {{ old('payment_method') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
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
                    @if ($member?->balance_paise > 0)
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
.mf-btn-primary { align-items: center; background: var(--app-brand); border: none; border-radius: 0.75rem; color: #0f172a; cursor: pointer; display: inline-flex; font-size: 0.875rem; font-weight: 600; padding: 0.55rem 1.25rem; transition: opacity 160ms; }
.mf-btn-primary:hover { opacity: 0.88; }
.mf-btn-ghost { align-items: center; background: transparent; border: 1px solid var(--app-border); border-radius: 0.75rem; color: var(--app-text-muted); display: inline-flex; font-size: 0.875rem; font-weight: 500; padding: 0.55rem 1.25rem; text-decoration: none; transition: background 140ms, color 140ms; }
.mf-btn-ghost:hover { background: color-mix(in srgb, var(--app-border) 60%, transparent); color: var(--app-text); }
</style>
@endpush

<script>
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
</script>

</x-layouts.admin>
