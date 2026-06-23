<x-layouts.admin
    title="Add Tenant"
    eyebrow=""
    heading="{{ __('tenants.wizard.heading') }}"
    subheading=""
>
    <div class="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="app-panel rounded-[2rem] border p-5">
            <div class="space-y-4" id="wizard-steps">
                @foreach ([
                    1 => ['title' => __('tenants.wizard.steps.business.title'), 'desc' => __('tenants.wizard.steps.business.desc')],
                    2 => ['title' => __('tenants.wizard.steps.owner.title'), 'desc' => __('tenants.wizard.steps.owner.desc')],
                    3 => ['title' => __('tenants.wizard.steps.plan.title'), 'desc' => __('tenants.wizard.steps.plan.desc')],
                    4 => ['title' => __('tenants.wizard.steps.review.title'), 'desc' => __('tenants.wizard.steps.review.desc')],
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

        <form method="POST" action="{{ route('admin.tenants.store') }}" id="tenant-wizard" class="app-panel rounded-[2rem] border p-6">
            @csrf

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <input type="hidden" id="current-step" value="1">

            <section class="wizard-panel space-y-6" data-step-panel="1">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.36em] text-[var(--app-info)]">{{ __('tenants.wizard.step_of', ['current' => 1, 'total' => 4]) }}</p>
                    <h3 class="mt-3 text-2xl font-semibold">{{ __('tenants.wizard.steps.business.title') }}</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.gym_name') }}</label>
                        <input name="gym_name" value="{{ old('gym_name') }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.business_type') }}</label>
                        <select name="business_type" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                            <option value="">Select type</option>
                            @foreach (['Gym', 'Yoga', 'Turf'] as $type)
                                <option value="{{ $type }}" @selected(old('business_type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.city') }}</label>
                        <input name="city" value="{{ old('city') }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.state') }}</label>
                        <input name="state" value="{{ old('state') }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.address') }}</label>
                        <textarea name="address" rows="4" class="w-full rounded-2xl border px-4 py-3 outline-none" required>{{ old('address') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.gst_number') }}</label>
                        <input name="gst_number" value="{{ old('gst_number') }}" class="w-full rounded-2xl border px-4 py-3 outline-none">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.phone') }}</label>
                        <input name="phone" value="{{ old('phone') }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                </div>
            </section>

            <section class="wizard-panel hidden space-y-6" data-step-panel="2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.36em] text-[var(--app-info)]">{{ __('tenants.wizard.step_of', ['current' => 2, 'total' => 4]) }}</p>
                    <h3 class="mt-3 text-2xl font-semibold">{{ __('tenants.wizard.steps.owner.title') }}</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.owner_name') }}</label>
                        <input name="owner_name" value="{{ old('owner_name') }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.owner_email') }}</label>
                        <input type="email" name="owner_email" value="{{ old('owner_email') }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.owner_password') }}</label>
                        <input type="password" name="owner_password" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.owner_password_confirmation') }}</label>
                        <input type="password" name="owner_password_confirmation" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                </div>
            </section>

            <section class="wizard-panel hidden space-y-6" data-step-panel="3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.36em] text-[var(--app-info)]">{{ __('tenants.wizard.step_of', ['current' => 3, 'total' => 4]) }}</p>
                    <h3 class="mt-3 text-2xl font-semibold">{{ __('tenants.wizard.steps.plan.title') }}</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.subdomain') }}</label>
                        <input name="subdomain" value="{{ old('subdomain') }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.domain_mode') }}</label>
                        <select name="domain_mode" id="domain_mode" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                            <option value="shared" @selected(old('domain_mode', 'shared') === 'shared')>{{ __('tenants.wizard.domain_modes.shared') }}</option>
                            <option value="separate" @selected(old('domain_mode') === 'separate')>{{ __('tenants.wizard.domain_modes.separate') }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 hidden" data-separate-domain-field>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.custom_domain') }}</label>
                        <input name="custom_domain" value="{{ old('custom_domain') }}" placeholder="gym.example.com" class="w-full rounded-2xl border px-4 py-3 outline-none">
                    </div>
                    <div class="hidden" data-separate-domain-field>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.database_mode') }}</label>
                        <select name="database_mode" id="database_mode" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                            <option value="shared" @selected(old('database_mode', 'shared') === 'shared')>{{ __('tenants.wizard.database_modes.shared') }}</option>
                            <option value="separate" @selected(old('database_mode') === 'separate')>{{ __('tenants.wizard.database_modes.separate') }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.plan') }}</label>
                        <select name="plan_id" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                            <option value="">Select plan</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected((string) old('plan_id') === (string) $plan->id)>
                                    {{ $plan->name }} · Rs. {{ number_format($plan->price_paise / 100, 2) }} / {{ $plan->billing_cycle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.trial_end_date') }}</label>
                        <input type="date" name="trial_end_date" value="{{ old('trial_end_date') }}" class="w-full rounded-2xl border px-4 py-3 outline-none">
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-3 text-sm">
                            <input type="checkbox" name="trial_enabled" value="1" @checked(old('trial_enabled')) class="h-4 w-4 rounded border-white/10 text-orange-500">
                            {{ __('tenants.wizard.trial_label') }}
                        </label>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.internal_notes') }}</label>
                        <textarea name="notes" rows="4" class="w-full rounded-2xl border px-4 py-3 outline-none">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </section>

            <section class="wizard-panel hidden space-y-6" data-step-panel="4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.36em] text-[var(--app-info)]">{{ __('tenants.wizard.step_of', ['current' => 4, 'total' => 4]) }}</p>
                    <h3 class="mt-3 text-2xl font-semibold">{{ __('tenants.wizard.steps.review.title') }}</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-2" id="review-grid">
                    @foreach ([
                        'gym_name' => 'Gym name',
                        'business_type' => 'Business type',
                        'city' => 'City',
                        'state' => 'State',
                        'owner_name' => 'Owner name',
                        'owner_email' => 'Owner email',
                        'subdomain' => 'Subdomain',
                        'domain_mode' => 'Domain mode',
                        'custom_domain' => 'Separate domain',
                        'database_mode' => 'Database mode',
                        'plan_id' => 'Plan',
                        'trial_end_date' => 'Trial end date',
                    ] as $field => $label)
                        <div class="app-panel-strong rounded-2xl border p-4">
                            <p class="app-muted text-xs uppercase tracking-[0.24em]">{{ $label }}</p>
                            <p class="review-value mt-2 text-sm font-semibold" data-review="{{ $field }}">-</p>
                        </div>
                    @endforeach
                    <div class="app-panel-strong rounded-2xl border p-4 md:col-span-2">
                        <p class="app-muted text-xs uppercase tracking-[0.24em]">Address</p>
                        <p class="review-value mt-2 text-sm font-semibold" data-review="address">-</p>
                    </div>
                    <div class="app-panel-strong rounded-2xl border p-4 md:col-span-2">
                        <p class="app-muted text-xs uppercase tracking-[0.24em]">Notes</p>
                        <p class="review-value mt-2 text-sm font-semibold" data-review="notes">-</p>
                    </div>
                </div>
            </section>

            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-white/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <button
                    type="button"
                    id="wizard-prev"
                    class="app-panel-strong rounded-2xl border px-5 py-3 text-sm font-semibold transition hover:opacity-90"
                >
                    {{ __('common.back') }}
                </button>

                <div class="flex gap-3">
                    <button
                        type="button"
                        id="wizard-next"
                        class="rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-orange-400"
                    >
                        {{ __('common.next') }}
                    </button>
                    <button
                        type="submit"
                        id="wizard-submit"
                        class="hidden rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-orange-400"
                    >
                        {{ __('tenants.wizard.create_tenant') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        (() => {
            const form = document.getElementById('tenant-wizard');
            if (!form) return;

            const panels = Array.from(form.querySelectorAll('[data-step-panel]'));
            const stepButtons = Array.from(document.querySelectorAll('[data-step-jump]'));
            const currentStepInput = document.getElementById('current-step');
            const nextButton = document.getElementById('wizard-next');
            const prevButton = document.getElementById('wizard-prev');
            const submitButton = document.getElementById('wizard-submit');
            const domainModeSelect = document.getElementById('domain_mode');
            const databaseModeSelect = document.getElementById('database_mode');
            const sharedDomainHost = @json(parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'gymos.in');
            const totalSteps = panels.length;

            const getStep = () => Number(currentStepInput.value || 1);

            const getFieldsForStep = (step) => {
                const panel = panels.find((item) => Number(item.dataset.stepPanel) === step);
                return panel ? Array.from(panel.querySelectorAll('input, select, textarea')) : [];
            };

            const validateStep = (step) => {
                const fields = getFieldsForStep(step).filter((field) => !['hidden', 'button', 'submit'].includes(field.type));

                for (const field of fields) {
                    if (field.hasAttribute('required') && !field.value.trim()) {
                        field.reportValidity();
                        field.focus();
                        return false;
                    }

                    if (field.type === 'email' && field.value && !field.checkValidity()) {
                        field.reportValidity();
                        field.focus();
                        return false;
                    }
                }

                return true;
            };

            const refreshReview = () => {
                const formData = new FormData(form);
                const planSelect = form.querySelector('[name="plan_id"]');
                const domainModeLabels = {
                    shared: @json(__('tenants.wizard.domain_modes.shared')),
                    separate: @json(__('tenants.wizard.domain_modes.separate')),
                };
                const databaseModeLabels = {
                    shared: @json(__('tenants.wizard.database_modes.shared')),
                    separate: @json(__('tenants.wizard.database_modes.separate')),
                };

                document.querySelectorAll('[data-review]').forEach((target) => {
                    const key = target.dataset.review;
                    let value = formData.get(key) || '-';

                    if (key === 'plan_id' && planSelect) {
                        value = planSelect.selectedOptions[0]?.textContent?.trim() || '-';
                    }

                    if (key === 'subdomain' && value !== '-') {
                        value = `${value}.${sharedDomainHost}`;
                    }

                    if (key === 'domain_mode') {
                        value = domainModeLabels[value] || '-';
                    }

                    if (key === 'database_mode') {
                        value = databaseModeLabels[value] || '-';
                    }

                    if (key === 'custom_domain' && formData.get('domain_mode') !== 'separate') {
                        value = '-';
                    }

                    if (key === 'notes' && !String(value).trim()) {
                        value = @json(__('tenants.wizard.review_empty'));
                    }

                    target.textContent = value;
                });
            };

            const ensureDefaults = () => {
            };

            const syncDomainFields = () => {
                const separateDomain = domainModeSelect?.value === 'separate';

                form.querySelectorAll('[data-separate-domain-field]').forEach((field) => {
                    field.classList.toggle('hidden', !separateDomain);
                });

                const customDomainInput = form.querySelector('[name="custom_domain"]');
                if (customDomainInput) {
                    customDomainInput.toggleAttribute('required', separateDomain);
                }

                if (databaseModeSelect && !separateDomain) {
                    databaseModeSelect.value = 'shared';
                }
            };

            const renderStep = (step) => {
                panels.forEach((panel) => {
                    panel.classList.toggle('hidden', Number(panel.dataset.stepPanel) !== step);
                });

                stepButtons.forEach((button, index) => {
                    const active = index + 1 === step;
                    button.classList.toggle('bg-[var(--app-brand)]', active);
                    button.classList.toggle('text-slate-950', active);
                    button.classList.toggle('app-panel-strong', !active);
                });

                prevButton.classList.toggle('invisible', step === 1);
                nextButton.classList.toggle('hidden', step === totalSteps);
                submitButton.classList.toggle('hidden', step !== totalSteps);

                if (step === totalSteps) {
                    refreshReview();
                }
            };

            const setStep = (step) => {
                currentStepInput.value = step;
                renderStep(step);
            };

            nextButton.addEventListener('click', () => {
                const step = getStep();
                if (!validateStep(step)) return;
                setStep(Math.min(step + 1, totalSteps));
            });

            prevButton.addEventListener('click', () => {
                const step = getStep();
                setStep(Math.max(step - 1, 1));
            });

            stepButtons.forEach((button, index) => {
                button.addEventListener('click', () => {
                    const targetStep = index + 1;
                    const current = getStep();

                    if (targetStep > current && !validateStep(current)) return;
                    setStep(targetStep);
                });
            });

            domainModeSelect?.addEventListener('change', syncDomainFields);
            ensureDefaults();
            syncDomainFields();
            renderStep(getStep());
        })();
    </script>
</x-layouts.admin>
