<x-layouts.admin
    title="Edit Tenant"
    eyebrow="Tenant"
    heading="Edit {{ $tenant->gym_name }}"
    subheading="Update tenant information, owner details, routing, language, and current status."
>
    <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" class="app-panel rounded-[2rem] border p-6">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium">Gym name</label>
                <input name="gym_name" value="{{ old('gym_name', $tenant->gym_name) }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Business type</label>
                <select name="business_type" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    @foreach ($businessTypes as $type)
                        <option value="{{ $type }}" @selected(old('business_type', $tenant->business_type) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Owner name</label>
                <input name="owner_name" value="{{ old('owner_name', $tenant->owner_name) }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Owner email</label>
                <input type="email" name="owner_email" value="{{ old('owner_email', $tenant->owner_email) }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Owner password</label>
                <input type="password" name="owner_password" class="w-full rounded-2xl border px-4 py-3 outline-none">
                <p class="mt-2 text-xs text-slate-400">Leave blank to keep the current password.</p>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Confirm owner password</label>
                <input type="password" name="owner_password_confirmation" class="w-full rounded-2xl border px-4 py-3 outline-none">
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Phone</label>
                <input name="phone" value="{{ old('phone', $tenant->phone) }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Status</label>
                <select name="status" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(old('status', $tenant->status) === $status)>{{ ucfirst($status) }}</option>
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
            <div>
                <label class="mb-2 block text-sm font-medium">Subdomain</label>
                <input name="subdomain" value="{{ old('subdomain', $tenant->subdomain) }}" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Domain mode</label>
                <select name="domain_mode" id="domain_mode" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    <option value="shared" @selected(old('domain_mode', $tenant->domain_mode) === 'shared')>Shared domain</option>
                    <option value="separate" @selected(old('domain_mode', $tenant->domain_mode) === 'separate')>Separate domain</option>
                </select>
            </div>
            <div class="md:col-span-2 {{ old('domain_mode', $tenant->domain_mode) === 'separate' ? '' : 'hidden' }}" data-separate-domain-field>
                <label class="mb-2 block text-sm font-medium">Separate domain</label>
                <input name="custom_domain" value="{{ old('custom_domain', $tenant->custom_domain) }}" class="w-full rounded-2xl border px-4 py-3 outline-none">
            </div>
            <div class="{{ old('domain_mode', $tenant->domain_mode) === 'separate' ? '' : 'hidden' }}" data-separate-domain-field>
                <label class="mb-2 block text-sm font-medium">Database mode</label>
                <select name="database_mode" id="database_mode" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    <option value="shared" @selected(old('database_mode', $tenant->database_mode) === 'shared')>Main database</option>
                    <option value="separate" @selected(old('database_mode', $tenant->database_mode) === 'separate')>Separate database</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Default language</label>
                <select name="default_language" class="w-full rounded-2xl border px-4 py-3 outline-none" required>
                    @foreach ($languages as $language)
                        <option value="{{ $language->locale_code }}" @selected(old('default_language', $tenant->default_language) === $language->locale_code)>{{ $language->display_name }} ({{ $language->locale_code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">GST number</label>
                <input name="gst_number" value="{{ old('gst_number', $tenant->gst_number) }}" class="w-full rounded-2xl border px-4 py-3 outline-none">
            </div>
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium">Address</label>
                <textarea name="address" rows="3" class="w-full rounded-2xl border px-4 py-3 outline-none" required>{{ old('address', $tenant->address) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium">Notes</label>
                <textarea name="notes" rows="4" class="w-full rounded-2xl border px-4 py-3 outline-none">{{ old('notes', $tenant->notes) }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400">Save changes</button>
            <a href="{{ route('admin.tenants.show', $tenant) }}" class="app-panel-strong rounded-2xl border px-5 py-3 text-sm font-semibold hover:opacity-90">Cancel</a>
        </div>
    </form>

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
