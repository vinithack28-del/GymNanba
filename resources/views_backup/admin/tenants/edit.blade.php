<x-layouts.admin
    title="Edit Tenant"
    eyebrow=""
    heading="Edit {{ $tenant->gym_name }}"
    subheading=""
>
    <div class="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">

        {{-- Step sidebar --}}
        <aside class="app-panel rounded-[2rem] border p-5">
            <div class="space-y-4" id="wizard-steps">
                @foreach ([
                    1 => ['title' => 'Business Info',       'desc' => 'Gym name, type, location, and GST'],
                    2 => ['title' => 'Owner Details',       'desc' => 'Owner contact and login credentials'],
                    3 => ['title' => 'Routing & Technical', 'desc' => 'Subdomain, domain mode, and language'],
                    4 => ['title' => 'Status & Notes',      'desc' => 'Tenant status and internal notes'],
                ] as $step => $meta)
                    <button
                        type="button"
                        data-step-jump="{{ $step }}"
                        class="wizard-step app-panel-strong flex w-full items-start gap-4 rounded-[1.5rem] border px-4 py-4 text-left transition hover:opacity-90"
                    >
                        <span class="wizard-step-badge app-brand-soft app-brand-text inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold">
                            {{ str_pad((string) $step, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span>
                            <span class="block text-sm font-semibold">{{ $meta['title'] }}</span>
                            <span class="app-muted mt-1 block text-xs">{{ $meta['desc'] }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
        </aside>

        {{-- Main form --}}
        <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" id="tenant-wizard" class="app-panel rounded-[2rem] border p-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <input type="hidden" id="current-step" value="{{ $errors->any() ? old('_step', 1) : 1 }}">

            {{-- Step 1: Business Info --}}
            <section class="wizard-panel space-y-6" data-step-panel="1">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.36em] text-[var(--app-info)]">Step 1 of 4</p>
                    <h3 class="mt-3 text-2xl font-semibold">Business Info</h3>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Gym Name</label>
                        <input name="gym_name" value="{{ old('gym_name', $tenant->gym_name) }}"
                               class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Business Type</label>
                        <select name="business_type" class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)" required>
                            @foreach ($businessTypes as $type)
                                <option value="{{ $type }}" @selected(old('business_type', $tenant->business_type) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">City</label>
                        <input name="city" value="{{ old('city', $tenant->city) }}"
                               class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">State</label>
                        <input name="state" value="{{ old('state', $tenant->state) }}"
                               class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Address</label>
                        <textarea name="address" rows="3" class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)" required>{{ old('address', $tenant->address) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">GST Number <span class="app-muted font-normal">(optional)</span></label>
                        <input name="gst_number" value="{{ old('gst_number', $tenant->gst_number) }}"
                               class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Phone</label>
                        <input name="phone" value="{{ old('phone', $tenant->phone) }}"
                               class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)" required minlength="10">
                    </div>
                </div>
            </section>

            {{-- Step 2: Owner Details --}}
            <section class="wizard-panel hidden space-y-6" data-step-panel="2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.36em] text-[var(--app-info)]">Step 2 of 4</p>
                    <h3 class="mt-3 text-2xl font-semibold">Owner Details</h3>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Owner Name</label>
                        <input name="owner_name" value="{{ old('owner_name', $tenant->owner_name) }}"
                               class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Owner Email</label>
                        <input type="email" name="owner_email" value="{{ old('owner_email', $tenant->owner_email) }}"
                               class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">New Password <span class="app-muted font-normal">(leave blank to keep current)</span></label>
                        <input type="password" name="owner_password" autocomplete="new-password"
                               class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Confirm New Password</label>
                        <input type="password" name="owner_password_confirmation" autocomplete="new-password"
                               class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    </div>
                </div>
            </section>

            {{-- Step 3: Routing & Technical --}}
            <section class="wizard-panel hidden space-y-6" data-step-panel="3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.36em] text-[var(--app-info)]">Step 3 of 4</p>
                    <h3 class="mt-3 text-2xl font-semibold">Routing &amp; Technical</h3>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Subdomain</label>
                        <input name="subdomain" value="{{ old('subdomain', $tenant->subdomain) }}"
                               class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Domain Mode</label>
                        <select name="domain_mode" id="domain_mode" class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)" required>
                            <option value="shared"   @selected(old('domain_mode', $tenant->domain_mode) === 'shared')>Shared domain</option>
                            <option value="separate" @selected(old('domain_mode', $tenant->domain_mode) === 'separate')>Separate domain</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 {{ old('domain_mode', $tenant->domain_mode) === 'separate' ? '' : 'hidden' }}" data-separate-domain-field>
                        <label class="mb-2 block text-sm font-medium">Custom Domain</label>
                        <input name="custom_domain" value="{{ old('custom_domain', $tenant->custom_domain) }}" placeholder="gym.example.com"
                               class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    </div>
                    <div class="{{ old('domain_mode', $tenant->domain_mode) === 'separate' ? '' : 'hidden' }}" data-separate-domain-field>
                        <label class="mb-2 block text-sm font-medium">Database Mode</label>
                        <select name="database_mode" id="database_mode" class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                            <option value="shared"   @selected(old('database_mode', $tenant->database_mode) === 'shared')>Main database</option>
                            <option value="separate" @selected(old('database_mode', $tenant->database_mode) === 'separate')>Separate database</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Default Language</label>
                        <select name="default_language" class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)" required>
                            @foreach ($languages as $language)
                                <option value="{{ $language->locale_code }}" @selected(old('default_language', $tenant->default_language) === $language->locale_code)>
                                    {{ $language->display_name }} ({{ $language->locale_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @php $currentPlanId = $tenant->subscriptions->sortByDesc('id')->first()?->plan_id; @endphp
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Plan</label>
                        <select name="plan_id" class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                            <option value="">— No change —</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(old('plan_id', $currentPlanId) == $plan->id)>
                                    {{ $plan->name }}{{ $plan->is_trial ? ' (Trial – '.$plan->trial_days.' days)' : ' · Rs. '.number_format($plan->price_paise / 100, 2).' / '.$plan->billing_cycle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Trial End Date</label>
                        <input type="date" name="trial_end_date"
                               value="{{ old('trial_end_date', $tenant->subscriptions->sortByDesc('id')->first()?->trial_end_date?->toDateString()) }}"
                               class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    </div>
                </div>
            </section>

            {{-- Step 4: Status & Notes --}}
            <section class="wizard-panel hidden space-y-6" data-step-panel="4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.36em] text-[var(--app-info)]">Step 4 of 4</p>
                    <h3 class="mt-3 text-2xl font-semibold">Status &amp; Notes</h3>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Status</label>
                        <select name="status" class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $tenant->status) === $status)>
                                    {{ ucwords(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Notes <span class="app-muted font-normal">(internal only)</span></label>
                        <textarea name="notes" rows="4" class="w-full rounded-2xl border px-4 py-3 outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">{{ old('notes', $tenant->notes) }}</textarea>
                    </div>
                </div>
            </section>

            {{-- Navigation footer --}}
            <div class="mt-8 flex flex-col-reverse gap-3 border-t pt-6 sm:flex-row sm:items-center sm:justify-between" style="border-color:var(--app-border)">
                <button type="button" id="wizard-prev"
                        class="app-panel-strong invisible rounded-2xl border px-5 py-3 text-sm font-semibold transition hover:opacity-90">
                    {{ __('common.back') }}
                </button>
                <div class="flex gap-3">
                    <button type="button" id="wizard-next"
                            class="rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-orange-400">
                        {{ __('common.next') }}
                    </button>
                    <button type="submit" id="wizard-submit"
                            class="hidden rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-orange-400">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.tenants.show', $tenant) }}" id="wizard-cancel"
                       class="hidden app-panel-strong rounded-2xl border px-5 py-3 text-sm font-semibold transition hover:opacity-90">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script>
    (() => {
        const form            = document.getElementById('tenant-wizard');
        const panels          = Array.from(form.querySelectorAll('[data-step-panel]'));
        const stepButtons     = Array.from(document.querySelectorAll('[data-step-jump]'));
        const currentStepInput = document.getElementById('current-step');
        const nextBtn         = document.getElementById('wizard-next');
        const prevBtn         = document.getElementById('wizard-prev');
        const submitBtn       = document.getElementById('wizard-submit');
        const cancelBtn       = document.getElementById('wizard-cancel');
        const domainModeSelect   = document.getElementById('domain_mode');
        const databaseModeSelect = document.getElementById('database_mode');
        const totalSteps      = panels.length;

        const getStep = () => Number(currentStepInput.value || 1);

        const getFieldLabel = (field) => {
            const lbl = field.closest('div')?.querySelector('label');
            return lbl ? lbl.textContent.trim().replace(/\s*\(.*\)$/, '').trim() : (field.placeholder || field.name || 'Field');
        };

        const getFieldError = (field) => {
            if (field.hasAttribute('required') && !field.value.trim())
                return `${getFieldLabel(field)} is required`;
            if (field.value && !field.checkValidity()) {
                const lbl = getFieldLabel(field);
                if (field.validity.tooShort)
                    return field.name?.includes('phone')
                        ? `${lbl} must be at least ${field.minLength} digits`
                        : `${lbl} must be at least ${field.minLength} characters`;
                if (field.validity.typeMismatch)
                    return `${lbl} must be a valid ${field.type}`;
                return `${lbl} is invalid`;
            }
            return null;
        };

        const validateStep = (step) => {
            const panel = panels.find(p => Number(p.dataset.stepPanel) === step);
            if (!panel) return true;

            // Clear previous errors
            panel.querySelector('.wizard-error-box')?.remove();
            panel.querySelectorAll('[data-invalid]').forEach(f => { f.style.borderColor = ''; delete f.dataset.invalid; });

            const errors = [];
            for (const field of panel.querySelectorAll('input, select, textarea')) {
                if (['hidden', 'button', 'submit', 'checkbox'].includes(field.type)) continue;
                const err = getFieldError(field);
                if (err) {
                    errors.push(err);
                    field.style.borderColor = '#ef4444';
                    field.dataset.invalid = '1';
                    field.addEventListener('input', () => {
                        field.style.borderColor = '';
                        delete field.dataset.invalid;
                        panel.querySelector('.wizard-error-box')?.remove();
                    }, { once: true });
                }
            }

            if (errors.length) {
                const box = document.createElement('div');
                box.className = 'wizard-error-box';
                box.style.cssText = 'border-radius:1.1rem;padding:.85rem 1.1rem;margin-bottom:1rem;background:color-mix(in srgb,#ef4444 10%,transparent);border:1px solid color-mix(in srgb,#ef4444 30%,transparent);color:#dc2626;font-size:.85rem';
                box.innerHTML = errors.map(e => `<div style="display:flex;gap:.5rem;align-items:baseline"><span>•</span><span>${e}</span></div>`).join('');
                panel.prepend(box);
                box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                return false;
            }
            return true;
        };

        const renderStep = (step) => {
            panels.forEach(p => p.classList.toggle('hidden', Number(p.dataset.stepPanel) !== step));

            stepButtons.forEach((btn, i) => {
                const active = i + 1 === step;
                btn.classList.toggle('bg-[var(--app-brand)]', active);
                btn.classList.toggle('text-slate-950', active);
                btn.classList.toggle('app-panel-strong', !active);
            });

            prevBtn.classList.toggle('invisible', step === 1);
            nextBtn.classList.toggle('hidden', step === totalSteps);
            submitBtn.classList.toggle('hidden', step !== totalSteps);
            cancelBtn.classList.toggle('hidden', step !== totalSteps);
        };

        const setStep = (step) => {
            currentStepInput.value = step;
            renderStep(step);
        };

        nextBtn.addEventListener('click', () => {
            const step = getStep();
            if (!validateStep(step)) return;
            setStep(Math.min(step + 1, totalSteps));
        });

        prevBtn.addEventListener('click', () => setStep(Math.max(getStep() - 1, 1)));

        stepButtons.forEach((btn, i) => {
            btn.addEventListener('click', () => {
                const target  = i + 1;
                const current = getStep();
                if (target > current && !validateStep(current)) return;
                setStep(target);
            });
        });

        const syncDomainFields = () => {
            const sep = domainModeSelect?.value === 'separate';
            document.querySelectorAll('[data-separate-domain-field]').forEach(el => el.classList.toggle('hidden', !sep));
            const cd = form.querySelector('[name="custom_domain"]');
            if (cd) cd.toggleAttribute('required', sep);
            if (databaseModeSelect && !sep) databaseModeSelect.value = 'shared';
        };

        domainModeSelect?.addEventListener('change', syncDomainFields);
        syncDomainFields();
        renderStep(getStep());
    })();
    </script>
</x-layouts.admin>
