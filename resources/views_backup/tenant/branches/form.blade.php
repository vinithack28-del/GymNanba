@php
    $editing    = isset($branch);
    $formAction = $editing ? route('tenant.branches.update', $branch) : route('tenant.branches.store');
    $pageTitle  = $editing ? 'Edit Branch' : 'Add Branch';
    $pageSub    = $editing ? "Update details for {$branch->name}." : 'Set up a new location for your gym.';

    $amenityIcons = ['pool'=>'🏊','steam'=>'💨','parking'=>'🅿','locker'=>'🔒','cafeteria'=>'☕','ac'=>'❄','wifi'=>'📶'];
    $days = ['mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thu'=>'Thursday','fri'=>'Friday','sat'=>'Saturday','sun'=>'Sunday'];

    $defaultHours = collect(['mon','tue','wed','thu','fri'])
        ->mapWithKeys(fn($d) => [$d => ['open'=>'06:00','close'=>'22:00','closed'=>false]])
        ->merge(['sat'=>['open'=>'07:00','close'=>'20:00','closed'=>false],'sun'=>['open'=>'08:00','close'=>'14:00','closed'=>false]])
        ->toArray();

    $savedHours = $editing ? ($branch->operating_hours ?? $defaultHours) : $defaultHours;

    $wizardSteps = [
        1 => ['title' => 'Basic Info',      'desc' => 'Name, contact & status'],
        2 => ['title' => 'Address',         'desc' => 'Location & PIN code'],
        3 => ['title' => 'Amenities',       'desc' => 'Facilities available'],
        4 => ['title' => 'Operating Hours', 'desc' => 'Daily open & close times'],
    ];

    // Determine which step to open after a server validation error
    $initialStep = (int) old('_wizard_step', 1);
    if ($errors->any()) {
        if ($errors->hasAny(['name','phone','email','manager_name','gst_number','status'])) {
            $initialStep = 1;
        } elseif ($errors->hasAny(['address1','address2','city','pin','state'])) {
            $initialStep = 2;
        } elseif ($errors->has('amenities')) {
            $initialStep = 3;
        }
    }
@endphp

<x-layouts.admin
    title="{{ $pageTitle }}"
    eyebrow="Branches"
    heading="{{ $pageTitle }}"
    subheading="{{ $pageSub }}"
>
    <x-slot:headerAction>
        <a
            href="{{ route('tenant.branches.index') }}"
            class="app-panel-strong inline-flex items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-medium transition hover:opacity-80"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Back to Branches
        </a>
    </x-slot:headerAction>

    <form method="POST" action="{{ $formAction }}" id="branch-wizard-form" class="app-panel rounded-[2rem] border">
        @csrf
        @if ($editing) @method('PUT') @endif
        <input type="hidden" name="_wizard_step" id="wizard_step_field" value="{{ $initialStep }}">

        {{-- ── Step indicator ──────────────────────────────────────────── --}}
        <div class="border-b border-[var(--app-border)] px-6 py-4">
            <nav class="flex items-start justify-center gap-0" aria-label="Wizard steps">
                @foreach ($wizardSteps as $num => $step)
                    {{-- Step button --}}
                    <button
                        type="button"
                        class="wizard-step-btn flex flex-col items-center gap-1.5 rounded-2xl px-3 py-2 transition-all"
                        data-step="{{ $num }}"
                        data-editing="{{ $editing ? 'true' : 'false' }}"
                    >
                        <span class="wizard-step-num inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold transition-all"
                              data-step="{{ $num }}">
                            <span class="step-num-text">{{ $num }}</span>
                            <svg class="step-check hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        </span>
                        <span class="wizard-step-label text-[11px] font-semibold leading-tight transition-all">{{ $step['title'] }}</span>
                    </button>

                    {{-- Connector arrow (not after last) --}}
                    @if ($num < 4)
                        <svg class="wizard-connector mx-1 mt-3 h-4 w-4 shrink-0 transition-colors" data-after="{{ $num }}"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    @endif
                @endforeach
            </nav>
        </div>

        {{-- ── Form body ─────────────────────────────────────────────────── --}}
        <div class="p-6">
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- ── Step 1: Basic Info ─────────────────────────────────── --}}
            <section id="branch-step-1" class="wizard-step-section space-y-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Branch Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name"
                            value="{{ old('name', $branch->name ?? '') }}"
                            placeholder="e.g. OMR Branch"
                            class="w-full rounded-2xl border px-4 py-3 outline-none transition focus:ring-1 focus:ring-[var(--app-brand)]"
                            required maxlength="80">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Phone <span class="text-red-400">*</span></label>
                        <input type="tel" name="phone"
                            value="{{ old('phone', $branch->phone ?? '') }}"
                            placeholder="+91 44 2200 0000"
                            class="w-full rounded-2xl border px-4 py-3 outline-none transition focus:ring-1 focus:ring-[var(--app-brand)]"
                            required maxlength="20">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Email <span class="app-muted font-normal">(optional)</span></label>
                        <input type="email" name="email"
                            value="{{ old('email', $branch->email ?? '') }}"
                            placeholder="branch@yourgym.in"
                            class="w-full rounded-2xl border px-4 py-3 outline-none transition focus:ring-1 focus:ring-[var(--app-brand)]"
                            maxlength="255">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Branch Manager <span class="app-muted font-normal">(optional)</span></label>
                        <input type="text" name="manager_name"
                            value="{{ old('manager_name', $branch->manager_name ?? '') }}"
                            placeholder="Manager name"
                            class="w-full rounded-2xl border px-4 py-3 outline-none transition focus:ring-1 focus:ring-[var(--app-brand)]"
                            maxlength="100">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">GST Number <span class="app-muted font-normal">(optional)</span></label>
                        <input type="text" name="gst_number"
                            value="{{ old('gst_number', $branch->gst_number ?? '') }}"
                            placeholder="15-char GSTIN"
                            maxlength="15"
                            class="w-full rounded-2xl border px-4 py-3 font-mono outline-none transition focus:ring-1 focus:ring-[var(--app-brand)]">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Status <span class="text-red-400">*</span></label>
                        <div class="flex gap-3">
                            @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $val => $lbl)
                                @php $checked = old('status', $branch->status ?? 'active') === $val; @endphp
                                <label class="flex flex-1 cursor-pointer items-center gap-3 rounded-2xl border px-4 py-3 transition {{ $checked ? 'border-[var(--app-brand)] bg-[color-mix(in_srgb,var(--app-brand)_8%,transparent)]' : 'app-panel-strong hover:opacity-90' }}">
                                    <input type="radio" name="status" value="{{ $val }}" class="accent-[var(--app-brand)]" {{ $checked ? 'checked' : '' }}>
                                    <span class="text-sm font-medium">{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            {{-- ── Step 2: Address ────────────────────────────────────── --}}
            <section id="branch-step-2" class="wizard-step-section hidden space-y-5">

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Address Line 1 <span class="text-red-400">*</span></label>
                        <input type="text" name="address1"
                            value="{{ old('address1', $branch->address1 ?? '') }}"
                            placeholder="Street, area, landmark"
                            class="w-full rounded-2xl border px-4 py-3 outline-none transition focus:ring-1 focus:ring-[var(--app-brand)]"
                            required maxlength="100">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Address Line 2 <span class="app-muted font-normal">(optional)</span></label>
                        <input type="text" name="address2"
                            value="{{ old('address2', $branch->address2 ?? '') }}"
                            placeholder="Floor, building, near landmark"
                            class="w-full rounded-2xl border px-4 py-3 outline-none transition focus:ring-1 focus:ring-[var(--app-brand)]"
                            maxlength="100">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">City <span class="text-red-400">*</span></label>
                        <input type="text" name="city"
                            value="{{ old('city', $branch->city ?? '') }}"
                            placeholder="City"
                            class="w-full rounded-2xl border px-4 py-3 outline-none transition focus:ring-1 focus:ring-[var(--app-brand)]"
                            required maxlength="50">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">PIN Code <span class="text-red-400">*</span></label>
                        <input type="text" name="pin"
                            value="{{ old('pin', $branch->pin ?? '') }}"
                            placeholder="6 digits"
                            maxlength="6" pattern="\d{6}"
                            class="w-full rounded-2xl border px-4 py-3 font-mono outline-none transition focus:ring-1 focus:ring-[var(--app-brand)]"
                            required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">State <span class="text-red-400">*</span></label>
                        <select name="state" class="w-full rounded-2xl border px-4 py-3 outline-none transition focus:ring-1 focus:ring-[var(--app-brand)]" required>
                            <option value="">Select state…</option>
                            @foreach ($states as $state)
                                <option value="{{ $state }}" @selected(old('state', $branch->state ?? '') === $state)>{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            {{-- ── Step 3: Amenities ──────────────────────────────────── --}}
            <section id="branch-step-3" class="wizard-step-section hidden space-y-5">

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($amenityOpts as $key => $label)
                        @php $checked = in_array($key, old('amenities', $branch->amenities ?? [])); @endphp
                        <label class="amenity-pill flex cursor-pointer items-center gap-2.5 rounded-2xl border px-4 py-3 transition {{ $checked ? 'border-[var(--app-brand)] bg-[color-mix(in_srgb,var(--app-brand)_10%,transparent)] text-[var(--app-brand)]' : 'app-panel-strong hover:opacity-90' }}">
                            <input type="checkbox" name="amenities[]" value="{{ $key }}"
                                class="sr-only" {{ $checked ? 'checked' : '' }}>
                            <span class="text-base leading-none">{{ $amenityIcons[$key] ?? '✓' }}</span>
                            <span class="text-sm font-medium">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- ── Step 4: Operating Hours ────────────────────────────── --}}
            <section id="branch-step-4" class="wizard-step-section hidden space-y-5">

                <div class="app-panel-strong divide-y divide-[var(--app-border)] rounded-2xl border">
                    @foreach ($days as $key => $label)
                        @php
                            $dayClosed = (bool) old("hours_{$key}_closed", $savedHours[$key]['closed'] ?? false);
                            $dayOpen   = old("hours_{$key}_open",  $savedHours[$key]['open']  ?? '06:00');
                            $dayClose  = old("hours_{$key}_close", $savedHours[$key]['close'] ?? '22:00');
                        @endphp
                        <div class="flex items-center gap-4 px-4 py-3">
                            <span class="w-12 text-sm font-semibold">{{ substr($label, 0, 3) }}</span>

                            <label class="flex cursor-pointer items-center gap-2">
                                <input type="checkbox" name="hours_{{ $key }}_closed" value="1"
                                    class="hours-closed-cb h-4 w-4 accent-[var(--app-brand)]"
                                    data-day="{{ $key }}"
                                    {{ $dayClosed ? 'checked' : '' }}>
                                <span class="app-muted text-xs">Closed</span>
                            </label>

                            <div id="times-{{ $key }}" class="ml-auto flex items-center gap-2 {{ $dayClosed ? 'hidden' : '' }}">
                                <input type="time" name="hours_{{ $key }}_open" value="{{ $dayOpen }}"
                                    class="rounded-xl border px-3 py-1.5 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                    style="background:var(--app-panel);color:var(--app-text);border-color:var(--app-border)">
                                <span class="app-muted text-xs">to</span>
                                <input type="time" name="hours_{{ $key }}_close" value="{{ $dayClose }}"
                                    class="rounded-xl border px-3 py-1.5 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                    style="background:var(--app-panel);color:var(--app-text);border-color:var(--app-border)">
                            </div>
                            <span id="closed-lbl-{{ $key }}" class="app-muted ml-auto text-xs italic {{ $dayClosed ? '' : 'hidden' }}">Day off</span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ── Step navigation bar ───────────────────────────────── --}}
            <div class="mt-8 flex items-center justify-between border-t border-[var(--app-border)] pt-6">
                {{-- Left: Cancel (step 1) or Back (steps 2-4) --}}
                <div>
                    <a id="wizard-cancel" href="{{ route('tenant.branches.index') }}"
                       class="wizard-nav-btn app-panel-strong rounded-2xl border px-5 py-3 text-sm font-medium transition hover:opacity-80">
                        Cancel
                    </a>
                    <button type="button" id="wizard-back"
                            class="wizard-nav-btn hidden app-panel-strong rounded-2xl border px-5 py-3 text-sm font-medium transition hover:opacity-80">
                        ← Back
                    </button>
                </div>

                {{-- Right: Next or Save --}}
                <div class="flex items-center gap-3">
                    <span id="wizard-step-label" class="app-muted hidden text-xs sm:block"></span>
                    <button type="button" id="wizard-next"
                            class="inline-flex items-center gap-2 rounded-2xl bg-[var(--app-brand)] px-6 py-3 text-sm font-semibold text-slate-950 transition hover:opacity-90">
                        Next
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                    <button type="submit" id="wizard-save"
                            style="display:none"
                            class="inline-flex items-center gap-2 rounded-2xl bg-[var(--app-brand)] px-6 py-3 text-sm font-semibold text-slate-950 transition hover:opacity-90">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        {{ $editing ? 'Save Changes' : 'Create Branch' }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
    (function () {
        const TOTAL_STEPS   = 4;
        const isEditing     = {{ $editing ? 'true' : 'false' }};
        let   currentStep   = {{ $initialStep }};
        const completedSteps = new Set(isEditing ? [1,2,3,4] : []);

        // ── DOM refs ──────────────────────────────────────────────────
        const sections    = () => document.querySelectorAll('.wizard-step-section');
        const stepBtns    = () => document.querySelectorAll('.wizard-step-btn');
        const connectors  = () => document.querySelectorAll('.wizard-connector');
        const cancelBtn   = document.getElementById('wizard-cancel');
        const backBtn     = document.getElementById('wizard-back');
        const nextBtn     = document.getElementById('wizard-next');
        const saveBtn     = document.getElementById('wizard-save');
        const stepField   = document.getElementById('wizard_step_field');
        const stepLabel   = document.getElementById('wizard-step-label');

        // ── Apply visual state for all steps ─────────────────────────
        function applyStepStyles() {
            stepBtns().forEach(btn => {
                const n     = parseInt(btn.dataset.step);
                const num   = btn.querySelector('.wizard-step-num');
                const label = btn.querySelector('.wizard-step-label');
                const check = btn.querySelector('.step-check');
                const numTxt= btn.querySelector('.step-num-text');

                const isActive    = n === currentStep;
                const isCompleted = completedSteps.has(n) && n !== currentStep;
                const canClick    = isEditing || isCompleted || n === currentStep;

                // Number badge
                num.className = 'wizard-step-num inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold transition-all';
                if (isActive) {
                    num.style.background = 'var(--app-brand)';
                    num.style.color      = '#0f172a';
                } else if (isCompleted) {
                    num.style.background = 'color-mix(in srgb, var(--app-brand) 20%, transparent)';
                    num.style.color      = 'var(--app-brand)';
                } else {
                    num.style.background = 'var(--app-panel-strong)';
                    num.style.color      = 'var(--app-text-muted)';
                }

                // Show check or number
                if (isCompleted) {
                    check?.classList.remove('hidden');
                    numTxt?.classList.add('hidden');
                } else {
                    check?.classList.add('hidden');
                    numTxt?.classList.remove('hidden');
                }

                // Text colour
                if (label) label.style.color = isActive ? 'var(--app-text)' : isCompleted ? 'var(--app-text)' : 'var(--app-text-muted)';

                // Cursor / pointer
                btn.style.cursor = canClick ? 'pointer' : 'default';
                btn.style.opacity = canClick ? '1' : '0.45';

                // Active step highlight
                btn.style.background = isActive ? 'color-mix(in srgb, var(--app-brand) 6%, transparent)' : 'transparent';
            });

            // Connectors
            connectors().forEach(c => {
                const after = parseInt(c.dataset.after);
                c.style.color = (after < currentStep) ? 'var(--app-brand)' : 'var(--app-border)';
            });
        }

        // ── Show/hide sections ────────────────────────────────────────
        function showStep(n) {
            sections().forEach((sec, i) => {
                const stepN = i + 1;
                sec.classList.toggle('hidden', stepN !== n);
            });

            stepField.value = n;

            // Back / Cancel
            if (n === 1) {
                cancelBtn.classList.remove('hidden');
                backBtn.classList.add('hidden');
            } else {
                cancelBtn.classList.add('hidden');
                backBtn.classList.remove('hidden');
            }

            // Next / Save
            if (n === TOTAL_STEPS) {
                nextBtn.style.display = 'none';
                saveBtn.style.display = 'inline-flex';
            } else {
                nextBtn.style.display = 'inline-flex';
                saveBtn.style.display = 'none';
            }

            // Label
            const labels = @json(array_map(fn($s) => $s['title'], $wizardSteps));
            stepLabel.textContent = `Step ${n} of ${TOTAL_STEPS}`;

            currentStep = n;
            applyStepStyles();
        }

        // ── Client-side validation for current step ───────────────────
        function validateStep(n) {
            const section = document.getElementById('branch-step-' + n);
            const fields  = section.querySelectorAll('input[required], select[required]');
            let ok = true;
            fields.forEach(f => {
                if (!f.reportValidity()) ok = false;
            });
            return ok;
        }

        // ── Button events ─────────────────────────────────────────────
        nextBtn.addEventListener('click', () => {
            if (!validateStep(currentStep)) return;
            completedSteps.add(currentStep);
            showStep(currentStep + 1);
        });

        backBtn.addEventListener('click', () => {
            showStep(currentStep - 1);
        });

        // Step indicator clicks
        stepBtns().forEach(btn => {
            btn.addEventListener('click', () => {
                const n = parseInt(btn.dataset.step);
                if (!isEditing && !completedSteps.has(n) && n !== currentStep) return;
                // In add mode going forward: validate intervening steps
                if (!isEditing && n > currentStep) {
                    for (let s = currentStep; s < n; s++) {
                        if (!validateStep(s)) return;
                        completedSteps.add(s);
                    }
                }
                showStep(n);
            });
        });

        // ── Operating hours closed toggle ─────────────────────────────
        document.querySelectorAll('.hours-closed-cb').forEach(cb => {
            cb.addEventListener('change', function () {
                const day   = this.dataset.day;
                const times = document.getElementById('times-' + day);
                const lbl   = document.getElementById('closed-lbl-' + day);
                times?.classList.toggle('hidden', this.checked);
                lbl?.classList.toggle('hidden', !this.checked);
            });
        });

        // ── Amenity pill toggle ───────────────────────────────────────
        document.querySelectorAll('.amenity-pill').forEach(pill => {
            pill.addEventListener('change', function () {
                const cb = this.querySelector('input');
                this.classList.toggle('border-[var(--app-brand)]', cb.checked);
                this.classList.toggle('bg-[color-mix(in_srgb,var(--app-brand)_10%,transparent)]', cb.checked);
                this.classList.toggle('text-[var(--app-brand)]', cb.checked);
                this.classList.toggle('app-panel-strong', !cb.checked);
            });
        });

        // ── Status radio pill ─────────────────────────────────────────
        document.querySelectorAll('input[name="status"]').forEach(radio => {
            radio.addEventListener('change', function () {
                document.querySelectorAll('input[name="status"]').forEach(r => {
                    const lbl = r.closest('label');
                    lbl.classList.toggle('border-[var(--app-brand)]', r.checked);
                    lbl.classList.toggle('bg-[color-mix(in_srgb,var(--app-brand)_8%,transparent)]', r.checked);
                    lbl.classList.toggle('app-panel-strong', !r.checked);
                });
            });
        });

        // ── Bootstrap ─────────────────────────────────────────────────
        // Mark steps before initialStep as completed (for edit mode or after server error)
        for (let s = 1; s < currentStep; s++) completedSteps.add(s);
        showStep(currentStep);
    })();
    </script>
</x-layouts.admin>
