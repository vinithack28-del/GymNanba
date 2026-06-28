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
                        <input name="phone" value="{{ old('phone') }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required minlength="10">
                    </div>
                </div>
            </section>

            <section class="wizard-panel hidden space-y-6" data-step-panel="2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.36em] text-[var(--app-info)]">{{ __('tenants.wizard.step_of', ['current' => 2, 'total' => 4]) }}</p>
                    <h3 class="mt-3 text-2xl font-semibold">{{ __('tenants.wizard.steps.owner.title') }}</h3>
                    <p class="mt-1 text-sm" style="color:var(--app-text-muted)">Password is auto-generated: first 4 chars of email + @ + last 4 digits of phone.</p>
                </div>

                <div id="owners-container" class="space-y-4">
                    {{-- Owner row template (index 0 = primary, cannot be removed) --}}
                    <div class="owner-row" data-owner-index="0">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem">
                            <span style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--app-text-muted)">Primary Owner</span>
                        </div>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-sm font-medium">Name</label>
                                <input name="owners[0][name]" value="{{ old('owners.0.name') }}"
                                       class="w-full rounded-2xl border px-4 py-3 outline-none owner-name" required
                                       placeholder="Owner name">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium">Email</label>
                                <input type="email" name="owners[0][email]" value="{{ old('owners.0.email') }}"
                                       class="w-full rounded-2xl border px-4 py-3 outline-none owner-email" required
                                       placeholder="owner@example.com"
                                       oninput="updateOwnerPassword(0)">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium">Phone</label>
                                <input name="owners[0][phone]" value="{{ old('owners.0.phone') }}"
                                       class="w-full rounded-2xl border px-4 py-3 outline-none owner-phone" required
                                       placeholder="10-digit mobile" minlength="10"
                                       oninput="updateOwnerPassword(0)">
                            </div>
                        </div>
                        <div id="owner-pwd-preview-0" style="margin-top:.6rem;display:none;padding:.55rem .9rem;border-radius:.9rem;background:color-mix(in srgb,var(--app-brand) 8%,transparent);border:1px solid color-mix(in srgb,var(--app-brand) 20%,transparent);font-size:.82rem;display:flex;align-items:center;gap:.75rem">
                            <span style="color:var(--app-text-muted)">Login password:</span>
                            <code id="owner-pwd-text-0" style="font-weight:700;color:var(--app-text);letter-spacing:.05em"></code>
                            <button type="button" onclick="copyOwnerPwd(0)" style="margin-left:auto;font-size:.75rem;padding:.2rem .6rem;border-radius:.5rem;border:1px solid var(--app-border);background:var(--app-panel);cursor:pointer;color:var(--app-text-muted)">Copy</button>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="addOwnerRow()"
                    style="width:100%;padding:.7rem;border:1px dashed var(--app-border);background:transparent;color:var(--app-text-muted);border-radius:1.25rem;cursor:pointer;font-size:.85rem;font-weight:600">
                    + Add Another Owner
                </button>
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
                        <select name="plan_id" id="create-plan-select" class="w-full rounded-2xl border px-4 py-3 outline-none" required onchange="onCreatePlanChange(this)">
                            <option value="">Select plan</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}"
                                        data-price="{{ $plan->price_paise }}"
                                        data-is-trial="{{ $plan->is_trial ? '1' : '0' }}"
                                        data-trial-days="{{ $plan->trial_days ?? 0 }}"
                                        @selected((string) old('plan_id') === (string) $plan->id)>
                                    @if ($plan->is_trial)
                                        {{ $plan->name }} · Trial ({{ $plan->trial_days }} days)
                                    @else
                                        {{ $plan->name }} · Rs. {{ number_format($plan->price_paise / 100, 2) }} / {{ $plan->billing_cycle }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Trial end date: shown only for trial plans (auto) or when checkbox is checked --}}
                    <div id="create-trial-date-wrap" class="hidden">
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.trial_end_date') }}</label>
                        <input type="date" name="trial_end_date" id="create-trial-end-date" value="{{ old('trial_end_date') }}" class="w-full rounded-2xl border px-4 py-3 outline-none">
                        <p id="create-trial-auto-note" class="hidden mt-1 text-xs" style="color:var(--app-text-muted)"></p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">{{ __('tenants.wizard.fields.internal_notes') }}</label>
                        <textarea name="notes" rows="3" class="w-full rounded-2xl border px-4 py-3 outline-none">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Payment section — shown only for paid plans --}}
                    <div class="md:col-span-2 hidden" id="create-payment-section">
                        <div style="border:1px solid var(--app-border);border-radius:1.25rem;padding:1.25rem;background:var(--app-panel-strong)">
                            <p style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--app-text-muted);margin-bottom:1rem">Payment (Optional)</p>

                            {{-- Price bar --}}
                            <div id="create-price-bar" style="display:flex;justify-content:space-between;font-size:.82rem;color:var(--app-text-muted);margin-bottom:1rem;gap:1rem;flex-wrap:wrap">
                                <span>Plan price: <strong id="create-plan-price" style="color:var(--app-text)">—</strong></span>
                                <span>Paying now: <strong id="create-paying-now" style="color:var(--app-text)">Rs. 0</strong></span>
                                <span>Balance: <strong id="create-balance" style="color:#b45309">—</strong></span>
                            </div>

                            {{-- Split rows --}}
                            <div id="create-splits-container" style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:.5rem">
                                <div class="create-split-row" style="display:grid;grid-template-columns:1.1fr 1fr 1.2fr auto;gap:.5rem;align-items:end">
                                    <div>
                                        <label style="display:block;font-size:.72rem;font-weight:600;color:var(--app-text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem">Method</label>
                                        <select name="payment_splits[0][method]" class="w-full rounded-2xl border px-3 py-2.5 outline-none create-split-method" style="font-size:.88rem">
                                            @foreach (['Cash', 'Bank transfer', 'UPI', 'Cheque'] as $m)<option>{{ $m }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label style="display:block;font-size:.72rem;font-weight:600;color:var(--app-text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem">Amount (₹)</label>
                                        <input type="number" step="0.01" min="0.01" name="payment_splits[0][amount]" class="w-full rounded-2xl border px-3 py-2.5 outline-none create-split-amount" placeholder="0.00" oninput="updateCreateBalance()">
                                    </div>
                                    <div>
                                        <label style="display:block;font-size:.72rem;font-weight:600;color:var(--app-text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem">Reference</label>
                                        <input name="payment_splits[0][reference]" class="w-full rounded-2xl border px-3 py-2.5 outline-none create-split-ref" placeholder="UPI ID / cheque no." style="font-size:.88rem">
                                    </div>
                                    <div style="padding-bottom:.05rem">
                                        <button type="button" onclick="removeCreateSplit(this)"
                                            style="height:2.6rem;width:2.6rem;border-radius:.9rem;border:1px solid var(--app-border);background:var(--app-panel);color:#dc2626;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center"
                                            title="Remove">✕</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" onclick="addCreateSplit()"
                                style="width:100%;padding:.55rem;border:1px dashed var(--app-border);background:transparent;color:var(--app-text-muted);border-radius:.9rem;cursor:pointer;font-size:.82rem;font-weight:600;margin-bottom:.85rem">
                                + Add Payment Method
                            </button>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                                <div>
                                    <label style="display:block;font-size:.75rem;font-weight:600;color:var(--app-text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.35rem">Payment Date</label>
                                    <input type="date" name="payment_paid_at" value="{{ now()->toDateString() }}" class="w-full rounded-2xl border px-3 py-2.5 outline-none" style="font-size:.88rem">
                                </div>
                                <div>
                                    <label style="display:block;font-size:.75rem;font-weight:600;color:var(--app-text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.35rem">Payment Notes</label>
                                    <input name="payment_notes" value="{{ old('payment_notes') }}" class="w-full rounded-2xl border px-3 py-2.5 outline-none" placeholder="Optional" style="font-size:.88rem">
                                </div>
                            </div>

                            <div id="create-payment-status" style="display:none;margin-top:.75rem;font-size:.8rem;padding:.5rem .8rem;border-radius:.75rem"></div>
                        </div>
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
                        'gym_name'      => 'Gym name',
                        'business_type' => 'Business type',
                        'city'          => 'City',
                        'state'         => 'State',
                        'subdomain'     => 'Subdomain',
                        'domain_mode'   => 'Domain mode',
                        'plan_id'       => 'Plan',
                        'trial_end_date'=> 'Trial end date',
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
                    <div class="app-panel-strong rounded-2xl border p-4 md:col-span-2" id="review-owners-card">
                        <p class="app-muted mb-2 text-xs uppercase tracking-[0.24em]">Owner Accounts</p>
                        <div id="review-owners-list" class="space-y-1 text-sm font-semibold">-</div>
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

            const getFieldLabel = (field) => {
                const lbl = field.closest('div')?.querySelector('label');
                return lbl ? lbl.textContent.trim().replace(/\s*\(.*\)$/, '').trim() : (field.placeholder || field.name || 'Field');
            };

            const getFieldError = (field) => {
                if (field.hasAttribute('required') && !field.value.trim())
                    return `${getFieldLabel(field)} is required`;
                if (field.value && !field.checkValidity()) {
                    const lbl = getFieldLabel(field);
                    const isPhone = field.name?.includes('phone') || field.classList.contains('owner-phone');
                    if (field.validity.tooShort)
                        return isPhone
                            ? `${lbl} must be at least ${field.minLength} digits`
                            : `${lbl} must be at least ${field.minLength} characters`;
                    if (field.validity.typeMismatch)
                        return `${lbl} must be a valid ${field.type}`;
                    return `${lbl} is invalid`;
                }
                return null;
            };

            const showStepErrors = (step, errors) => {
                const panel = panels.find(p => Number(p.dataset.stepPanel) === step);
                if (!panel) return;
                let box = panel.querySelector('.wizard-error-box');
                if (!box) {
                    box = document.createElement('div');
                    box.className = 'wizard-error-box';
                    box.style.cssText = 'border-radius:1.1rem;padding:.85rem 1.1rem;margin-bottom:1rem;background:color-mix(in srgb,#ef4444 10%,transparent);border:1px solid color-mix(in srgb,#ef4444 30%,transparent);color:#dc2626;font-size:.85rem';
                    panel.prepend(box);
                }
                box.innerHTML = errors.map(e => `<div style="display:flex;gap:.5rem;align-items:baseline"><span>•</span><span>${e}</span></div>`).join('');
                box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            };

            const clearStepErrors = (step) => {
                const panel = panels.find(p => Number(p.dataset.stepPanel) === step);
                panel?.querySelector('.wizard-error-box')?.remove();
                panel?.querySelectorAll('[data-invalid]').forEach(f => {
                    f.style.borderColor = '';
                    delete f.dataset.invalid;
                });
            };

            const validateStep = (step) => {
                const fields = getFieldsForStep(step).filter((field) => !['hidden', 'button', 'submit', 'checkbox'].includes(field.type));
                clearStepErrors(step);
                const errors = [];

                for (const field of fields) {
                    const err = getFieldError(field);
                    if (err) {
                        errors.push(err);
                        field.style.borderColor = '#ef4444';
                        field.dataset.invalid = '1';
                        field.addEventListener('input', () => {
                            field.style.borderColor = '';
                            delete field.dataset.invalid;
                            const box = panels.find(p => Number(p.dataset.stepPanel) === step)?.querySelector('.wizard-error-box');
                            if (box) box.remove();
                        }, { once: true });
                    }
                }

                if (errors.length) {
                    showStepErrors(step, errors);
                    return false;
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

                // Refresh owners summary
                const ownersList = document.getElementById('review-owners-list');
                if (ownersList) {
                    const rows = document.querySelectorAll('.owner-row');
                    ownersList.innerHTML = Array.from(rows).map((row, i) => {
                        const name  = row.querySelector('.owner-name')?.value || '—';
                        const email = row.querySelector('.owner-email')?.value || '—';
                        const phone = row.querySelector('.owner-phone')?.value || '—';
                        const pwd   = document.getElementById(`owner-pwd-text-${i}`)?.textContent || '—';
                        return `<div style="padding:.35rem 0;${i>0?'border-top:1px solid var(--app-border);margin-top:.35rem':''}">
                            <span style="font-weight:700">${name}</span>
                            <span style="color:var(--app-text-muted);font-weight:400"> · ${email} · ${phone}</span>
                            <span style="margin-left:.5rem;font-size:.78rem;color:var(--app-text-muted)">pwd: <code style="color:var(--app-text)">${pwd}</code></span>
                        </div>`;
                    }).join('');
                }
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

    <script>
    // ── Create-tenant payment section ─────────────────────────────────────────

    function setPaymentSectionDisabled(disabled) {
        document.querySelectorAll('#create-payment-section input, #create-payment-section select').forEach(el => {
            el.disabled = disabled;
        });
    }

    function onCreatePlanChange(sel) {
        const opt       = sel.options[sel.selectedIndex];
        const price     = parseInt(opt?.dataset.price || 0);
        const isTrial   = opt?.dataset.isTrial === '1';
        const trialDays = parseInt(opt?.dataset.trialDays || 0);

        const section   = document.getElementById('create-payment-section');
        const dateWrap  = document.getElementById('create-trial-date-wrap');
        const dateInput = document.getElementById('create-trial-end-date');
        const autoNote  = document.getElementById('create-trial-auto-note');

        if (!opt.value) {
            dateWrap.classList.add('hidden');
            section.classList.add('hidden');
            return;
        }

        if (isTrial && trialDays > 0) {
            // Trial plan — auto-calculate end date, hide payment
            const end  = new Date();
            end.setDate(end.getDate() + trialDays);
            const yyyy = end.getFullYear();
            const mm   = String(end.getMonth() + 1).padStart(2, '0');
            const dd   = String(end.getDate()).padStart(2, '0');
            dateInput.value         = `${yyyy}-${mm}-${dd}`;
            dateInput.readOnly      = true;
            dateInput.style.opacity = '0.7';
            autoNote.textContent    = `Auto-calculated: ${trialDays} days from today (${dd}/${mm}/${yyyy})`;
            autoNote.classList.remove('hidden');
            dateWrap.classList.remove('hidden');
            section.classList.add('hidden');
            setPaymentSectionDisabled(true);
        } else {
            // Paid plan — hide trial date, show payment if price > 0
            dateInput.readOnly      = false;
            dateInput.style.opacity = '';
            autoNote.classList.add('hidden');
            dateWrap.classList.add('hidden');
            dateInput.value = '';

            if (price > 0) {
                section.classList.remove('hidden');
                setPaymentSectionDisabled(false);
                document.getElementById('create-plan-price').textContent = `Rs. ${(price / 100).toFixed(0)}`;
                updateCreateBalance();
            } else {
                section.classList.add('hidden');
                setPaymentSectionDisabled(true);
            }
        }
    }

    function updateCreateBalance() {
        const sel   = document.getElementById('create-plan-select');
        const price = parseInt(sel?.options[sel.selectedIndex]?.dataset.price || 0);
        if (!price) return;

        let totalPaise = 0;
        document.querySelectorAll('#create-splits-container .create-split-amount').forEach(inp => {
            totalPaise += Math.round(parseFloat(inp.value || 0) * 100);
        });

        document.getElementById('create-paying-now').textContent = `Rs. ${(totalPaise / 100).toFixed(0)}`;
        const balance = Math.max(0, price - totalPaise);
        document.getElementById('create-balance').textContent = `Rs. ${(balance / 100).toFixed(0)}`;

        const statusEl = document.getElementById('create-payment-status');
        if (totalPaise === 0) {
            statusEl.style.display = 'none';
        } else if (balance === 0) {
            statusEl.style.display = 'block';
            statusEl.style.background = 'color-mix(in srgb,#22c55e 12%,transparent)';
            statusEl.style.color = '#16a34a';
            statusEl.textContent = '✓ Full payment — subscription will be activated immediately.';
        } else {
            statusEl.style.display = 'block';
            statusEl.style.background = 'color-mix(in srgb,#f59e0b 12%,transparent)';
            statusEl.style.color = '#b45309';
            statusEl.textContent = `⚠ Part payment — Rs. ${(balance / 100).toFixed(0)} balance remaining.`;
        }
    }

    function addCreateSplit() {
        const container = document.getElementById('create-splits-container');
        const rows = container.querySelectorAll('.create-split-row');
        const idx  = rows.length;
        const tpl  = rows[0].cloneNode(true);

        tpl.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${idx}]`);
            if (el.tagName === 'INPUT') el.value = '';
        });
        container.appendChild(tpl);
        renumberCreateSplits();
    }

    function removeCreateSplit(btn) {
        const container = document.getElementById('create-splits-container');
        if (container.querySelectorAll('.create-split-row').length <= 1) return;
        btn.closest('.create-split-row').remove();
        renumberCreateSplits();
        updateCreateBalance();
    }

    function renumberCreateSplits() {
        document.getElementById('create-splits-container').querySelectorAll('.create-split-row').forEach((row, i) => {
            row.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
            });
        });
    }

    // Restore state on validation error (old() plan pre-selected)
    document.addEventListener('DOMContentLoaded', () => {
        const sel = document.getElementById('create-plan-select');
        if (sel && sel.value) onCreatePlanChange(sel);
        // Restore password previews for any pre-filled old() owners
        document.querySelectorAll('.owner-row').forEach((row, i) => {
            const email = row.querySelector('.owner-email')?.value;
            const phone = row.querySelector('.owner-phone')?.value;
            if (email && phone) updateOwnerPassword(i);
        });
    });

    // ── Owner rows ────────────────────────────────────────────────────────────

    function generateOwnerPassword(email, phone) {
        const local     = (email.split('@')[0] || '').toLowerCase();
        const emailPart = local.substring(0, 4);
        const digits    = phone.replace(/\D/g, '');
        const phonePart = digits.slice(-4);
        return emailPart && phonePart ? `${emailPart}@${phonePart}` : '';
    }

    function updateOwnerPassword(index) {
        const row     = document.querySelector(`.owner-row[data-owner-index="${index}"]`);
        if (!row) return;
        const email   = row.querySelector('.owner-email')?.value || '';
        const phone   = row.querySelector('.owner-phone')?.value || '';
        const pwd     = generateOwnerPassword(email, phone);
        const preview = document.getElementById(`owner-pwd-preview-${index}`);
        const text    = document.getElementById(`owner-pwd-text-${index}`);
        if (preview && text) {
            text.textContent = pwd || '—';
            preview.style.display = pwd ? 'flex' : 'none';
        }
    }

    function copyOwnerPwd(index) {
        const pwd = document.getElementById(`owner-pwd-text-${index}`)?.textContent;
        if (pwd && navigator.clipboard) navigator.clipboard.writeText(pwd);
    }

    function addOwnerRow() {
        const container = document.getElementById('owners-container');
        const index     = container.querySelectorAll('.owner-row').length;

        const div = document.createElement('div');
        div.className = 'owner-row';
        div.setAttribute('data-owner-index', index);
        div.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem">
                <span style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--app-text-muted)">Additional Owner ${index}</span>
                <button type="button" onclick="removeOwnerRow(this)"
                    style="font-size:.8rem;padding:.2rem .65rem;border-radius:.6rem;border:1px solid var(--app-border);background:var(--app-panel-strong);color:#dc2626;cursor:pointer">
                    Remove
                </button>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium">Name</label>
                    <input name="owners[${index}][name]" class="w-full rounded-2xl border px-4 py-3 outline-none owner-name" required placeholder="Owner name">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">Email</label>
                    <input type="email" name="owners[${index}][email]" class="w-full rounded-2xl border px-4 py-3 outline-none owner-email" required placeholder="owner@example.com"
                           oninput="updateOwnerPassword(${index})">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">Phone</label>
                    <input name="owners[${index}][phone]" class="w-full rounded-2xl border px-4 py-3 outline-none owner-phone" required placeholder="10-digit mobile" minlength="10"
                           oninput="updateOwnerPassword(${index})">
                </div>
            </div>
            <div id="owner-pwd-preview-${index}" style="display:none;margin-top:.6rem;padding:.55rem .9rem;border-radius:.9rem;background:color-mix(in srgb,var(--app-brand) 8%,transparent);border:1px solid color-mix(in srgb,var(--app-brand) 20%,transparent);font-size:.82rem;align-items:center;gap:.75rem">
                <span style="color:var(--app-text-muted)">Login password:</span>
                <code id="owner-pwd-text-${index}" style="font-weight:700;color:var(--app-text);letter-spacing:.05em"></code>
                <button type="button" onclick="copyOwnerPwd(${index})" style="margin-left:auto;font-size:.75rem;padding:.2rem .6rem;border-radius:.5rem;border:1px solid var(--app-border);background:var(--app-panel);cursor:pointer;color:var(--app-text-muted)">Copy</button>
            </div>`;
        container.appendChild(div);
    }

    function removeOwnerRow(btn) {
        btn.closest('.owner-row').remove();
        // Re-number remaining rows
        document.querySelectorAll('.owner-row').forEach((row, i) => {
            row.setAttribute('data-owner-index', i);
            row.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/owners\[\d+\]/, `owners[${i}]`);
            });
            ['owner-pwd-preview-', 'owner-pwd-text-'].forEach(prefix => {
                const el = row.querySelector(`[id^="${prefix}"]`);
                if (el) el.id = `${prefix}${i}`;
            });
        });
    }
    </script>
</x-layouts.admin>
