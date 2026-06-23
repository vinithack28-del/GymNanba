<x-layouts.admin
    title="Add Equipment"
    eyebrow="Operations"
    heading="Add Equipment"
    subheading="Capture equipment details, status, and purchase information."
>
    <x-slot:headerAction>
        <a href="{{ route('tenant.equipment.index') }}"
           class="inline-flex items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-medium transition hover:opacity-80"
           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text-muted)">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
            Back to Equipment
        </a>
    </x-slot:headerAction>

    <form method="POST" action="{{ route('tenant.equipment.store') }}" class="app-panel rounded-[2rem] border p-6">
        @csrf

        @if ($errors->any())
            <div class="mb-5 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="ef-label">Equipment Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="ef-input" placeholder="e.g. Treadmill Pro 3000" maxlength="150" required>
                @error('name') <p class="ef-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="ef-label">Type <span class="text-red-400">*</span></label>
                <select name="type" class="ef-input" required>
                    <option value="">Select type…</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type') <p class="ef-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="ef-label">Status</label>
                <select name="status" class="ef-input">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'operational') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <p class="ef-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="ef-label">Brand</label>
                <input type="text" name="brand" value="{{ old('brand') }}" class="ef-input" placeholder="Brand name" maxlength="100">
                @error('brand') <p class="ef-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="ef-label">Model</label>
                <input type="text" name="model" value="{{ old('model') }}" class="ef-input" placeholder="Model name" maxlength="100">
                @error('model') <p class="ef-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="ef-label">Purchase Date</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" max="{{ today()->toDateString() }}" class="ef-input">
                @error('purchase_date') <p class="ef-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="ef-label">Warranty Expiry</label>
                <input type="date" name="warranty_expiry" value="{{ old('warranty_expiry') }}" class="ef-input">
                @error('warranty_expiry') <p class="ef-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="ef-label">Purchase Price (₹)</label>
                <input type="number" name="purchase_price" value="{{ old('purchase_price', '0') }}" min="0" step="1" class="ef-input" placeholder="0">
                @error('purchase_price') <p class="ef-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="ef-label">Location</label>
                <input type="text" name="location" value="{{ old('location') }}" class="ef-input" placeholder="e.g. Cardio Zone, Floor 1" maxlength="200">
                @error('location') <p class="ef-error">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="ef-label">Notes</label>
                <textarea name="notes" rows="5" class="ef-input" placeholder="Additional details…" maxlength="1000">{{ old('notes') }}</textarea>
                @error('notes') <p class="ef-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button type="submit" class="ef-btn-primary">Add Equipment</button>
            <a href="{{ route('tenant.equipment.index') }}" class="ef-btn-ghost">Cancel</a>
        </div>
    </form>

    @push('styles')
        <style>
            .ef-label { color: var(--app-text); display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.5rem; }
            .ef-input { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 1rem; color: var(--app-text); font-size: 0.9rem; outline: none; padding: 0.85rem 1rem; width: 100%; }
            .ef-input:focus { border-color: color-mix(in srgb, var(--app-brand) 60%, var(--app-border)); }
            .ef-error { color: #fca5a5; font-size: 0.78rem; margin-top: 0.4rem; }
            .ef-btn-primary, .ef-btn-ghost { align-items: center; border-radius: 0.9rem; display: inline-flex; font-size: 0.84rem; font-weight: 600; min-height: 3rem; padding: 0 1.1rem; text-decoration: none; }
            .ef-btn-primary { background: var(--app-brand); color: #0f172a; }
            .ef-btn-ghost { border: 1px solid var(--app-border); color: var(--app-text-muted); }
            .ef-btn-ghost:hover { background: color-mix(in srgb, var(--app-border) 50%, transparent); color: var(--app-text); }
        </style>
    @endpush
</x-layouts.admin>
