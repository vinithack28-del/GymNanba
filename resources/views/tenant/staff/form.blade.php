@php $editing = isset($staff); @endphp

<x-layouts.admin
    title="{{ $editing ? __('staff.form.edit_title') : __('staff.form.create_title') }}"
    eyebrow="Gym Workspace"
    heading="{{ $editing ? $staff->name : __('staff.form.create_title') }}"
    subheading="{{ $editing ? __('staff.form.edit_subheading') : __('staff.form.create_subheading') }}"
>

<form id="staff-form"
      method="POST"
      action="{{ $editing ? route('tenant.staff.update', $staff) : route('tenant.staff.store') }}"
      enctype="multipart/form-data">
    @csrf
    @if ($editing) @method('PUT') @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Section: Personal & role --}}
    <div class="app-panel rounded-[2rem] border p-6">
        <h2 class="mb-5 text-base font-semibold uppercase tracking-[0.16em] opacity-60">Personal &amp; Role</h2>
        <div class="grid gap-4 md:grid-cols-2">

            <div>
                <label class="mb-2 block text-sm font-medium">{{ __('staff.form.full_name') }} <span class="text-red-400">*</span></label>
                <input name="name" value="{{ old('name', $staff->name ?? '') }}"
                       class="w-full rounded-2xl border px-4 py-3 text-sm outline-none" required>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">{{ __('staff.form.phone') }} <span class="text-red-400">*</span></label>
                <input name="phone" value="{{ old('phone', $staff->phone ?? '') }}"
                       placeholder="+919876543210"
                       class="w-full rounded-2xl border px-4 py-3 text-sm outline-none" required>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">{{ __('staff.form.email') }} <span class="text-red-400">*</span></label>
                <input type="email" name="email" value="{{ old('email', $staff->email ?? '') }}"
                       class="w-full rounded-2xl border px-4 py-3 text-sm outline-none" required>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">{{ __('staff.form.role') }} <span class="text-red-400">*</span></label>
                <select name="role" class="w-full rounded-2xl border px-4 py-3 text-sm outline-none" required>
                    <option value="">{{ __('staff.form.select_role') }}</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->role }}" @selected(old('role', $staff->role ?? '') === $role->role)>
                            {{ $role->display_name ?? str($role->role)->replace('_', ' ')->title() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">{{ __('staff.form.branch') }} <span class="text-red-400">*</span></label>
                <select name="branch_id" class="w-full rounded-2xl border px-4 py-3 text-sm outline-none" required>
                    <option value="">{{ __('staff.form.select_branch') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}"
                                @selected((string) old('branch_id', $staff->branch_id ?? $selectedBranchId ?? '') === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">{{ __('staff.form.join_date') }} <span class="text-red-400">*</span></label>
                <input type="date" name="join_date"
                       value="{{ old('join_date', isset($staff) ? $staff->join_date?->format('Y-m-d') : '') }}"
                       class="w-full rounded-2xl border px-4 py-3 text-sm outline-none" required>
            </div>

        </div>
    </div>

    {{-- Section: Employment --}}
    <div class="app-panel mt-4 rounded-[2rem] border p-6">
        <h2 class="mb-5 text-base font-semibold uppercase tracking-[0.16em] opacity-60">Employment</h2>
        <div class="grid gap-4 md:grid-cols-2">

            {{-- Salary shown in ₹; JS converts to paise on submit --}}
            <div>
                <label class="mb-2 block text-sm font-medium">{{ __('staff.form.salary') }}</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm opacity-50">₹</span>
                    <input id="salary-display"
                           type="number" min="0" step="0.01"
                           value="{{ old('salary_rupees', isset($staff) && $staff->salary_paise ? $staff->salary_paise / 100 : '') }}"
                           placeholder="0.00"
                           class="w-full rounded-2xl border px-4 py-3 pl-8 text-sm outline-none">
                </div>
                <input type="hidden" name="salary_paise" id="salary-paise"
                       value="{{ old('salary_paise', $staff->salary_paise ?? '') }}">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">{{ __('staff.form.id_proof_type') }}</label>
                <select name="id_proof_type" class="w-full rounded-2xl border px-4 py-3 text-sm outline-none">
                    <option value="">{{ __('staff.form.select_proof') }}</option>
                    @foreach ($proofTypes as $type)
                        <option value="{{ $type }}" @selected(old('id_proof_type', $staff->id_proof_type ?? '') === $type)>
                            {{ strtoupper($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">{{ __('staff.form.id_proof_upload') }}</label>
                <input type="file" name="id_proof" accept=".jpg,.jpeg,.png,.pdf"
                       class="w-full rounded-2xl border px-4 py-3 text-sm outline-none">
                @if (isset($staff) && $staff->id_proof_url)
                    <p class="mt-1 text-xs opacity-60">Current file uploaded — upload a new one to replace it.</p>
                @endif
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">{{ __('staff.form.profile_photo') }}</label>
                @if (isset($staff) && $staff->photo_url)
                    <div class="mb-2 flex items-center gap-3">
                        <img src="{{ asset('storage/'.$staff->photo_url) }}" alt="Current photo"
                             class="h-12 w-12 rounded-full object-cover ring-2 ring-[var(--app-border)]">
                        <span class="text-xs opacity-60">Upload new to replace</span>
                    </div>
                @endif
                <input type="file" name="photo" accept=".jpg,.jpeg,.png"
                       class="w-full rounded-2xl border px-4 py-3 text-sm outline-none">
            </div>

            @if ($editing)
                <div>
                    <label class="mb-2 block text-sm font-medium">{{ __('staff.form.status') }}</label>
                    <select name="status" class="w-full rounded-2xl border px-4 py-3 text-sm outline-none" required>
                        @foreach (['active', 'inactive'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $staff->status) === $status)>
                                {{ __('staff.statuses.'.$status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium">{{ __('staff.form.notes') }}</label>
                <textarea name="notes" rows="3"
                          class="w-full rounded-2xl border px-4 py-3 text-sm outline-none">{{ old('notes', $staff->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>

    @if ($editing)
        {{-- Section: Reset password --}}
        <div class="app-panel mt-4 rounded-[2rem] border p-6">
            <h2 class="mb-5 text-base font-semibold uppercase tracking-[0.16em] opacity-60">Security</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium">{{ __('staff.form.reset_password') }}</label>
                    <input type="password" name="password"
                           placeholder="Leave blank to keep current"
                           class="w-full rounded-2xl border px-4 py-3 text-sm outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">{{ __('staff.form.confirm_password') }}</label>
                    <input type="password" name="password_confirmation"
                           class="w-full rounded-2xl border px-4 py-3 text-sm outline-none">
                </div>
            </div>
        </div>
    @endif

    {{-- Footer buttons --}}
    <div class="mt-6 flex gap-3">
        <button type="submit"
                class="rounded-2xl bg-orange-500 px-6 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400">
            {{ $editing ? __('staff.actions.update') : __('staff.actions.save') }}
        </button>
        <a href="{{ route('tenant.staff.index') }}"
           class="rounded-2xl border px-6 py-3 text-sm font-semibold hover:opacity-80">
            {{ __('staff.actions.back') }}
        </a>
    </div>
</form>

<script>
    // Convert rupees display field to paise hidden field before submit
    document.getElementById('staff-form').addEventListener('submit', function () {
        const display = document.getElementById('salary-display');
        const paise   = document.getElementById('salary-paise');
        const rupees  = parseFloat(display.value);
        paise.value   = rupees > 0 ? Math.round(rupees * 100) : '';
    });
</script>

</x-layouts.admin>
