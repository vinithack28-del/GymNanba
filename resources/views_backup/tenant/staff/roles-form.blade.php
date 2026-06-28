@php
    $editing    = isset($roleRow) && $roleRow !== null;
    $isSystem   = $editing && (bool) ($roleRow->is_system ?? false);
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
        'equipment'  => '<path d="M14.7 6.3a1 1 0 0 1 1.4 0l1.6 1.6a1 1 0 0 1 0 1.4l-1 1 1.4 1.4a1 1 0 0 1 0 1.4l-1.4 1.4a1 1 0 0 1-1.4 0l-1.4-1.4-1.3 1.3 1.4 1.4a1 1 0 0 1 0 1.4l-1.4 1.4a1 1 0 0 1-1.4 0l-1.4-1.4-1 1a1 1 0 0 1-1.4 0L6.3 16.1a1 1 0 0 1 0-1.4l1-1-1.4-1.4a1 1 0 0 1 0-1.4l1.4-1.4a1 1 0 0 1 1.4 0l1.4 1.4 1.3-1.3-1.4-1.4a1 1 0 0 1 0-1.4l1.4-1.4a1 1 0 0 1 1.4 0l1.4 1.4z"/><circle cx="12" cy="12" r="2.5"/>',
        'locker'     => '<rect x="6" y="3" width="12" height="18" rx="2"/><circle cx="12" cy="12" r="1.2"/><path d="M12 8v2"/>',
    ];
@endphp

<x-layouts.admin
    title="{{ $editing ? (($roleRow->display_name ?? str($roleRow->role)->replace('_',' ')->title()).' — Permissions') : 'Add Role' }}"
    eyebrow="Gym Workspace"
    heading="{{ $editing ? ($roleRow->display_name ?? str($roleRow->role)->replace('_',' ')->title()) : 'Add new role' }}"
    subheading="{{ $editing ? 'Enable or disable permissions for this role.' : 'Define a custom role and configure its permissions.' }}"
>
    @if ($editing)
        @php
            $activeModules = collect($permissions)->filter(fn ($actions) => collect($actions)->contains(true))->count();
        @endphp
        <div class="mb-5 rounded-2xl border px-5 py-4" style="background:var(--app-panel);border-color:var(--app-border)">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs uppercase tracking-[0.18em]" style="color:var(--app-text-muted)">Role</p>
                    <p class="mt-2 text-lg font-semibold" style="color:var(--app-text)">{{ $roleRow->display_name ?? str($roleRow->role)->replace('_',' ')->title() }}</p>
                    <p class="mt-1 text-xs font-mono" style="color:var(--app-text-muted)">{{ $roleRow->role }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <span class="rounded-full px-3 py-1.5 font-medium" style="background:var(--app-panel-strong);color:var(--app-text);border:1px solid var(--app-border)">
                        {{ $isSystem ? 'System role' : 'Custom role' }}
                    </span>
                    <span class="rounded-full px-3 py-1.5 font-medium" style="background:var(--app-panel-strong);color:var(--app-text);border:1px solid var(--app-border)">
                        {{ number_format($staffCount ?? 0) }} assigned staff
                    </span>
                    <span class="rounded-full px-3 py-1.5 font-medium" style="background:var(--app-panel-strong);color:var(--app-text);border:1px solid var(--app-border)">
                        {{ number_format($activeModules) }} active modules
                    </span>
                    <span class="text-xs" style="color:var(--app-text-muted)">
                        Updated {{ $roleRow->updated_at?->diffForHumans() ?? 'never' }}
                    </span>
                </div>
            </div>
        </div>
    @endif


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
            @foreach ($defaultModules as $moduleRow)
                @php
                    $module = $moduleRow->slug;
                    $actions = $moduleRow->actions;
                    $modulePerms  = $permissions[$module] ?? [];
                    $iconKey      = $moduleRow->icon ?? $module;
                    $icon         = $moduleIcons[$iconKey] ?? '<circle cx="12" cy="12" r="9"/>';
                    $actionSlugs  = collect($actions)->pluck('slug');
                    $initChecked  = $actionSlugs->mapWithKeys(fn($a) => [$a => (bool)($modulePerms[$a] ?? false)]);
                    $moduleSlug   = \Illuminate\Support\Str::slug($module);
                @endphp

                <div class="rounded-2xl overflow-hidden"
                     style="background:var(--app-panel);border:1px solid var(--app-border)"
                     data-permission-module
                     data-module="{{ $moduleSlug }}"
                     data-open="{{ $initChecked->contains(true) ? '1' : '0' }}">

                    {{-- Module header --}}
                    <div class="flex items-center gap-3 px-5 py-4 cursor-pointer select-none"
                         data-module-header>
                        <div class="h-8 w-8 rounded-lg flex items-center justify-center flex-none transition-colors permission-module-icon">
                            <svg class="h-4 w-4 transition-colors"
                                 data-module-icon-svg
                                 fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                {!! $icon !!}
                            </svg>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold" style="color:var(--app-text)">
                                {{ $moduleRow->name }}
                            </p>
                            <p class="text-xs" style="color:var(--app-text-muted)">
                                {{ $actions->count() }} {{ Str::plural('permission', $actions->count()) }}
                            </p>
                        </div>

                        {{-- Enable-all toggle --}}
                        <button type="button"
                                class="hidden sm:flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium transition-colors"
                                data-toggle-all>
                            <svg class="h-3 w-3 hidden" data-toggle-all-on-icon fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M18 6L6 18M6 6l12 12"/>
                            </svg>
                            <svg class="h-3 w-3" data-toggle-all-off-icon fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                            <span data-toggle-all-text>Enable all</span>
                        </button>

                        {{-- Active dot indicator --}}
                        <span class="h-2 w-2 rounded-full flex-none transition-colors permission-module-dot"
                              data-module-dot>
                        </span>

                        {{-- Expand chevron --}}
                        <svg class="h-4 w-4 transition-transform flex-none"
                             data-module-chevron
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                             style="color:var(--app-text-muted)">
                            <path d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    {{-- Permission pills --}}
                    <div
                         data-module-body
                         class="px-5 pb-5 border-t" style="border-color:var(--app-border)">
                        <div class="flex flex-wrap gap-2 pt-4">
                            @foreach ($actions as $actionRow)
                                @php
                                    $action = $actionRow->slug;
                                    $isChecked = (bool) ($modulePerms[$action] ?? false);
                                @endphp
                                <label class="permission-pill" data-permission-pill data-action="{{ $action }}">
                                    <input type="checkbox"
                                           class="permission-pill-input"
                                           name="permissions[{{ $module }}][{{ $action }}]"
                                           value="1"
                                           @checked($isChecked)
                                           data-permission-input>
                                    <span class="permission-pill-content">
                                        <svg class="h-3 w-3 flex-none" data-pill-on-icon fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <svg class="h-3 w-3 flex-none" data-pill-off-icon fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path d="M12 5v14M5 12h14"/>
                                        </svg>
                                        <span class="text-xs font-medium">
                                            {{ $actionRow->name }}
                                        </span>
                                    </span>
                                </label>
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
    cursor: pointer;
    display: inline-flex;
    font-family: inherit;
    line-height: 1;
    user-select: none;
}

.permission-pill-input {
    height: 1px;
    left: -9999px;
    opacity: 0;
    pointer-events: none;
    position: absolute;
    width: 1px;
}

.permission-pill-content {
    align-items: center;
    background: var(--app-panel-strong);
    border: 1.5px dashed rgba(128,128,128,0.35);
    border-radius: 2rem;
    color: var(--app-text-muted);
    display: inline-flex;
    gap: 0.375rem;
    padding: 0.375rem 0.875rem;
    transition: background 0.12s, border-color 0.12s, color 0.12s;
}

.permission-pill:hover .permission-pill-content {
    background: color-mix(in srgb, var(--app-brand) 8%, transparent);
    border-color: color-mix(in srgb, var(--app-brand) 50%, transparent);
    color: var(--app-brand);
}

.permission-pill-input:checked + .permission-pill-content {
    background: color-mix(in srgb, var(--app-brand) 14%, transparent);
    border: 1.5px solid color-mix(in srgb, var(--app-brand) 45%, transparent);
    color: var(--app-brand);
}

.permission-pill-input:not(:checked) + .permission-pill-content [data-pill-on-icon] {
    display: none;
}

.permission-pill-input:checked + .permission-pill-content [data-pill-off-icon] {
    display: none;
}

.permission-module-active .permission-module-icon {
    background: color-mix(in srgb,var(--app-brand) 14%,transparent);
}

.permission-module-active [data-module-icon-svg] {
    color: var(--app-brand);
}

.permission-module-inactive .permission-module-icon {
    background: var(--app-panel-strong);
}

.permission-module-inactive [data-module-icon-svg] {
    color: var(--app-text-muted);
}

.permission-module-all [data-toggle-all] {
    background: color-mix(in srgb,var(--app-brand) 12%,transparent);
    color: var(--app-brand);
}

.permission-module-not-all [data-toggle-all] {
    background: var(--app-panel-strong);
    color: var(--app-text-muted);
}

.permission-module-all [data-module-dot] {
    background: var(--app-brand);
}

.permission-module-some [data-module-dot] {
    background: color-mix(in srgb,var(--app-brand) 60%,transparent);
}

.permission-module-none [data-module-dot] {
    background: var(--app-border);
}

[data-permission-module][data-open="1"] [data-module-chevron] {
    transform: rotate(180deg);
}
</style>

@push('scripts')
<script>
    (() => {
        const modules = document.querySelectorAll('[data-permission-module]');

        const updatePillState = (pill, checked) => {
            const input = pill.querySelector('[data-permission-input]');
            if (input) {
                input.checked = checked;
            }
        };

        const updateModuleState = (module) => {
            const pills = [...module.querySelectorAll('[data-permission-pill]')];
            const checked = pills.filter((pill) => pill.querySelector('[data-permission-input]')?.checked).length;
            const all = checked === pills.length && pills.length > 0;
            const some = checked > 0 && !all;
            const none = checked === 0;

            module.classList.toggle('permission-module-active', all || some);
            module.classList.toggle('permission-module-inactive', none);
            module.classList.toggle('permission-module-all', all);
            module.classList.toggle('permission-module-not-all', !all);
            module.classList.toggle('permission-module-some', some);
            module.classList.toggle('permission-module-none', none);

            module.querySelector('[data-toggle-all-text]').textContent = all ? 'Disable all' : 'Enable all';
            module.querySelector('[data-toggle-all-on-icon]')?.classList.toggle('hidden', !all);
            module.querySelector('[data-toggle-all-off-icon]')?.classList.toggle('hidden', all);
        };

        modules.forEach((module) => {
            const body = module.querySelector('[data-module-body]');
            const header = module.querySelector('[data-module-header]');
            const toggleAll = module.querySelector('[data-toggle-all]');
            const pills = [...module.querySelectorAll('[data-permission-pill]')];

            const setOpen = (open) => {
                module.dataset.open = open ? '1' : '0';
                body?.classList.toggle('hidden', !open);
            };

            header?.addEventListener('click', () => {
                setOpen(module.dataset.open !== '1');
            });

            toggleAll?.addEventListener('click', (event) => {
                event.stopPropagation();
                const shouldEnableAll = !module.classList.contains('permission-module-all');
                pills.forEach((pill) => updatePillState(pill, shouldEnableAll));
                setOpen(true);
                updateModuleState(module);
            });

            pills.forEach((pill) => {
                const input = pill.querySelector('[data-permission-input]');
                input?.addEventListener('change', () => {
                    updatePillState(pill, input.checked);
                    updateModuleState(module);
                });
            });

            setOpen(module.dataset.open === '1');
            updateModuleState(module);
        });
    })();
</script>
@endpush
