@php
    $availabilityOptions = ['available' => 'Available', 'occupied' => 'Occupied'];
    $statusOptions = App\Models\Locker::STATUSES;
@endphp

<x-layouts.admin
    title="Lockers"
    eyebrow="Operations"
    heading="Lockers"
    subheading="Track locker availability, assignments, and usage history."
>
    @if ($canAdd)
        <x-slot:headerAction>
            <a href="{{ route('tenant.lockers.create') }}"
                class="inline-flex items-center gap-2 rounded-full bg-[var(--app-brand)] px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:opacity-90">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add Locker
            </a>
        </x-slot:headerAction>
    @endif

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ([
            ['label' => 'Total', 'value' => $summary['total'], 'color' => 'var(--app-text)'],
            ['label' => 'Available', 'value' => $summary['available'], 'color' => '#22c55e'],
            ['label' => 'Occupied', 'value' => $summary['occupied'], 'color' => '#f59e0b'],
            ['label' => 'Inactive', 'value' => $summary['inactive'], 'color' => '#94a3b8'],
        ] as $card)
            <div class="app-panel rounded-2xl border p-4">
                <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">{{ $card['label'] }}</p>
                <p class="mt-1 text-3xl font-bold" style="color:{{ $card['color'] }}">{{ number_format($card['value']) }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-center gap-3">
        <div class="flex min-w-[220px] flex-1 items-center gap-2 rounded-xl border px-3 py-2.5"
             style="background:var(--app-panel-strong);border-color:var(--app-border)">
            <svg class="h-4 w-4 shrink-0" style="color:var(--app-text-muted)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search locker no. / member"
                   class="w-full bg-transparent text-sm outline-none" style="color:var(--app-text)">
        </div>
        @if(request('per_page'))
            <input type="hidden" name="per_page" value="{{ request('per_page') }}">
        @endif
        <select name="availability" onchange="this.form.submit()"
                class="rounded-xl border px-3 py-2.5 text-sm outline-none"
                style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
            <option value="">All Availability</option>
            @foreach ($availabilityOptions as $value => $label)
                <option value="{{ $value }}" @selected(request('availability') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()"
                class="rounded-xl border px-3 py-2.5 text-sm outline-none"
                style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
            <option value="">All Status</option>
            @foreach ($statusOptions as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @if (request()->hasAny(['search', 'availability', 'status']))
            <a href="{{ route('tenant.lockers.index') }}"
               class="rounded-xl border px-3 py-2.5 text-sm font-medium transition hover:opacity-80"
               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text-muted)">
                Clear
            </a>
        @endif
    </form>

    <div class="lk-desktop app-panel overflow-hidden rounded-[2rem] border">
        @if ($lockers->isEmpty())
            <div class="flex flex-col items-center gap-4 py-20 text-center">
                <div class="lk-empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="6" y="3" width="12" height="18" rx="2"></rect>
                        <circle cx="12" cy="12" r="1.2"></circle>
                        <path d="M12 8v2"></path>
                    </svg>
                </div>
                @if(request()->hasAny(['search','availability','status']))
                    <p class="text-base font-semibold" style="color:var(--app-text)">No lockers match these filters.</p>
                    <p class="text-sm" style="color:var(--app-text-muted)">Try adjusting your search or clearing the filters.</p>
                    <a href="{{ route('tenant.lockers.index') }}" class="lk-empty-ghost mt-2">Clear all</a>
                @else
                    <p class="text-base font-semibold" style="color:var(--app-text)">No lockers yet.</p>
                    <p class="text-sm" style="color:var(--app-text-muted)">Add your first locker to start assigning members.</p>
                    @if ($canAdd)
                        <a href="{{ route('tenant.lockers.create') }}" class="lk-empty-primary mt-2">Add Locker</a>
                    @endif
                @endif
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--app-panel-strong);border-bottom:1px solid var(--app-border)">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Locker No.</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Availability</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Assigned To</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Assigned Since</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lockers as $locker)
                    @php
                        $current = $locker->currentAssignment;
                        $member = $current?->member;
                        $isOccupied = $locker->availability === 'occupied' && $current;
                    @endphp
                    <tr class="border-t transition hover:opacity-95"
                        style="border-color:var(--app-border);background:var(--app-panel)">
                        <td class="px-4 py-3">
                            <a href="{{ route('tenant.lockers.show', $locker) }}" class="font-semibold hover:underline" style="color:var(--app-text)">{{ $locker->locker_number }}</a>
                            @if ($locker->location)
                                <div class="text-xs" style="color:var(--app-text-muted)">{{ $locker->location }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                  style="background:{{ $isOccupied ? 'color-mix(in srgb, #f59e0b 16%, transparent)' : 'color-mix(in srgb, #22c55e 16%, transparent)' }};color:{{ $isOccupied ? '#f59e0b' : '#22c55e' }}">
                                <span class="h-1.5 w-1.5 rounded-full" style="background:currentColor"></span>
                                {{ $isOccupied ? 'Occupied' : 'Available' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($isOccupied)
                                <div class="font-medium" style="color:var(--app-text)">{{ $member?->name }}</div>
                                @if ($canAssign)
                                    <button type="button"
                                            class="lk-link mt-1 text-xs font-semibold"
                                            data-action="reassign"
                                            data-locker-id="{{ $locker->id }}">
                                        Reassign
                                    </button>
                                @endif
                            @elseif ($canAssign && $locker->status === 'active')
                                <button type="button"
                                        class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold"
                                        style="background:color-mix(in srgb, var(--app-brand) 18%, transparent);color:var(--app-brand)"
                                        data-action="assign"
                                        data-locker-id="{{ $locker->id }}">
                                    + Assign
                                </button>
                            @else
                                <span class="text-sm" style="color:var(--app-text-muted)">— Unassigned —</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm" style="color:var(--app-text-muted)">
                            {{ $current?->from_date?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                  style="background:{{ $locker->status === 'active' ? 'color-mix(in srgb, #22c55e 14%, transparent)' : 'color-mix(in srgb, #94a3b8 14%, transparent)' }};color:{{ $locker->status === 'active' ? '#22c55e' : '#94a3b8' }}">
                                <span class="h-1.5 w-1.5 rounded-full" style="background:currentColor"></span>
                                {{ $statusOptions[$locker->status] ?? ucfirst($locker->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('tenant.lockers.show', $locker) }}"
                                   class="lk-icon-btn lk-icon-btn-view"
                                   title="View Details">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                @if ($canEdit)
                                    <a href="{{ route('tenant.lockers.show', $locker) }}"
                                       class="lk-icon-btn lk-icon-btn-edit"
                                       title="Edit Locker">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z"/></svg>
                                    </a>
                                @endif
                                @if ($canDelete)
                                    <button type="button"
                                            class="lk-icon-btn lk-icon-btn-delete"
                                            data-action="delete"
                                            data-locker-id="{{ $locker->id }}"
                                            data-locker-name="{{ $locker->locker_number }}"
                                            title="{{ $isOccupied ? 'Release this locker before deleting.' : 'Delete locker' }}"
                                            @disabled($isOccupied)>
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="lk-mobile space-y-3">
        @if ($lockers->isEmpty())
            <div class="app-panel rounded-[2rem] border px-6 py-14 text-center" style="border-color:var(--app-border)">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl" style="background:color-mix(in srgb, var(--app-brand) 12%, transparent);color:var(--app-brand)">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="6" y="3" width="12" height="18" rx="2"></rect>
                        <circle cx="12" cy="12" r="1.2"></circle>
                        <path d="M12 8v2"></path>
                    </svg>
                </div>
                @if(request()->hasAny(['search','availability','status']))
                    <p class="mt-4 text-base font-semibold" style="color:var(--app-text)">No lockers match these filters.</p>
                    <p class="mt-1 text-sm" style="color:var(--app-text-muted)">Try adjusting your search or clearing the filters.</p>
                    <a href="{{ route('tenant.lockers.index') }}" class="lk-empty-ghost mt-4 inline-flex">Clear all</a>
                @else
                    <p class="mt-4 text-base font-semibold" style="color:var(--app-text)">No lockers yet.</p>
                    <p class="mt-1 text-sm" style="color:var(--app-text-muted)">Add your first locker to start assigning members.</p>
                    @if ($canAdd)
                        <a href="{{ route('tenant.lockers.create') }}" class="lk-empty-primary mt-4 inline-flex">Add Locker</a>
                    @endif
                @endif
            </div>
        @else
        @foreach ($lockers as $locker)
            @php
                $current = $locker->currentAssignment;
                $member = $current?->member;
                $isOccupied = $locker->availability === 'occupied' && $current;
            @endphp
            <div class="app-panel rounded-2xl border p-4" style="border-color:var(--app-border)">
                <div class="flex items-start justify-between gap-3">
                    <a href="{{ route('tenant.lockers.show', $locker) }}" class="min-w-0 text-left">
                        <p class="truncate text-base font-semibold" style="color:var(--app-text)">{{ $locker->locker_number }}</p>
                        <p class="mt-1 text-xs" style="color:var(--app-text-muted)">{{ $locker->location ?: 'No location added' }}</p>
                    </a>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                              style="background:{{ $isOccupied ? 'color-mix(in srgb, #f59e0b 16%, transparent)' : 'color-mix(in srgb, #22c55e 16%, transparent)' }};color:{{ $isOccupied ? '#f59e0b' : '#22c55e' }}">
                            {{ $isOccupied ? 'Occupied' : 'Available' }}
                        </span>
                        <div class="inline-flex items-center gap-1">
                            <a href="{{ route('tenant.lockers.show', $locker) }}" class="lk-icon-btn lk-icon-btn-view" title="View Details">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            @if ($canEdit)
                                <a href="{{ route('tenant.lockers.show', $locker) }}" class="lk-icon-btn lk-icon-btn-edit" title="Edit Locker">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z"/></svg>
                                </a>
                            @endif
                            @if ($canDelete)
                                <button type="button"
                                        class="lk-icon-btn lk-icon-btn-delete"
                                        data-action="delete"
                                        data-locker-id="{{ $locker->id }}"
                                        data-locker-name="{{ $locker->locker_number }}"
                                        title="{{ $isOccupied ? 'Release this locker before deleting.' : 'Delete locker' }}"
                                        @disabled($isOccupied)>
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide" style="color:var(--app-text-muted)">Assigned to</p>
                        <p class="truncate text-sm font-medium" style="color:var(--app-text)">{{ $member?->name ?? '— Unassigned —' }}</p>
                    </div>
                    @if ($canAssign && $locker->status === 'active')
                        <button type="button"
                                class="rounded-full px-3 py-1.5 text-xs font-semibold"
                                style="background:color-mix(in srgb, var(--app-brand) 18%, transparent);color:var(--app-brand)"
                                data-action="{{ $isOccupied ? 'reassign' : 'assign' }}"
                                data-locker-id="{{ $locker->id }}">
                            {{ $isOccupied ? 'Reassign' : 'Assign' }}
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
        @endif
    </div>

    @if ($lockers->isNotEmpty())
        <div class="mt-4 flex flex-col items-center justify-between gap-3 rounded-[1.5rem] border px-5 py-3 sm:flex-row"
             style="border-color:var(--app-border);background:var(--app-panel)">
            <p class="text-xs" style="color:var(--app-text-muted)">
                Showing {{ $lockers->firstItem() }} to {{ $lockers->lastItem() }} of {{ number_format($lockers->total()) }} lockers
            </p>
            <div class="flex items-center gap-3">
                <select name="per_page"
                        onchange="window.location='{{ route('tenant.lockers.index') }}?'+new URLSearchParams({...Object.fromEntries(new URLSearchParams(window.location.search)),...{per_page:this.value,page:1}}).toString()"
                        class="rounded-xl border px-3 py-2 text-xs outline-none"
                        style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    @foreach ([10, 25, 50, 100] as $pp)
                        <option value="{{ $pp }}" @selected($lockers->perPage() === $pp)>{{ $pp }} / page</option>
                    @endforeach
                </select>
                {{ $lockers->links() }}
            </div>
        </div>
    @endif

    <div id="locker-modal" class="fixed inset-0 z-[220] hidden items-center justify-center bg-black/55 px-4">
        <div class="w-full max-w-xl rounded-[1.75rem] border p-6 shadow-2xl" style="background:var(--app-panel);border-color:var(--app-border)">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)" id="locker-modal-eyebrow">Locker</p>
                    <h3 class="mt-1 text-xl font-semibold" style="color:var(--app-text)" id="locker-modal-title">Assign Locker</h3>
                    <p class="mt-1 text-sm" style="color:var(--app-text-muted)" id="locker-modal-subtitle"></p>
                </div>
                <button type="button" id="locker-modal-close"
                        class="rounded-xl p-2 transition hover:opacity-80"
                        style="background:var(--app-panel-strong);color:var(--app-text-muted)">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div id="locker-modal-error" class="mt-4 hidden rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-300"></div>

            <form id="locker-modal-form" class="mt-5 space-y-4">
                <div id="locker-modal-current" class="hidden rounded-2xl border px-4 py-3 text-sm" style="border-color:var(--app-border);background:var(--app-panel-strong)"></div>

                <div>
                    <label class="lk-inline-label" id="locker-member-label">Member <span class="text-red-400">*</span></label>
                    <input type="text" id="locker-member-search" class="lk-inline-input" placeholder="Search by name / phone / member ID" autocomplete="off">
                    <div id="locker-member-results" class="mt-2 hidden rounded-2xl border p-2" style="border-color:var(--app-border);background:var(--app-panel-strong)"></div>
                    <input type="hidden" id="locker-member-id" name="member_id">
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="lk-inline-label">From date <span class="text-red-400">*</span></label>
                        <input type="date" id="locker-from-date" name="from_date" class="lk-inline-input" min="{{ today()->toDateString() }}" value="{{ today()->toDateString() }}" required>
                    </div>
                    <div>
                        <label class="lk-inline-label">To date</label>
                        <input type="date" id="locker-to-date" name="to_date" class="lk-inline-input" min="{{ today()->toDateString() }}">
                    </div>
                </div>

                <div>
                    <label class="lk-inline-label">Notes</label>
                    <textarea id="locker-assignment-notes" name="notes" rows="3" class="lk-inline-input" placeholder="Optional notes"></textarea>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button" id="locker-modal-cancel" class="rounded-2xl border px-4 py-2.5 text-sm font-medium" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        Cancel
                    </button>
                    <button type="submit" id="locker-modal-submit" class="rounded-2xl px-4 py-2.5 text-sm font-semibold" style="background:var(--app-brand);color:#0f172a">
                        Assign
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            .lk-desktop { display: none; }
            .lk-mobile { display: block; }
            .lk-empty-icon { align-items: center; background: color-mix(in srgb, var(--app-brand) 12%, transparent); border-radius: 1.5rem; color: var(--app-brand); display: inline-flex; height: 4.75rem; justify-content: center; width: 4.75rem; }
            .lk-empty-icon svg { height: 2.1rem; width: 2.1rem; }
            .lk-empty-primary, .lk-empty-ghost { align-items: center; border-radius: 0.9rem; display: inline-flex; font-size: 0.84rem; font-weight: 600; min-height: 3rem; padding: 0 1.1rem; text-decoration: none; }
            .lk-empty-primary { background: var(--app-brand); color: #0f172a; }
            .lk-empty-ghost { background: var(--app-panel-strong); border: 1px solid var(--app-border); color: var(--app-text-muted); }
            .lk-link { color: var(--app-brand); }
            .lk-inline-label { color: var(--app-text); display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.45rem; }
            .lk-inline-input { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 1rem; color: var(--app-text); font-size: 0.92rem; outline: none; padding: 0.85rem 1rem; width: 100%; }
            .lk-inline-input:focus { border-color: color-mix(in srgb, var(--app-brand) 60%, var(--app-border)); }
            .lk-history-row-current { background: color-mix(in srgb, var(--app-brand) 10%, transparent); }
            .lk-member-result { border-radius: 1rem; color: var(--app-text); cursor: pointer; display: block; padding: 0.75rem 0.85rem; transition: background 120ms; width: 100%; text-align: left; }
            .lk-member-result:hover { background: color-mix(in srgb, var(--app-border) 55%, transparent); }
            .lk-icon-btn { align-items: center; border-radius: 0.5rem; display: inline-flex; height: 2rem; justify-content: center; transition: background 140ms, color 140ms; width: 2rem; }
            .lk-icon-btn-view { background: color-mix(in srgb, #3b82f6 12%, transparent); color: #3b82f6; }
            .lk-icon-btn-view:hover { background: color-mix(in srgb, #3b82f6 22%, transparent); }
            .lk-icon-btn-edit { background: color-mix(in srgb, #f59e0b 12%, transparent); color: #f59e0b; }
            .lk-icon-btn-edit:hover { background: color-mix(in srgb, #f59e0b 22%, transparent); }
            .lk-icon-btn-delete { border: none; cursor: pointer; background: color-mix(in srgb, #ef4444 12%, transparent); color: #ef4444; }
            .lk-icon-btn-delete:hover:not(:disabled) { background: color-mix(in srgb, #ef4444 22%, transparent); }
            .lk-icon-btn-delete:disabled { cursor: not-allowed; opacity: 0.35; }
            @media (min-width: 900px) {
                .lk-desktop { display: block; }
                .lk-mobile { display: none; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (() => {
                const csrf = @json(csrf_token());
                const canAssign = @json($canAssign);
                const canEdit = @json($canEdit);
                const canDelete = @json($canDelete);
                const routes = {
                    details: @json(route('tenant.lockers.details', ['locker' => '__LOCKER__'])),
                    destroy: @json(route('tenant.lockers.destroy', ['locker' => '__LOCKER__'])),
                    assign: @json(route('tenant.lockers.assign', ['locker' => '__LOCKER__'])),
                    reassign: @json(route('tenant.lockers.reassign', ['locker' => '__LOCKER__'])),
                    memberSearch: @json(route('tenant.lockers.member-search')),
                };

                const modal = document.getElementById('locker-modal');
                const modalClose = document.getElementById('locker-modal-close');
                const modalCancel = document.getElementById('locker-modal-cancel');
                const modalForm = document.getElementById('locker-modal-form');
                const modalTitle = document.getElementById('locker-modal-title');
                const modalEyebrow = document.getElementById('locker-modal-eyebrow');
                const modalSubtitle = document.getElementById('locker-modal-subtitle');
                const modalError = document.getElementById('locker-modal-error');
                const modalSubmit = document.getElementById('locker-modal-submit');
                const modalCurrent = document.getElementById('locker-modal-current');
                const memberSearchInput = document.getElementById('locker-member-search');
                const memberResults = document.getElementById('locker-member-results');
                const memberIdInput = document.getElementById('locker-member-id');
                const memberLabel = document.getElementById('locker-member-label');
                const fromDateInput = document.getElementById('locker-from-date');
                const toDateInput = document.getElementById('locker-to-date');
                const notesInput = document.getElementById('locker-assignment-notes');

                let activeLockerId = null;
                let activeLockerData = null;
                let modalMode = 'assign';

                const lockerUrl = (template, lockerId) => template.replace('__LOCKER__', lockerId);
                const escapeHtml = (value) => String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');

                async function fetchJson(url, options = {}) {
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            ...(options.headers || {}),
                        },
                        ...options,
                    });

                    if (response.status === 422) {
                        const payload = await response.json();
                        const message = Object.values(payload.errors || {}).flat()[0] || 'Please check the form and try again.';
                        throw new Error(message);
                    }

                    if (!response.ok) {
                        throw new Error('Unable to complete the request right now.');
                    }

                    return response.json();
                }

                async function loadLocker(lockerId) {
                    activeLockerId = lockerId;
                    activeLockerData = await fetchJson(lockerUrl(routes.details, lockerId));
                }

                function openModal(mode, locker) {
                    modalMode = mode;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modalError.classList.add('hidden');
                    modalError.textContent = '';
                    memberResults.classList.add('hidden');
                    memberResults.innerHTML = '';
                    memberIdInput.value = '';
                    memberSearchInput.value = '';
                    notesInput.value = '';
                    fromDateInput.value = @json(today()->toDateString());
                    toDateInput.value = '';

                    if (mode === 'assign') {
                        modalEyebrow.textContent = 'Locker assignment';
                        modalTitle.textContent = `Assign Locker - ${locker.locker_number}`;
                        modalSubtitle.textContent = 'Choose an active member and assignment dates.';
                        modalSubmit.textContent = 'Assign';
                        memberLabel.innerHTML = 'Member <span class="text-red-400">*</span>';
                        modalCurrent.classList.add('hidden');
                    } else {
                        modalEyebrow.textContent = 'Locker reassignment';
                        modalTitle.textContent = `Reassign Locker - ${locker.locker_number}`;
                        modalSubtitle.textContent = 'Move this locker to a different member.';
                        modalSubmit.textContent = 'Reassign';
                        memberLabel.innerHTML = 'New member <span class="text-red-400">*</span>';
                        modalCurrent.classList.remove('hidden');
                        modalCurrent.innerHTML = `
                            <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Currently assigned to</p>
                            <p class="mt-1 font-semibold" style="color:var(--app-text)">${escapeHtml(locker.current_assignment?.member_name || '—')}</p>
                            <p class="mt-1 text-sm" style="color:var(--app-text-muted)">Since ${escapeHtml(locker.current_assignment?.from_date || '—')}</p>
                        `;
                    }
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                async function submitAssignmentForm(event) {
                    event.preventDefault();
                    if (!activeLockerId || !activeLockerData) {
                        return;
                    }

                    const payload = {
                        from_date: fromDateInput.value,
                        to_date: toDateInput.value || null,
                        notes: notesInput.value || null,
                    };

                    if (modalMode === 'assign') {
                        payload.member_id = memberIdInput.value;
                    } else {
                        payload.new_member_id = memberIdInput.value;
                    }

                    try {
                        modalSubmit.disabled = true;
                        await fetchJson(lockerUrl(modalMode === 'assign' ? routes.assign : routes.reassign, activeLockerId), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload),
                        });
                        window.location.reload();
                    } catch (error) {
                        modalError.textContent = error.message;
                        modalError.classList.remove('hidden');
                    } finally {
                        modalSubmit.disabled = false;
                    }
                }

                async function deleteLocker(lockerId, lockerName) {
                    if (!window.confirm(`This will permanently delete locker "${lockerName}" and its full usage history. Continue?`)) {
                        return;
                    }

                    try {
                        await fetchJson(lockerUrl(routes.destroy, lockerId), { method: 'DELETE' });
                        window.location.reload();
                    } catch (error) {
                        alert(error.message);
                    }
                }

                let memberSearchTimer = null;
                memberSearchInput.addEventListener('input', () => {
                    memberIdInput.value = '';
                    if (memberSearchTimer) {
                        clearTimeout(memberSearchTimer);
                    }

                    const term = memberSearchInput.value.trim();
                    if (term.length < 2) {
                        memberResults.classList.add('hidden');
                        memberResults.innerHTML = '';
                        return;
                    }

                    memberSearchTimer = setTimeout(async () => {
                        try {
                            const results = await fetchJson(`${routes.memberSearch}?q=${encodeURIComponent(term)}`);
                            memberResults.innerHTML = results.length
                                ? results.map(member => `
                                    <button type="button" class="lk-member-result" data-member-id="${member.id}" data-member-label="${escapeHtml(member.name)} · ${escapeHtml(member.member_code || '')} · ${escapeHtml(member.phone || '')}">
                                        <div class="font-semibold">${escapeHtml(member.name)}</div>
                                        <div class="mt-1 text-xs" style="color:var(--app-text-muted)">${escapeHtml(member.member_code || '—')} · ${escapeHtml(member.phone || '—')}</div>
                                    </button>
                                `).join('')
                                : `<p class="px-3 py-2 text-sm" style="color:var(--app-text-muted)">No active members found.</p>`;
                            memberResults.classList.remove('hidden');
                        } catch (error) {
                            memberResults.innerHTML = `<p class="px-3 py-2 text-sm text-red-300">${escapeHtml(error.message)}</p>`;
                            memberResults.classList.remove('hidden');
                        }
                    }, 220);
                });

                memberResults.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-member-id]');
                    if (!button) {
                        return;
                    }

                    memberIdInput.value = button.dataset.memberId;
                    memberSearchInput.value = button.dataset.memberLabel;
                    memberResults.classList.add('hidden');
                });

                document.addEventListener('click', (event) => {
                    const actionButton = event.target.closest('[data-action]');
                    if (actionButton) {
                        event.preventDefault();
                        event.stopPropagation();

                        const action = actionButton.dataset.action;
                        const lockerId = actionButton.dataset.lockerId;
                        const lockerName = actionButton.dataset.lockerName;

                        if (action === 'view') {
                            window.location.href = lockerUrl(@json(route('tenant.lockers.show', ['locker' => '__LOCKER__'])), lockerId);
                        } else if (action === 'delete') {
                            deleteLocker(lockerId, lockerName || 'this locker');
                        } else if (action === 'assign' || action === 'reassign') {
                            loadLocker(lockerId).then(() => {
                                if (activeLockerData) {
                                    openModal(action, activeLockerData);
                                }
                            });
                        }

                        return;
                    }
                });

                modalForm.addEventListener('submit', submitAssignmentForm);
                modalClose.addEventListener('click', closeModal);
                modalCancel.addEventListener('click', closeModal);

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeModal();
                    }
                });
            })();
        </script>
    @endpush
</x-layouts.admin>
