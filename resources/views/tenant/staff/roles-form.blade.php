@php
    $editing    = isset($roleRow) && $roleRow !== null;
    $isSystem   = $editing && in_array($roleRow->role, \App\Models\Staff::ROLES);
    $permissions = $editing ? ($roleRow->permissions ?? []) : [];

    $moduleIcons = [
        'dashboard'  => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'members'    => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'renewals'   => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
        'attendance' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
        'classes'    => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'branches'   => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'staff'      => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'payments'   => '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
        'invoices'   => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'expenses'   => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'pos'        => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
        'reports'    => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
    ];
@endphp

<x-layouts.admin
    title="{{ $editing ? str($roleRow->role)->replace('_',' ')->title().' — Permissions' : 'Add Role' }}"
    eyebrow="Gym Workspace"
    heading="{{ $editing ? str($roleRow->role)->replace('_',' ')->title() : 'Add new role' }}"
    subheading="{{ $editing ? 'Enable or disable permissions for this role.' : 'Define a custom role and configure its permissions.' }}"
>

    <x-slot:headerAction>
        <div class="flex gap-2">
            @if ($editing && $isSystem)
                <form method="POST" action="{{ route('tenant.staff.roles.reset', $roleRow->role) }}">
                    @csrf
                    <button type="submit"
                            class="rounded-2xl px-4 py-2.5 text-sm font-semibold transition-opacity hover:opacity-75"
                            style="background:var(--app-panel-strong);color:var(--app-text-muted);border:1px solid var(--app-border)">
                        Reset defaults
                    </button>
                </form>
            @endif
            <a href="{{ route('tenant.staff.roles') }}"
               class="rounded-2xl px-4 py-2.5 text-sm font-semibold transition-opacity hover:opacity-75"
               style="background:var(--app-panel-strong);color:var(--app-text-muted);border:1px solid var(--app-border)">
                ← Back
            </a>
        </div>
    </x-slot:headerAction>

    @if ($errors->any())
        <div class="mb-5 rounded-2xl px-4 py-3 text-sm text-red-300"
             style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2)">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST"
          action="{{ $editing ? route('tenant.staff.roles.update', $roleRow->role) : route('tenant.staff.roles.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        {{-- Role name (new roles only) --}}
        @if (! $editing)
            <div class="rounded-2xl p-5 mb-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
                <div class="flex items-start gap-4">
                    <div class="h-9 w-9 rounded-xl flex items-center justify-center flex-none"
                         style="background:color-mix(in srgb,var(--app-brand) 12%,transparent)">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                             style="color:var(--app-brand)">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M20 21a8 8 0 1 0-16 0"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-semibold mb-1" style="color:var(--app-text)">
                            Role name <span class="text-red-400">*</span>
                        </label>
                        <p class="text-xs mb-3" style="color:var(--app-text-muted)">Lowercase letters and underscores only — e.g. <code>floor_manager</code></p>
                        <input name="role_name"
                               value="{{ old('role_name') }}"
                               placeholder="floor_manager"
                               pattern="[a-z_]+"
                               class="w-full max-w-xs rounded-xl border px-4 py-2.5 text-sm outline-none focus:ring-2"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)"
                               required>
                        @error('role_name')
                            <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        @endif

        {{-- Permissions --}}
        <div class="space-y-3">
            @foreach ($defaultModules as $module => $actions)
                @php
                    $modulePerms  = $permissions[$module] ?? [];
                    $icon         = $moduleIcons[$module] ?? '<circle cx="12" cy="12" r="9"/>';
                    $initChecked  = collect($actions)->mapWithKeys(fn($a) => [$a => (bool)($modulePerms[$a] ?? false)]);
                @endphp

                <div class="rounded-2xl overflow-hidden"
                     style="background:var(--app-panel);border:1px solid var(--app-border)"
                     x-data='{
                         open: {{ $initChecked->contains(true) ? "true" : "false" }},
                         ch: {{ $initChecked->toJson() }},
                         get all() { return Object.values(this.ch).every(Boolean) },
                         get some() { return Object.values(this.ch).some(Boolean) && !this.all },
                         toggleAll() { const v = !this.all; Object.keys(this.ch).forEach(k => this.ch[k] = v); this.open = true; }
                     }'>

                    {{-- Module header --}}
                    <div class="flex items-center gap-3 px-5 py-4 cursor-pointer select-none"
                         @click="open = !open">
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center flex-none transition-colors"
                             :style="some || all ? 'background:color-mix(in srgb,var(--app-brand) 14%,transparent)' : 'background:var(--app-panel-strong)'">
                            <svg class="h-4 w-4 transition-colors"
                                 :style="some || all ? 'color:var(--app-brand)' : 'color:var(--app-text-muted)'"
                                 fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                {!! $icon !!}
                            </svg>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold" style="color:var(--app-text)">
                                {{ str($module)->replace('_', ' ')->title() }}
                            </p>
                            <p class="text-xs" style="color:var(--app-text-muted)">
                                {{ count($actions) }} {{ Str::plural('permission', count($actions)) }}
                            </p>
                        </div>

                        {{-- Enable-all toggle --}}
                        <button type="button" @click.stop="toggleAll()"
                                class="hidden sm:flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium transition-colors"
                                :style="all
                                    ? 'background:color-mix(in srgb,var(--app-brand) 12%,transparent);color:var(--app-brand)'
                                    : 'background:var(--app-panel-strong);color:var(--app-text-muted)'">
                            <svg x-show="all" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M18 6L6 18M6 6l12 12"/>
                            </svg>
                            <svg x-show="!all" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                            <span x-text="all ? 'Disable all' : 'Enable all'"></span>
                        </button>

                        {{-- Active dot indicator --}}
                        <span class="h-2 w-2 rounded-full flex-none transition-colors"
                              :style="all ? 'background:var(--app-brand)' : some ? 'background:color-mix(in srgb,var(--app-brand) 60%,transparent)' : 'background:var(--app-border)'">
                        </span>

                        {{-- Expand chevron --}}
                        <svg class="h-4 w-4 transition-transform flex-none" :class="open && 'rotate-180'"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                             style="color:var(--app-text-muted)">
                            <path d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    {{-- Permission pills --}}
                    <div x-show="open" x-cloak
                         class="px-5 pb-5 border-t" style="border-color:var(--app-border)">
                        <div class="flex flex-wrap gap-2 pt-4">
                            @foreach ($actions as $action)
                                <button type="button"
                                        class="permission-pill"
                                        :class="ch['{{ $action }}'] ? 'pill-on' : 'pill-off'"
                                        @click="ch['{{ $action }}'] = !ch['{{ $action }}']">
                                    {{-- hidden input tracks state for form submission --}}
                                    <input type="hidden"
                                           name="permissions[{{ $module }}][{{ $action }}]"
                                           :value="ch['{{ $action }}'] ? '1' : '0'">
                                    {{-- checkmark when on --}}
                                    <svg x-show="ch['{{ $action }}']" class="h-3 w-3 flex-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{-- plus when off --}}
                                    <svg x-show="!ch['{{ $action }}']" class="h-3 w-3 flex-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path d="M12 5v14M5 12h14"/>
                                    </svg>
                                    <span class="text-xs font-medium">
                                        {{ str($action)->replace('_', ' ')->title() }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Save bar --}}
        <div class="mt-5 flex items-center gap-3 rounded-2xl px-5 py-4"
             style="background:var(--app-panel);border:1px solid var(--app-border)">
            <button type="submit"
                    class="rounded-xl px-6 py-2.5 text-sm font-semibold text-white transition-opacity hover:opacity-85"
                    style="background:var(--app-brand)">
                {{ $editing ? 'Save permissions' : 'Create role' }}
            </button>
            <a href="{{ route('tenant.staff.roles') }}"
               class="rounded-xl px-5 py-2.5 text-sm font-semibold transition-opacity hover:opacity-75"
               style="background:var(--app-panel-strong);color:var(--app-text-muted);border:1px solid var(--app-border)">
                Cancel
            </a>
            @if ($editing)
                <p class="ml-auto text-xs" style="color:var(--app-text-muted)">
                    Last saved: {{ $roleRow->updated_at?->diffForHumans() ?? 'never' }}
                </p>
            @endif
        </div>

    </form>

</x-layouts.admin>

<style>
.permission-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    border-radius: 2rem;
    padding: 0.375rem 0.875rem;
    border: 1px solid transparent;
    transition: background 0.12s, border-color 0.12s, color 0.12s;
    line-height: 1;
    cursor: pointer;
    user-select: none;
    font-family: inherit;
}
.pill-off {
    background: var(--app-panel-strong);
    border: 1.5px dashed rgba(128,128,128,0.35);
    color: var(--app-text-muted);
}
.pill-off:hover {
    background: color-mix(in srgb, var(--app-brand) 8%, transparent);
    border-color: color-mix(in srgb, var(--app-brand) 50%, transparent);
    color: var(--app-brand);
}
.pill-on {
    background: color-mix(in srgb, var(--app-brand) 14%, transparent);
    border: 1.5px solid color-mix(in srgb, var(--app-brand) 45%, transparent);
    color: var(--app-brand);
}
</style>
