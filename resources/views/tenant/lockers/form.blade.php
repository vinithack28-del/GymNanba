<x-layouts.admin
    title="Add Locker"
    eyebrow="Operations"
    heading="Add Locker"
    subheading="Register a locker and keep it ready for member assignment."
>
    <x-slot:headerAction>
        <a href="{{ route('tenant.lockers.index') }}"
           class="inline-flex items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-medium transition hover:opacity-80"
           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text-muted)">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
            Back to Lockers
        </a>
    </x-slot:headerAction>

    <form method="POST" action="{{ route('tenant.lockers.store') }}" class="lk-form-card app-panel rounded-[2rem] border p-6">
        @csrf

        @if ($errors->any())
            <div class="mb-5 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-5 md:grid-cols-2">
            @if ($selectedBranchId)
                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
            @else
                <div>
                    <label class="lk-label">Branch <span class="text-red-400">*</span></label>
                    <select name="branch_id" class="lk-input" required>
                        <option value="">Select branch…</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id') <p class="lk-error">{{ $message }}</p> @enderror
                </div>
            @endif

            <div>
                <label class="lk-label">Locker No. <span class="text-red-400">*</span></label>
                <input type="text" name="locker_number" value="{{ old('locker_number') }}" class="lk-input" placeholder="e.g. L-07" maxlength="20" required>
                @error('locker_number') <p class="lk-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="lk-label">Status <span class="text-red-400">*</span></label>
                <select name="status" class="lk-input" required>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'active') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <p class="lk-error">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="lk-label">Location / Zone</label>
                <input type="text" name="location" value="{{ old('location') }}" class="lk-input" placeholder="e.g. Male changing room, Zone A" maxlength="200">
                @error('location') <p class="lk-error">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="lk-label">Notes</label>
                <textarea name="notes" rows="5" class="lk-input" placeholder="Extra details…" maxlength="1000">{{ old('notes') }}</textarea>
                @error('notes') <p class="lk-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button type="submit" class="lk-btn-primary">Add Locker</button>
            <a href="{{ route('tenant.lockers.index') }}" class="lk-btn-ghost">Cancel</a>
        </div>
    </form>

    @push('styles')
        <style>
            .lk-label { color: var(--app-text); display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.5rem; }
            .lk-input { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 1rem; color: var(--app-text); font-size: 0.9rem; outline: none; padding: 0.85rem 1rem; width: 100%; }
            .lk-input:focus { border-color: color-mix(in srgb, var(--app-brand) 60%, var(--app-border)); }
            .lk-error { color: #fca5a5; font-size: 0.78rem; margin-top: 0.4rem; }
            .lk-btn-primary, .lk-btn-ghost { align-items: center; border-radius: 0.9rem; display: inline-flex; font-size: 0.84rem; font-weight: 600; min-height: 3rem; padding: 0 1.1rem; text-decoration: none; }
            .lk-btn-primary { background: var(--app-brand); color: #0f172a; }
            .lk-btn-ghost { border: 1px solid var(--app-border); color: var(--app-text-muted); }
            .lk-btn-ghost:hover { background: color-mix(in srgb, var(--app-border) 50%, transparent); color: var(--app-text); }
        </style>
    @endpush
</x-layouts.admin>
