<x-layouts.admin
    title="Edit Tenant"
    eyebrow="Tenant"
    heading="Edit {{ $tenant->gym_name }}"
    subheading="Update tenant information, owner details, routing, language, and current status."
>
    <div class="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">

        {{-- Sidebar navigation --}}
        <aside class="app-panel rounded-[2rem] border p-5">
            <div class="space-y-4">
                @foreach ([
                    1 => ['title' => 'Business Info',        'desc'  => 'Gym name, type, location, and GST'],
                    2 => ['title' => 'Owner Details',        'desc'  => 'Owner contact and login credentials'],
                    3 => ['title' => 'Routing & Technical',  'desc'  => 'Subdomain, domain mode, and language'],
                    4 => ['title' => 'Status & Notes',       'desc'  => 'Tenant status and internal notes'],
                ] as $num => $item)
                    <a
                        href="#section-{{ $num }}"
                        class="app-panel-strong flex w-full items-start gap-4 rounded-[1.5rem] border px-4 py-4 text-left transition hover:opacity-90"
                    >
                        <span class="app-brand-soft app-brand-text inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold">
                            {{ str_pad((string) $num, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span>
                            <span class="block text-sm font-semibold">{{ $item['title'] }}</span>
                            <span class="app-muted mt-1 block text-xs">{{ $item['desc'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </aside>

        {{-- Main form --}}
        <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" class="app-panel rounded-[2rem] border p-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Section 1: Business Info --}}
            <section id="section-1" class="space-y-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.36em] text-[var(--app-info)]">Section 1 of 4</p>
                    <h3 class="mt-3 text-2xl font-semibold">Business Info</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Gym Name</label>
                        <input name="gym_name" value="{{ old('gym_name', $tenant->gym_name) }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Business Type</label>
                        <select name="business_type" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                            @foreach ($businessTypes as $type)
                                <option value="{{ $type }}" @selected(old('business_type', $tenant->business_type) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">City</label>
                        <input name="city" value="{{ old('city', $tenant->city) }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">State</label>
                        <input name="state" value="{{ old('state', $tenant->state) }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Address</label>
                        <textarea name="address" rows="3" class="w-full rounded-2xl border px-4 py-3 outline-none" required>{{ old('address', $tenant->address) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">GST Number <span class="app-muted font-normal">(optional)</span></label>
                        <input name="gst_number" value="{{ old('gst_number', $tenant->gst_number) }}" class="w-full rounded-2xl border px-4 py-3 outline-none">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Phone</label>
                        <input name="phone" value="{{ old('phone', $tenant->phone) }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                </div>
            </section>

            <hr class="my-8 border-[var(--app-border)]">

            {{-- Section 2: Owner Details --}}
            <section id="section-2" class="space-y-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.36em] text-[var(--app-info)]">Section 2 of 4</p>
                    <h3 class="mt-3 text-2xl font-semibold">Owner Details</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Owner Name</label>
                        <input name="owner_name" value="{{ old('owner_name', $tenant->owner_name) }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Owner Email</label>
                        <input type="email" name="owner_email" value="{{ old('owner_email', $tenant->owner_email) }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">New Password <span class="app-muted font-normal">(optional)</span></label>
                        <input type="password" name="owner_password" class="w-full rounded-2xl border px-4 py-3 outline-none" autocomplete="new-password">
                        <p class="mt-2 text-xs text-[var(--app-text-muted)]">Leave blank to keep the current password.</p>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Confirm New Password</label>
                        <input type="password" name="owner_password_confirmation" class="w-full rounded-2xl border px-4 py-3 outline-none" autocomplete="new-password">
                    </div>
                </div>
            </section>

            <hr class="my-8 border-[var(--app-border)]">

            {{-- Section 3: Routing & Technical --}}
            <section id="section-3" class="space-y-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.36em] text-[var(--app-info)]">Section 3 of 4</p>
                    <h3 class="mt-3 text-2xl font-semibold">Routing &amp; Technical</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Subdomain</label>
                        <input name="subdomain" value="{{ old('subdomain', $tenant->subdomain) }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Domain Mode</label>
                        <select name="domain_mode" id="domain_mode" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                            <option value="shared"   @selected(old('domain_mode', $tenant->domain_mode) === 'shared')>Shared domain</option>
                            <option value="separate" @selected(old('domain_mode', $tenant->domain_mode) === 'separate')>Separate domain</option>
                        </select>
                    </div>
                    <div
                        class="md:col-span-2 {{ old('domain_mode', $tenant->domain_mode) === 'separate' ? '' : 'hidden' }}"
                        data-separate-domain-field
                    >
                        <label class="mb-2 block text-sm font-medium">Custom Domain</label>
                        <input name="custom_domain" value="{{ old('custom_domain', $tenant->custom_domain) }}" placeholder="gym.example.com" class="w-full rounded-2xl border px-4 py-3 outline-none">
                    </div>
                    <div
                        class="{{ old('domain_mode', $tenant->domain_mode) === 'separate' ? '' : 'hidden' }}"
                        data-separate-domain-field
                    >
                        <label class="mb-2 block text-sm font-medium">Database Mode</label>
                        <select name="database_mode" id="database_mode" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                            <option value="shared"   @selected(old('database_mode', $tenant->database_mode) === 'shared')>Main database</option>
                            <option value="separate" @selected(old('database_mode', $tenant->database_mode) === 'separate')>Separate database</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">Default Language</label>
                        <select name="default_language" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                            @foreach ($languages as $language)
                                <option value="{{ $language->locale_code }}" @selected(old('default_language', $tenant->default_language) === $language->locale_code)>
                                    {{ $language->display_name }} ({{ $language->locale_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <hr class="my-8 border-[var(--app-border)]">

            {{-- Section 4: Status & Notes --}}
            <section id="section-4" class="space-y-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.36em] text-[var(--app-info)]">Section 4 of 4</p>
                    <h3 class="mt-3 text-2xl font-semibold">Status &amp; Notes</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Status</label>
                        <select name="status" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $tenant->status) === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Notes <span class="app-muted font-normal">(internal only)</span></label>
                        <textarea name="notes" rows="4" class="w-full rounded-2xl border px-4 py-3 outline-none">{{ old('notes', $tenant->notes) }}</textarea>
                    </div>
                </div>
            </section>

            {{-- Save bar --}}
            <div class="mt-8 flex gap-3 border-t border-[var(--app-border)] pt-6">
                <button type="submit" class="rounded-2xl bg-[var(--app-brand)] px-6 py-3 text-sm font-semibold text-slate-950 transition hover:opacity-90">
                    Save Changes
                </button>
                <a href="{{ route('admin.tenants.show', $tenant) }}" class="app-panel-strong rounded-2xl border px-6 py-3 text-sm font-semibold transition hover:opacity-90">
                    Cancel
                </a>
            </div>
        </form>

    </div>

    <script>
        (() => {
            const domainModeSelect = document.getElementById('domain_mode');
            const databaseModeSelect = document.getElementById('database_mode');

            const syncDomainFields = () => {
                const separateDomain = domainModeSelect?.value === 'separate';

                document.querySelectorAll('[data-separate-domain-field]').forEach((field) => {
                    field.classList.toggle('hidden', !separateDomain);
                });

                const customDomainInput = document.querySelector('[name="custom_domain"]');
                if (customDomainInput) {
                    customDomainInput.toggleAttribute('required', separateDomain);
                }

                if (databaseModeSelect && !separateDomain) {
                    databaseModeSelect.value = 'shared';
                }
            };

            domainModeSelect?.addEventListener('change', syncDomainFields);
            syncDomainFields();
        })();
    </script>
</x-layouts.admin>
