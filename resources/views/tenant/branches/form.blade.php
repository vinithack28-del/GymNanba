@php
    $editing     = isset($branch);
    $formAction  = $editing ? route('tenant.branches.update', $branch) : route('tenant.branches.store');
    $pageTitle   = $editing ? 'Edit branch' : 'Add branch';
    $pageSub     = $editing ? "Update details for {$branch->name}." : 'Set up a new location for your gym.';

    $amenityIcons = ['pool'=>'🏊','steam'=>'💨','parking'=>'🅿','locker'=>'🔒','cafeteria'=>'☕','ac'=>'❄','wifi'=>'📶'];
    $days = ['mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thu'=>'Thursday','fri'=>'Friday','sat'=>'Saturday','sun'=>'Sunday'];

    $defaultHours = collect(['mon','tue','wed','thu','fri'])->mapWithKeys(fn($d) => [$d => ['open'=>'06:00','close'=>'22:00','closed'=>false]])
        ->merge(['sat'=>['open'=>'07:00','close'=>'20:00','closed'=>false],'sun'=>['open'=>'08:00','close'=>'14:00','closed'=>false]])
        ->toArray();

    $savedHours = $editing ? ($branch->operating_hours ?? $defaultHours) : $defaultHours;
@endphp

<x-layouts.admin
    title="{{ $pageTitle }}"
    eyebrow="Branches"
    heading="{{ $pageTitle }}"
    subheading="{{ $pageSub }}"
>
@slot('headerAction')
    <a href="{{ route('tenant.branches.index') }}" class="bf-back-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        Back to branches
    </a>
@endslot

<form method="POST" action="{{ $formAction }}">
    @csrf
    @if ($editing) @method('PUT') @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="bf-error-box mb-6">
            @foreach ($errors->all() as $e)
                <p class="text-sm">{{ $e }}</p>
            @endforeach
        </div>
    @endif

    <div class="bf-layout">

        {{-- ── LEFT: Main fields ─────────────────────────────────────────── --}}
        <div class="bf-main">

            {{-- Basic information --}}
            <div class="bf-card">
                <h3 class="bf-card-title">Basic information</h3>

                <div class="bf-field">
                    <label class="bf-label" for="f-name">Branch name <span class="bf-req">*</span></label>
                    <input id="f-name" type="text" name="name"
                        value="{{ old('name', $branch->name ?? '') }}"
                        placeholder="e.g. OMR Branch"
                        class="bf-input" required maxlength="80">
                </div>

                <div class="bf-row">
                    <div class="bf-field">
                        <label class="bf-label" for="f-phone">Phone <span class="bf-req">*</span></label>
                        <input id="f-phone" type="tel" name="phone"
                            value="{{ old('phone', $branch->phone ?? '') }}"
                            placeholder="+91 44 2200 0000"
                            class="bf-input" required maxlength="20">
                    </div>
                    <div class="bf-field">
                        <label class="bf-label" for="f-email">Email</label>
                        <input id="f-email" type="email" name="email"
                            value="{{ old('email', $branch->email ?? '') }}"
                            placeholder="branch@yourgym.in"
                            class="bf-input" maxlength="255">
                    </div>
                </div>

                <div class="bf-field">
                    <label class="bf-label" for="f-manager">Branch manager</label>
                    <input id="f-manager" type="text" name="manager_name"
                        value="{{ old('manager_name', $branch->manager_name ?? '') }}"
                        placeholder="Manager name (optional)"
                        class="bf-input" maxlength="100">
                </div>

                <div class="bf-row">
                    <div class="bf-field">
                        <label class="bf-label" for="f-gst">GST number</label>
                        <input id="f-gst" type="text" name="gst_number"
                            value="{{ old('gst_number', $branch->gst_number ?? '') }}"
                            placeholder="15-char GSTIN (optional)"
                            maxlength="15" class="bf-input">
                    </div>
                    <div class="bf-field">
                        <label class="bf-label">Status <span class="bf-req">*</span></label>
                        <div class="flex gap-5 mt-2">
                            <label class="bf-radio">
                                <input type="radio" name="status" value="active" class="bf-checkbox"
                                    {{ old('status', $branch->status ?? 'active') === 'active' ? 'checked' : '' }}>
                                <span style="color:#1D9E75;font-size:0.875rem">● Active</span>
                            </label>
                            <label class="bf-radio">
                                <input type="radio" name="status" value="inactive" class="bf-checkbox"
                                    {{ old('status', $branch->status ?? '') === 'inactive' ? 'checked' : '' }}>
                                <span class="app-muted" style="font-size:0.875rem">● Inactive</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="bf-card">
                <h3 class="bf-card-title">Address</h3>

                <div class="bf-field">
                    <label class="bf-label" for="f-addr1">Address line 1 <span class="bf-req">*</span></label>
                    <input id="f-addr1" type="text" name="address1"
                        value="{{ old('address1', $branch->address1 ?? '') }}"
                        placeholder="Street, area, landmark"
                        class="bf-input" required maxlength="100">
                </div>

                <div class="bf-field">
                    <label class="bf-label" for="f-addr2">Address line 2</label>
                    <input id="f-addr2" type="text" name="address2"
                        value="{{ old('address2', $branch->address2 ?? '') }}"
                        placeholder="Floor, building, near landmark (optional)"
                        class="bf-input" maxlength="100">
                </div>

                <div class="bf-row">
                    <div class="bf-field">
                        <label class="bf-label" for="f-city">City <span class="bf-req">*</span></label>
                        <input id="f-city" type="text" name="city"
                            value="{{ old('city', $branch->city ?? '') }}"
                            placeholder="City"
                            class="bf-input" required maxlength="50">
                    </div>
                    <div class="bf-field">
                        <label class="bf-label" for="f-pin">PIN code <span class="bf-req">*</span></label>
                        <input id="f-pin" type="text" name="pin"
                            value="{{ old('pin', $branch->pin ?? '') }}"
                            placeholder="6 digits"
                            maxlength="6" pattern="\d{6}"
                            class="bf-input" required>
                    </div>
                </div>

                <div class="bf-field">
                    <label class="bf-label" for="f-state">State <span class="bf-req">*</span></label>
                    <select id="f-state" name="state" class="bf-input" required>
                        <option value="">Select state…</option>
                        @foreach ($states as $state)
                            <option value="{{ $state }}"
                                @selected(old('state', $branch->state ?? '') === $state)>{{ $state }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Amenities --}}
            <div class="bf-card">
                <h3 class="bf-card-title">Amenities</h3>
                <div class="bf-amenity-grid">
                    @foreach ($amenityOpts as $key => $label)
                        @php $checked = in_array($key, old('amenities', $branch->amenities ?? [])); @endphp
                        <label class="bf-amenity-check {{ $checked ? 'bf-amenity-on' : '' }}">
                            <input type="checkbox" name="amenities[]" value="{{ $key }}"
                                class="bf-checkbox" {{ $checked ? 'checked' : '' }}>
                            <span>{{ $amenityIcons[$key] ?? '' }} {{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ── RIGHT: Operating hours ────────────────────────────────────── --}}
        <div class="bf-side">
            <div class="bf-card bf-card-sticky">
                <h3 class="bf-card-title">Operating hours</h3>
                <div class="bf-hours-list">
                    @foreach ($days as $key => $label)
                        @php
                            $dayClosed = (bool) old("hours_{$key}_closed", $savedHours[$key]['closed'] ?? false);
                            $dayOpen   = old("hours_{$key}_open",  $savedHours[$key]['open']  ?? '06:00');
                            $dayClose  = old("hours_{$key}_close", $savedHours[$key]['close'] ?? '22:00');
                        @endphp
                        <div class="bf-hours-row">
                            <span class="bf-hours-day">{{ substr($label, 0, 3) }}</span>
                            <label class="bf-hours-toggle">
                                <input type="checkbox" name="hours_{{ $key }}_closed"
                                    value="1"
                                    class="bf-checkbox bf-closed-cb"
                                    data-day="{{ $key }}"
                                    {{ $dayClosed ? 'checked' : '' }}>
                                <span class="text-xs app-muted">Closed</span>
                            </label>
                            <div id="times-{{ $key }}" class="bf-hours-times {{ $dayClosed ? 'bf-hidden' : '' }}">
                                <input type="time" name="hours_{{ $key }}_open"
                                    value="{{ $dayOpen }}" class="bf-time-input">
                                <span class="app-muted text-xs">–</span>
                                <input type="time" name="hours_{{ $key }}_close"
                                    value="{{ $dayClose }}" class="bf-time-input">
                            </div>
                            <span id="closed-lbl-{{ $key }}" class="text-xs app-muted italic {{ $dayClosed ? '' : 'bf-hidden' }}">Closed</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- Form actions --}}
    <div class="bf-footer">
        <a href="{{ route('tenant.branches.index') }}" class="bf-btn-ghost">Cancel</a>
        <button type="submit" class="bf-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M20 6L9 17l-5-5"/></svg>
            {{ $editing ? 'Save changes' : 'Create branch' }}
        </button>
    </div>
</form>

@push('styles')
<style>
.bf-back-btn { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.75rem; color: var(--app-text-muted); display: inline-flex; font-size: 0.82rem; font-weight: 500; gap: 0.4rem; padding: 0.45rem 0.85rem; text-decoration: none; transition: background 140ms; }
.bf-back-btn:hover { background: color-mix(in srgb, var(--app-border) 70%, transparent); color: var(--app-text); }

.bf-error-box { background: rgba(226,75,74,0.1); border: 1px solid rgba(226,75,74,0.3); border-radius: 0.75rem; color: #E24B4A; padding: 0.85rem 1rem; }

.bf-layout { display: grid; gap: 1.25rem; grid-template-columns: 1fr; }
@media (min-width: 900px) { .bf-layout { grid-template-columns: 1fr 340px; } }

.bf-main { display: flex; flex-direction: column; gap: 1.25rem; }

.bf-card { background: var(--app-panel); border: 1px solid var(--app-border); border-radius: 1.5rem; padding: 1.5rem; }
.bf-card-sticky { position: sticky; top: 6rem; }
.bf-card-title { color: var(--app-text-muted); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.14em; margin-bottom: 1.1rem; text-transform: uppercase; }

.bf-field { display: flex; flex-direction: column; gap: 0.3rem; margin-bottom: 0.9rem; }
.bf-field:last-child { margin-bottom: 0; }
.bf-row { display: grid; gap: 0.85rem; grid-template-columns: 1fr 1fr; margin-bottom: 0.9rem; }
.bf-row .bf-field { margin-bottom: 0; }

.bf-label { color: var(--app-text-muted); font-size: 0.79rem; font-weight: 500; }
.bf-req { color: #E24B4A; }
.bf-input { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.65rem; color: var(--app-text); font-size: 0.875rem; outline: none; padding: 0.55rem 0.75rem; transition: border-color 160ms; width: 100%; }
.bf-input:focus { border-color: color-mix(in srgb, var(--app-brand) 60%, var(--app-border)); }
.bf-checkbox { accent-color: var(--app-brand); cursor: pointer; }
.bf-radio { align-items: center; cursor: pointer; display: flex; gap: 0.4rem; }

.bf-amenity-grid { display: grid; gap: 0.5rem; grid-template-columns: repeat(2, 1fr); }
.bf-amenity-check { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.65rem; cursor: pointer; display: flex; font-size: 0.82rem; gap: 0.5rem; padding: 0.5rem 0.75rem; transition: border-color 140ms, background 140ms; }
.bf-amenity-check:hover { border-color: color-mix(in srgb, var(--app-brand) 40%, var(--app-border)); }
.bf-amenity-on { background: color-mix(in srgb, var(--app-brand-soft) 60%, transparent); border-color: color-mix(in srgb, var(--app-brand) 45%, var(--app-border)); }

.bf-hours-list { display: flex; flex-direction: column; gap: 0.5rem; }
.bf-hours-row { align-items: center; display: flex; gap: 0.6rem; min-height: 2rem; }
.bf-hours-day { color: var(--app-text-muted); font-size: 0.78rem; font-weight: 600; min-width: 2.5rem; }
.bf-hours-toggle { align-items: center; display: flex; gap: 0.3rem; min-width: 4.5rem; }
.bf-hours-times { align-items: center; display: flex; flex: 1; gap: 0.35rem; }
.bf-time-input { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.5rem; color: var(--app-text); font-size: 0.78rem; outline: none; padding: 0.28rem 0.45rem; width: 5.5rem; }
.bf-hidden { display: none !important; }

.bf-footer { border-top: 1px solid var(--app-border); display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1.25rem; }

.bf-btn-primary { align-items: center; background: var(--app-brand); border: none; border-radius: 0.75rem; color: #0f172a; cursor: pointer; display: inline-flex; font-size: 0.875rem; font-weight: 600; gap: 0.4rem; padding: 0.55rem 1.1rem; transition: opacity 160ms; }
.bf-btn-primary:hover { opacity: 0.88; }
.bf-btn-ghost { align-items: center; background: transparent; border: 1px solid var(--app-border); border-radius: 0.75rem; color: var(--app-text-muted); cursor: pointer; display: inline-flex; font-size: 0.875rem; font-weight: 500; padding: 0.55rem 1.1rem; text-decoration: none; transition: background 140ms; }
.bf-btn-ghost:hover { background: color-mix(in srgb, var(--app-border) 60%, transparent); color: var(--app-text); }
</style>
@endpush

<script>
document.querySelectorAll('.bf-closed-cb').forEach(cb => {
    cb.addEventListener('change', function () {
        const day   = this.dataset.day;
        const times = document.getElementById('times-' + day);
        const lbl   = document.getElementById('closed-lbl-' + day);
        times?.classList.toggle('bf-hidden', this.checked);
        lbl?.classList.toggle('bf-hidden', !this.checked);
    });
});
document.querySelectorAll('.bf-amenity-check').forEach(wrap => {
    wrap.querySelector('input')?.addEventListener('change', function () {
        wrap.classList.toggle('bf-amenity-on', this.checked);
    });
});
</script>

</x-layouts.admin>
