<x-layouts.admin
    title="{{ __('staff.roles_page.title') }}"
    eyebrow="Gym Workspace"
    heading="{{ __('staff.roles_page.title') }}"
    subheading="{{ __('staff.roles_page.subheading') }}"
>

    <x-slot:headerAction>
        <a href="{{ route('tenant.staff.roles.create') }}"
           class="inline-flex items-center gap-2 rounded-2xl px-5 py-2.5 text-sm font-semibold text-white"
           style="background:var(--app-brand)">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Add Role
        </a>
    </x-slot:headerAction>

    @php
        $roleData = [
            'receptionist'   => ['color' => '#06b6d4', 'light' => 'rgba(6,182,212,0.12)',   'border' => 'rgba(6,182,212,0.3)'],
            'trainer'        => ['color' => '#a855f7', 'light' => 'rgba(168,85,247,0.12)',  'border' => 'rgba(168,85,247,0.3)'],
            'accountant'     => ['color' => '#f59e0b', 'light' => 'rgba(245,158,11,0.12)',  'border' => 'rgba(245,158,11,0.3)'],
            'pos'            => ['color' => '#3b82f6', 'light' => 'rgba(59,130,246,0.12)',  'border' => 'rgba(59,130,246,0.3)'],
            'branch_manager' => ['color' => '#10b981', 'light' => 'rgba(16,185,129,0.12)', 'border' => 'rgba(16,185,129,0.3)'],
        ];
        $fallback   = ['color' => 'var(--app-brand)', 'light' => 'color-mix(in srgb,var(--app-brand) 12%,transparent)', 'border' => 'color-mix(in srgb,var(--app-brand) 30%,transparent)'];
        $totalRoles = $roles->count();
        $sysCount   = $roles->filter(fn($r) => (bool) $r->is_system)->count();
        $custCount  = $totalRoles - $sysCount;
    @endphp

    {{-- Summary strip --}}
    <div class="mb-6 rounded-2xl px-5 py-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <div class="flex flex-wrap items-center gap-3">
            @foreach ([['Total Roles', $totalRoles, 'var(--app-text)'], ['System', $sysCount, 'var(--app-text-muted)'], ['Custom', $custCount, 'var(--app-brand)']] as [$label, $val, $clr])
                <div class="inline-flex items-center gap-3 rounded-full px-4 py-2.5" style="background:var(--app-panel-strong);border:1px solid var(--app-border)">
                    <span class="text-xs font-medium uppercase tracking-[0.14em]" style="color:var(--app-text-muted)">{{ $label }}</span>
                    <span class="text-base font-bold" style="color:{{ $clr }}">{{ $val }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Role cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($roles as $roleRow)
            @php
                $isSystem   = (bool) $roleRow->is_system;
                $rd         = $roleData[$roleRow->role] ?? $fallback;
                $modCount   = count($roleRow->permissions ?? []);
                $staffCount = $staffCounts[$roleRow->role] ?? 0;
                $label      = $roleRow->display_name ?? str($roleRow->role)->replace('_', ' ')->title();
            @endphp

            <div class="rounded-2xl overflow-hidden flex flex-col"
                 style="background:var(--app-panel);border:1px solid var(--app-border)"
                 x-data="{ del: false }">

                {{-- Colour accent bar --}}
                <div class="h-1 w-full" style="background:{{ $rd['color'] }}"></div>

                <div class="p-5 flex flex-col flex-1 gap-4">

                    {{-- Row 1: avatar + name + badge --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-11 w-11 rounded-xl flex items-center justify-center font-bold text-base flex-none"
                                 style="background:{{ $rd['light'] }};color:{{ $rd['color'] }};border:1px solid {{ $rd['border'] }}">
                                {{ strtoupper(substr($roleRow->role, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-sm truncate" style="color:var(--app-text)">{{ $label }}</p>
                                <p class="text-xs font-mono mt-0.5 truncate" style="color:var(--app-text-muted)">{{ $roleRow->role }}</p>
                            </div>
                        </div>
                        <span class="flex-none text-xs font-semibold px-2.5 py-1 rounded-full"
                              @if($isSystem)
                              style="background:rgba(148,163,184,0.1);color:#94a3b8;border:1px solid rgba(148,163,184,0.2)"
                              @else
                              style="background:{{ $rd['light'] }};color:{{ $rd['color'] }};border:1px solid {{ $rd['border'] }}"
                              @endif>
                            {{ $isSystem ? 'System' : 'Custom' }}
                        </span>
                    </div>

                    {{-- Row 2: stats grid --}}
                    <div class="grid grid-cols-3 divide-x rounded-xl py-3"
                         style="background:var(--app-panel-strong);border:1px solid var(--app-border);divide-color:var(--app-border)">
                        <div class="text-center px-2">
                            <p class="text-xs mb-0.5" style="color:var(--app-text-muted)">Modules</p>
                            <p class="text-sm font-bold" style="color:var(--app-text)">{{ $modCount }}</p>
                        </div>
                        <div class="text-center px-2">
                            <p class="text-xs mb-0.5" style="color:var(--app-text-muted)">Staff</p>
                            <p class="text-sm font-bold" style="color:{{ $staffCount > 0 ? 'var(--app-text)' : 'var(--app-text-muted)' }}">{{ $staffCount }}</p>
                        </div>
                        <div class="text-center px-2">
                            <p class="text-xs mb-0.5" style="color:var(--app-text-muted)">Updated</p>
                            <p class="text-xs" style="color:var(--app-text-muted)">
                                {{ $roleRow->updated_at?->diffForHumans() ?? '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- Row 3: actions --}}
                    <div class="pt-3 border-t mt-auto" style="border-color:var(--app-border)">

                        {{-- Normal actions --}}
                        <div x-show="!del" class="flex gap-2">
                            <a href="{{ route('tenant.staff.roles.edit', $roleRow->role) }}"
                               class="flex-1 rounded-xl py-2 text-center text-xs font-semibold transition-opacity hover:opacity-75"
                               style="background:{{ $rd['light'] }};color:{{ $rd['color'] }};border:1px solid {{ $rd['border'] }}">
                                Edit Permissions
                            </a>
                            @if (! $isSystem)
                                <button type="button" @click="del = true"
                                        class="rounded-xl px-3 py-2 text-xs transition-opacity hover:opacity-75"
                                        style="background:rgba(239,68,68,0.08);color:#f87171;border:1px solid rgba(239,68,68,0.2)">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6M14 11v6"/>
                                    </svg>
                                </button>
                            @endif
                        </div>

                        {{-- Delete confirmation --}}
                        @if (! $isSystem)
                            <div x-show="del" x-cloak class="space-y-2">
                                <p class="text-xs text-red-400">Delete "{{ $label }}"? Cannot be undone.</p>
                                <div class="flex gap-2">
                                    <form method="POST"
                                          action="{{ route('tenant.staff.roles.destroy', $roleRow->role) }}"
                                          class="flex-1">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-full rounded-xl py-2 text-xs font-semibold text-white bg-red-500 hover:bg-red-400 transition-colors">
                                            Yes, delete
                                        </button>
                                    </form>
                                    <button type="button" @click="del = false"
                                            class="flex-1 rounded-xl py-2 text-xs font-semibold transition-opacity hover:opacity-75"
                                            style="background:var(--app-panel-strong);color:var(--app-text-muted);border:1px solid var(--app-border)">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        @empty
            <div class="sm:col-span-2 xl:col-span-3 rounded-2xl py-16 text-center text-sm"
                 style="background:var(--app-panel);border:1px solid var(--app-border);color:var(--app-text-muted)">
                No roles configured yet.
            </div>
        @endforelse
    </div>

</x-layouts.admin>
