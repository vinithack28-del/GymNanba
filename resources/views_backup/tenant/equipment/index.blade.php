@php
    $types    = App\Models\Equipment::TYPES;
    $statuses = App\Models\Equipment::STATUSES;
    $serviceTypes = App\Models\EquipmentServiceRecord::TYPES;
@endphp

<x-layouts.admin
    title="Equipment"
    eyebrow="Operations"
    heading="Equipment"
    subheading="Track gym equipment, status, and maintenance history."
>
    @if ($canAdd)
        <x-slot:headerAction>
            <a href="{{ route('tenant.equipment.create') }}"
                class="inline-flex items-center gap-2 rounded-full bg-[var(--app-brand)] px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:opacity-90">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add Equipment
            </a>
        </x-slot:headerAction>
    @endif

    {{-- ── Summary cards ──────────────────────────────────────────────────────── --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4" id="eq-summary">
        @foreach ([
            ['key'=>'total',       'label'=>'Total Equipment', 'color'=>'var(--app-text)',    'val'=>$summary['total']],
            ['key'=>'operational', 'label'=>'Operational',     'color'=>'#22c55e',            'val'=>$summary['operational']],
            ['key'=>'maintenance', 'label'=>'Maintenance',     'color'=>'#f59e0b',            'val'=>$summary['maintenance']],
            ['key'=>'broken',      'label'=>'Broken',          'color'=>'#ef4444',            'val'=>$summary['broken']],
        ] as $card)
            <div class="app-panel rounded-2xl border p-4">
                <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">{{ $card['label'] }}</p>
                <p class="mt-1 text-3xl font-bold" style="color:{{ $card['color'] }}" id="eq-count-{{ $card['key'] }}">{{ $card['val'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────────────── --}}
    <form method="GET" class="mb-4 flex flex-wrap items-center gap-3">
        <div class="flex min-w-[200px] flex-1 items-center gap-2 rounded-xl border px-3 py-2.5"
             style="background:var(--app-panel-strong);border-color:var(--app-border)">
            <svg class="h-4 w-4 shrink-0" style="color:var(--app-text-muted)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name, brand, model…"
                   class="w-full bg-transparent text-sm outline-none"
                   style="color:var(--app-text)">
        </div>
        <select name="type" onchange="this.form.submit()"
                class="rounded-xl border px-3 py-2.5 text-sm outline-none"
                style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
            <option value="">All Types</option>
            @foreach ($types as $val => $lbl)
                <option value="{{ $val }}" @selected(request('type') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()"
                class="rounded-xl border px-3 py-2.5 text-sm outline-none"
                style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
            <option value="">All Status</option>
            @foreach ($statuses as $val => $lbl)
                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        @if (request()->hasAny(['search','type','status']))
            <a href="{{ route('tenant.equipment.index') }}"
               class="rounded-xl border px-3 py-2.5 text-sm font-medium transition hover:opacity-80"
               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text-muted)">
                Clear
            </a>
        @endif
    </form>

    {{-- ── Equipment table (desktop) ───────────────────────────────────────────── --}}
    <div class="eq-desktop-list app-panel rounded-2xl border">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:var(--app-panel-strong);border-bottom:1px solid var(--app-border)">
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Equipment</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Brand</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Purchase Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Warranty</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">Actions</th>
                </tr>
            </thead>
            <tbody id="eq-table-body">
                @forelse ($equipment as $item)
                    <tr class="eq-row cursor-pointer border-t transition hover:opacity-90"
                        style="border-color:var(--app-border);background:var(--app-panel)"
                        data-id="{{ $item->id }}">
                        <td class="px-4 py-3 font-medium" style="color:var(--app-text)">{{ $item->name }}</td>
                        <td class="px-4 py-3 text-xs">
                            <span class="rounded-full px-2.5 py-1 font-semibold"
                                  style="background:var(--app-panel-strong);color:var(--app-text-muted)">
                                {{ $types[$item->type] ?? $item->type }}
                            </span>
                        </td>
                        <td class="px-4 py-3" style="color:var(--app-text-muted)">{{ $item->brand ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php $statusColors = ['operational'=>'#22c55e','maintenance'=>'#f59e0b','broken'=>'#ef4444']; @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                  style="background:color-mix(in srgb, {{ $statusColors[$item->status] ?? '#888' }} 15%, transparent);color:{{ $statusColors[$item->status] ?? 'var(--app-text)' }}">
                                <span class="h-1.5 w-1.5 rounded-full" style="background:currentColor"></span>
                                {{ $statuses[$item->status] ?? $item->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm" style="color:var(--app-text-muted)">{{ $item->location ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm" style="color:var(--app-text-muted)">
                            {{ $item->purchase_date?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if ($item->warranty_expiry)
                                <span style="color:{{ $item->isWarrantyExpired() ? '#ef4444' : 'var(--app-text-muted)' }}">
                                    {{ $item->warranty_expiry->format('d M Y') }}
                                    @if ($item->isWarrantyExpired()) <span class="text-xs">(expired)</span> @endif
                                </span>
                            @else
                                <span style="color:var(--app-text-muted)">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1">
                                {{-- View --}}
                                <button type="button"
                                    class="eq-view-btn inline-flex items-center justify-center rounded-lg p-1.5 transition hover:opacity-80"
                                    style="color:var(--app-text-muted)"
                                    data-id="{{ $item->id }}"
                                    title="View details">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                {{-- Edit --}}
                                @if ($canEdit)
                                    <button type="button"
                                        class="eq-edit-btn inline-flex items-center justify-center rounded-lg p-1.5 transition hover:opacity-80"
                                        style="color:var(--app-text-muted)"
                                        data-id="{{ $item->id }}"
                                        title="Edit">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                @endif
                                {{-- Delete --}}
                                @if ($canDelete)
                                    <button type="button"
                                        class="eq-delete-btn inline-flex items-center justify-center rounded-lg p-1.5 transition hover:opacity-80"
                                        style="color:var(--app-text-muted)"
                                        data-id="{{ $item->id }}"
                                        data-name="{{ $item->name }}"
                                        title="Delete">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div style="display:flex;flex-direction:column;align-items:center;padding:4rem 1rem;text-align:center">
                                <div style="width:4.5rem;height:4.5rem;border-radius:999px;background:var(--app-panel-strong);border:1px solid var(--app-border);display:flex;align-items:center;justify-content:center;margin-bottom:1.25rem;color:var(--app-text-muted)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:1.8rem;height:1.8rem">
                                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                                    </svg>
                                </div>
                                <p style="font-size:1.05rem;font-weight:700;color:var(--app-text)">No equipment found</p>
                                <p style="font-size:.85rem;color:var(--app-text-muted);margin-top:.4rem;max-width:22rem">
                                    @if(request()->hasAny(['search','status','type','branch_id']))
                                        No equipment matches your current filters. Try adjusting or clearing them.
                                    @else
                                        Start tracking your gym equipment — add your first item to get started.
                                    @endif
                                </p>
                                <div style="display:flex;gap:.75rem;margin-top:1.25rem;flex-wrap:wrap;justify-content:center">
                                    @if(request()->hasAny(['search','status','type','branch_id']))
                                        <a href="{{ route('tenant.equipment.index') }}"
                                           style="border:1px solid var(--app-border);background:transparent;color:var(--app-text);border-radius:.6rem;padding:.5rem 1rem;font-size:.85rem;font-weight:600;text-decoration:none">
                                            Clear Filters
                                        </a>
                                    @endif
                                    @if($canAdd)
                                        <a href="{{ route('tenant.equipment.create') }}"
                                           style="border:none;background:var(--app-brand);color:#0f172a;border-radius:.6rem;padding:.5rem 1.1rem;font-size:.85rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:.9rem;height:.9rem"><path d="M12 5v14M5 12h14"/></svg>
                                            Add Equipment
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Equipment card list (mobile) ────────────────────────────────────────── --}}
    <div class="eq-mobile-list space-y-3" id="eq-card-list">
        @forelse ($equipment as $item)
            @php $statusColors = ['operational'=>'#22c55e','maintenance'=>'#f59e0b','broken'=>'#ef4444']; @endphp
            <div class="eq-row app-panel cursor-pointer rounded-2xl border p-4 transition active:opacity-80"
                 style="border-color:var(--app-border)" data-id="{{ $item->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate font-semibold" style="color:var(--app-text)">{{ $item->name }}</p>
                        <p class="mt-0.5 text-xs" style="color:var(--app-text-muted)">{{ $item->brand ?? '' }}{{ $item->brand && $item->location ? ' · ' : '' }}{{ $item->location ?? '' }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1.5">
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                              style="background:color-mix(in srgb, {{ $statusColors[$item->status] ?? '#888' }} 15%, transparent);color:{{ $statusColors[$item->status] ?? 'var(--app-text)' }}">
                            {{ $statuses[$item->status] ?? $item->status }}
                        </span>
                        @if ($canEdit)
                            <button type="button" class="eq-edit-btn p-1"
                                    style="color:var(--app-text-muted)"
                                    data-id="{{ $item->id }}" title="Edit">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                        @endif
                        @if ($canDelete)
                            <button type="button" class="eq-delete-btn p-1"
                                    style="color:var(--app-text-muted)"
                                    data-id="{{ $item->id }}" data-name="{{ $item->name }}" title="Delete">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium"
                          style="background:var(--app-panel-strong);color:var(--app-text-muted)">
                        {{ $types[$item->type] ?? $item->type }}
                    </span>
                </div>
            </div>
        @empty
            <div style="display:flex;flex-direction:column;align-items:center;padding:3rem 1rem;text-align:center">
                <div style="width:3.5rem;height:3.5rem;border-radius:999px;background:var(--app-panel-strong);border:1px solid var(--app-border);display:flex;align-items:center;justify-content:center;margin-bottom:1rem;color:var(--app-text-muted)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:1.4rem;height:1.4rem">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                </div>
                <p style="font-size:.95rem;font-weight:700;color:var(--app-text)">No equipment found</p>
                <p style="font-size:.8rem;color:var(--app-text-muted);margin-top:.35rem">
                    @if(request()->hasAny(['search','status','type','branch_id']))
                        Try adjusting your filters.
                    @else
                        Add your first equipment item to get started.
                    @endif
                </p>
                @if($canAdd && !request()->hasAny(['search','status','type','branch_id']))
                    <a href="{{ route('tenant.equipment.create') }}"
                       style="margin-top:1rem;border:none;background:var(--app-brand);color:#0f172a;border-radius:.6rem;padding:.45rem 1rem;font-size:.82rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:.85rem;height:.85rem"><path d="M12 5v14M5 12h14"/></svg>
                        Add Equipment
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         SLIDE-IN DETAIL PANEL
         ════════════════════════════════════════════════════════════════════════ --}}
    <div id="eq-overlay" class="fixed inset-0 z-[200] hidden bg-black/40 backdrop-blur-sm transition-opacity"></div>

    <div id="eq-panel"
         class="fixed inset-y-0 right-0 z-[201] flex w-full flex-col shadow-2xl transition-transform duration-300 translate-x-full sm:w-[480px] md:w-[540px]"
         style="background:var(--app-panel);border-left:1px solid var(--app-border)"
         role="dialog" aria-modal="true" aria-label="Equipment details">

        {{-- Panel header --}}
        <div class="flex shrink-0 items-center justify-between border-b px-5 py-4"
             style="border-color:var(--app-border)">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)" id="panel-mode-label">Details</p>
                <h2 class="mt-0.5 text-lg font-bold" style="color:var(--app-text)" id="panel-title">—</h2>
            </div>
            <button type="button" id="panel-close"
                    class="rounded-xl p-2 transition hover:opacity-80"
                    style="background:var(--app-panel-strong);color:var(--app-text-muted)">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Panel scrollable body --}}
        <div class="flex-1 overflow-y-auto px-5 py-5" id="panel-body">
            <p class="text-sm" style="color:var(--app-text-muted)">Loading…</p>
        </div>

        {{-- Panel footer --}}
        <div class="shrink-0 border-t px-5 py-4" style="border-color:var(--app-border)">
            <div id="panel-footer-view" class="flex items-center gap-3">
                @if ($canEdit)
                    <button type="button" id="btn-edit" class="flex-1 rounded-2xl border px-4 py-3 text-sm font-semibold transition hover:opacity-80"
                            style="border-color:var(--app-border);background:var(--app-panel-strong);color:var(--app-text)">
                        Edit
                    </button>
                @endif
            </div>
            <div id="panel-footer-edit" class="hidden flex items-center gap-3">
                <button type="button" id="btn-cancel-edit"
                        class="flex-1 rounded-2xl border px-4 py-3 text-sm font-medium transition hover:opacity-80"
                        style="border-color:var(--app-border);background:var(--app-panel-strong);color:var(--app-text)">
                    Cancel
                </button>
                <button type="button" id="btn-save-edit"
                        class="flex-1 rounded-2xl bg-[var(--app-brand)] px-4 py-3 text-sm font-semibold text-slate-950 transition hover:opacity-90">
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         DELETE CONFIRMATION MODAL
         ════════════════════════════════════════════════════════════════════════ --}}
    <div id="del-modal-overlay" class="fixed inset-0 z-[300] hidden items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="app-panel w-full max-w-sm rounded-[2rem] border p-6 shadow-2xl" style="border-color:var(--app-border)">
            <h2 class="text-lg font-bold" style="color:var(--app-text)">Are you sure?</h2>
            <p class="mt-2 text-sm" style="color:var(--app-text-muted)" id="del-modal-msg">
                This action cannot be undone.
            </p>
            <div class="mt-6 flex items-center gap-3">
                <button type="button" id="del-cancel"
                        class="flex-1 rounded-2xl border px-4 py-3 text-sm font-medium transition hover:opacity-80"
                        style="border-color:var(--app-border);background:var(--app-panel-strong);color:var(--app-text)">
                    Cancel
                </button>
                <button type="button" id="del-confirm"
                        class="flex-1 rounded-2xl bg-red-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-400">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const BASE_URL = '{{ rtrim(route("tenant.equipment.index"), "/") }}';
        const CAN_ADD     = {{ $canAdd ? 'true' : 'false' }};
        const CAN_EDIT    = {{ $canEdit ? 'true' : 'false' }};
        const CAN_DELETE  = {{ $canDelete ? 'true' : 'false' }};
        const CAN_SERVICE = {{ $canServiceRecord ? 'true' : 'false' }};

        const statusColors = { operational: '#22c55e', maintenance: '#f59e0b', broken: '#ef4444' };
        const statusLabels = @json($statuses);
        const typeLabels   = @json($types);
        const svcTypeLabels = @json($serviceTypes);

        let currentEquipmentId = null;
        let panelMode = 'view'; // 'view' | 'edit'
        let panelData = null;
        let panelOpen = false;

        // ── DOM refs ──────────────────────────────────────────────────────────
        const overlay       = document.getElementById('eq-overlay');
        const panel         = document.getElementById('eq-panel');
        const panelBody     = document.getElementById('panel-body');
        const panelTitle    = document.getElementById('panel-title');
        const panelModeLabel= document.getElementById('panel-mode-label');
        const footerView    = document.getElementById('panel-footer-view');
        const footerEdit    = document.getElementById('panel-footer-edit');
        const btnEdit       = document.getElementById('btn-edit');
        const btnCancelEdit = document.getElementById('btn-cancel-edit');
        const btnSaveEdit   = document.getElementById('btn-save-edit');
        const delModal      = document.getElementById('del-modal-overlay');

        // Mount overlays at the document root so they are never constrained by
        // the page layout, sidebar, or content containers.
        [overlay, panel, delModal].forEach(el => {
            if (el && el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
        });

        // ── Panel show/hide helpers ───────────────────────────────────────────
        // Use inline style.transform so hiding works regardless of whether Tailwind
        // includes translate-y-full in the generated CSS (it gets purged when only
        // added via JS and never appears in static HTML).
        function _hidePanelTransform() {
            panel.classList.remove('translate-x-full');
            panel.style.transform = window.innerWidth < 640 ? 'translateY(100%)' : 'translateX(100%)';
        }
        function _showPanelTransform() {
            panel.classList.remove('translate-x-full');
            panel.style.removeProperty('transform');
        }

        // ── Panel open/close ──────────────────────────────────────────────────
        function openPanel(id) {
            currentEquipmentId = id;
            panelMode = 'view';
            panelOpen = true;
            _showPanelTransform();
            overlay.classList.remove('hidden');
            panelBody.innerHTML = '<p class="text-sm" style="color:var(--app-text-muted)">Loading…</p>';
            footerView.classList.remove('hidden');
            footerEdit.classList.add('hidden');
            panelModeLabel.textContent = 'Details';
            fetchDetails(id);
        }

        function openPanelEdit(id) {
            currentEquipmentId = id;
            panelMode = 'edit';
            panelOpen = true;
            _showPanelTransform();
            overlay.classList.remove('hidden');
            panelBody.innerHTML = '<p class="text-sm" style="color:var(--app-text-muted)">Loading…</p>';
            footerView.classList.add('hidden');
            footerEdit.classList.remove('hidden');
            panelModeLabel.textContent = 'Edit Equipment';
            fetchDetails(id, true);
        }

        function closePanel() {
            panelOpen = false;
            _hidePanelTransform();
            overlay.classList.add('hidden');
            currentEquipmentId = null;
        }

        document.getElementById('panel-close').addEventListener('click', closePanel);
        overlay.addEventListener('click', closePanel);

        // ── Row / action button clicks ────────────────────────────────────────
        document.addEventListener('click', function (e) {
            const delBtn  = e.target.closest('.eq-delete-btn');
            const editBtn = e.target.closest('.eq-edit-btn');
            const viewBtn = e.target.closest('.eq-view-btn');
            const row     = e.target.closest('.eq-row');

            if (delBtn)  { e.stopPropagation(); triggerDelete(delBtn); return; }
            if (editBtn) { e.stopPropagation(); openPanelEdit(editBtn.dataset.id); return; }
            if (viewBtn) { e.stopPropagation(); openPanel(viewBtn.dataset.id); return; }
            if (row)     { openPanel(row.dataset.id); }
        });

        // ── Fetch details ─────────────────────────────────────────────────────
        async function fetchDetails(id, openInEdit = false) {
            try {
                const res = await fetch(`${BASE_URL}/${id}/details`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
                });
                if (!res.ok) throw new Error('Failed');
                panelData = await res.json();
                if (openInEdit) {
                    renderEdit(panelData);
                } else {
                    renderView(panelData);
                }
            } catch {
                panelBody.innerHTML = '<p class="text-sm text-red-400">Could not load details. Please try again.</p>';
            }
        }

        // ── Render view mode ──────────────────────────────────────────────────
        function renderView(d) {
            panelTitle.textContent = d.name;
            panelModeLabel.textContent = 'Equipment Details';

            const statusColor = statusColors[d.status] || '#888';
            const statusBg    = `color-mix(in srgb, ${statusColor} 15%, transparent)`;

            let html = `
                <div class="space-y-5">
                    <!-- Status + type badges -->
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                              style="background:${statusBg};color:${statusColor}">
                            <span class="h-1.5 w-1.5 rounded-full" style="background:currentColor"></span>
                            ${statusLabels[d.status] ?? d.status}
                        </span>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold"
                              style="background:var(--app-panel-strong);color:var(--app-text-muted)">
                            ${typeLabels[d.type] ?? d.type}
                        </span>
                    </div>

                    <!-- Details grid -->
                    <div class="grid grid-cols-2 gap-x-4 gap-y-3 rounded-2xl border p-4"
                         style="background:var(--app-panel-strong);border-color:var(--app-border)">
                        ${detailRow('Brand', d.brand)}
                        ${detailRow('Model', d.model)}
                        ${detailRow('Location', d.location)}
                        ${detailRow('Branch', d.branch_name)}
                        ${detailRow('Purchase Date', d.purchase_date_fmt)}
                        ${detailRow('Purchase Price', d.purchase_price_fmt)}
                        ${detailRow('Warranty Expiry',
                            d.warranty_expiry_fmt
                                ? `<span style="color:${d.warranty_expired ? '#ef4444' : 'var(--app-text)'}">
                                    ${d.warranty_expiry_fmt}${d.warranty_expired ? ' <em class="text-xs not-italic">(expired)</em>' : ''}
                                   </span>`
                                : null
                        )}
                        ${detailRow('Added', d.created_at)}
                    </div>
                    ${d.notes ? `<p class="rounded-2xl border p-4 text-sm" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text-muted)">${escHtml(d.notes)}</p>` : ''}
                </div>`;

            // Service history section
            html += renderServiceSection(d);

            panelBody.innerHTML = html;
            bindServiceForm();
        }

        function detailRow(label, value) {
            if (!value && value !== 0) return '';
            return `<div>
                <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--app-text-muted)">${label}</p>
                <p class="mt-0.5 text-sm font-medium" style="color:var(--app-text)">${value}</p>
            </div>`;
        }

        function renderServiceSection(d) {
            let html = `<div class="mt-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold" style="color:var(--app-text)">Service History</h3>
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold"
                          style="background:var(--app-panel-strong);color:var(--app-text-muted)">${d.service_records.length}</span>
                </div>`;

            if (CAN_SERVICE) {
                html += `
                <div id="svc-form-wrap" class="mt-3 rounded-2xl border p-4" style="background:var(--app-panel-strong);border-color:var(--app-border)">
                    <p class="mb-3 text-sm font-semibold" style="color:var(--app-text)">Add Service Record</p>
                    <p id="svc-error" class="mb-2 hidden rounded-xl border border-red-400/20 bg-red-500/10 px-3 py-2 text-xs text-red-400"></p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium" style="color:var(--app-text-muted)">Service Date <span class="text-red-400">*</span></label>
                            <input type="date" id="svc-date" value="${todayStr()}" max="${todayStr()}"
                                   class="w-full rounded-xl border px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                   style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium" style="color:var(--app-text-muted)">Service Type <span class="text-red-400">*</span></label>
                            <select id="svc-type"
                                    class="w-full rounded-xl border px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                    style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
                                <option value="">Select type…</option>
                                ${Object.entries(svcTypeLabels).map(([v,l]) => `<option value="${v}">${l}</option>`).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium" style="color:var(--app-text-muted)">Cost (₹) <span class="text-red-400">*</span></label>
                            <input type="number" id="svc-cost" min="0" step="1" placeholder="0"
                                   class="w-full rounded-xl border px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                   style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium" style="color:var(--app-text-muted)">Service Provider</label>
                            <input type="text" id="svc-provider" maxlength="200" placeholder="Company / technician"
                                   class="w-full rounded-xl border px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                   style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-medium" style="color:var(--app-text-muted)">Notes</label>
                            <textarea id="svc-notes" rows="2" maxlength="1000" placeholder="Details about the service…"
                                      class="w-full rounded-xl border px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                      style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text);resize:vertical"></textarea>
                        </div>
                    </div>
                    <button type="button" id="svc-submit"
                            class="mt-3 w-full rounded-xl bg-[var(--app-brand)] px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:opacity-90">
                        Add Service Record
                    </button>
                </div>`;
            }

            // History list
            html += `<div class="mt-3 space-y-2" id="svc-list">`;
            if (d.service_records.length === 0) {
                html += `<p class="py-4 text-center text-xs" style="color:var(--app-text-muted)">No service records yet.</p>`;
            } else {
                d.service_records.forEach(r => { html += serviceRecordCard(r); });
            }
            html += `</div></div>`;
            return html;
        }

        function serviceRecordCard(r) {
            return `<div class="rounded-2xl border p-3" style="background:var(--app-panel-strong);border-color:var(--app-border)" data-record-id="${r.id}">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold" style="color:var(--app-text)">${escHtml(r.service_date_fmt)}</span>
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                  style="background:var(--app-panel);border:1px solid var(--app-border);color:var(--app-text-muted)">
                                ${escHtml(r.service_type_label)}
                            </span>
                            <span class="font-semibold text-xs" style="color:var(--app-text)">${escHtml(r.cost_fmt)}</span>
                        </div>
                        ${r.service_provider ? `<p class="mt-1 text-xs" style="color:var(--app-text-muted)">Provider: ${escHtml(r.service_provider)}</p>` : ''}
                        ${r.notes ? `<p class="mt-1 text-xs" style="color:var(--app-text-muted)">${escHtml(r.notes)}</p>` : ''}
                    </div>
                    ${CAN_SERVICE ? `<button type="button" class="svc-delete-btn shrink-0 rounded-lg p-1 transition hover:opacity-70"
                            style="color:var(--app-text-muted)" data-record-id="${r.id}">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                    </button>` : ''}
                </div>
            </div>`;
        }

        // ── Render edit mode ──────────────────────────────────────────────────
        function renderEdit(d) {
            panelModeLabel.textContent = 'Edit Equipment';
            panelTitle.textContent = d.name;

            panelBody.innerHTML = `
                <div class="space-y-4">
                    <p id="edit-error" class="hidden rounded-xl border border-red-400/20 bg-red-500/10 px-3 py-2 text-sm text-red-400"></p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium" style="color:var(--app-text)">Equipment Name <span class="text-red-400">*</span></label>
                            <input type="text" id="edit-name" value="${escAttr(d.name)}" maxlength="150"
                                   class="w-full rounded-2xl border px-4 py-3 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                   style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" style="color:var(--app-text)">Type <span class="text-red-400">*</span></label>
                            <select id="edit-type"
                                    class="w-full rounded-2xl border px-4 py-3 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                    style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                ${Object.entries(typeLabels).map(([v,l]) => `<option value="${v}"${d.type===v?' selected':''}>${l}</option>`).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" style="color:var(--app-text)">Status <span class="text-red-400">*</span></label>
                            <select id="edit-status"
                                    class="w-full rounded-2xl border px-4 py-3 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                    style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                ${Object.entries(statusLabels).map(([v,l]) => `<option value="${v}"${d.status===v?' selected':''}>${l}</option>`).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" style="color:var(--app-text)">Brand</label>
                            <input type="text" id="edit-brand" value="${escAttr(d.brand??'')}" maxlength="100"
                                   class="w-full rounded-2xl border px-4 py-3 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                   style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" style="color:var(--app-text)">Model</label>
                            <input type="text" id="edit-model" value="${escAttr(d.model??'')}" maxlength="100"
                                   class="w-full rounded-2xl border px-4 py-3 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                   style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" style="color:var(--app-text)">Purchase Date</label>
                            <input type="date" id="edit-purchase-date" value="${d.purchase_date??''}" max="${todayStr()}"
                                   class="w-full rounded-2xl border px-4 py-3 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                   style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" style="color:var(--app-text)">Warranty Expiry</label>
                            <input type="date" id="edit-warranty" value="${d.warranty_expiry??''}"
                                   class="w-full rounded-2xl border px-4 py-3 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                   style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" style="color:var(--app-text)">Purchase Price (₹)</label>
                            <input type="number" id="edit-price" min="0" step="1"
                                   value="${d.purchase_price_paise != null ? d.purchase_price_paise / 100 : ''}"
                                   class="w-full rounded-2xl border px-4 py-3 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                   style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium" style="color:var(--app-text)">Location</label>
                            <input type="text" id="edit-location" value="${escAttr(d.location??'')}" maxlength="200"
                                   class="w-full rounded-2xl border px-4 py-3 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                   style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium" style="color:var(--app-text)">Notes</label>
                            <textarea id="edit-notes" rows="3" maxlength="1000"
                                      class="w-full rounded-2xl border px-4 py-3 text-sm outline-none focus:ring-1 focus:ring-[var(--app-brand)]"
                                      style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text);resize:vertical"
                                      >${escHtml(d.notes??'')}</textarea>
                        </div>
                    </div>
                </div>`;
        }

        // ── Edit actions ──────────────────────────────────────────────────────
        btnEdit?.addEventListener('click', () => {
            if (!CAN_EDIT) return;
            if (!panelData) return;
            panelMode = 'edit';
            footerView.classList.add('hidden');
            footerEdit.classList.remove('hidden');
            renderEdit(panelData);
        });

        btnCancelEdit?.addEventListener('click', () => {
            panelMode = 'view';
            footerView.classList.remove('hidden');
            footerEdit.classList.add('hidden');
            renderView(panelData);
        });

        btnSaveEdit?.addEventListener('click', async () => {
            const name   = document.getElementById('edit-name')?.value.trim();
            const type   = document.getElementById('edit-type')?.value;
            const errEl  = document.getElementById('edit-error');

            if (!name || !type) {
                errEl.textContent = 'Please fill in all required fields';
                errEl.classList.remove('hidden');
                return;
            }
            errEl?.classList.add('hidden');

            const payload = {
                name,
                type,
                status:         document.getElementById('edit-status')?.value,
                brand:          document.getElementById('edit-brand')?.value.trim() || null,
                model:          document.getElementById('edit-model')?.value.trim() || null,
                purchase_date:  document.getElementById('edit-purchase-date')?.value || null,
                warranty_expiry:document.getElementById('edit-warranty')?.value || null,
                purchase_price: document.getElementById('edit-price')?.value || null,
                location:       document.getElementById('edit-location')?.value.trim() || null,
                notes:          document.getElementById('edit-notes')?.value.trim() || null,
            };

            try {
                btnSaveEdit.disabled = true;
                btnSaveEdit.textContent = 'Saving…';
                const res = await fetch(`${BASE_URL}/${currentEquipmentId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify(payload),
                });
                if (!res.ok) throw await res.json();
                panelData = await res.json();
                panelMode = 'view';
                footerView.classList.remove('hidden');
                footerEdit.classList.add('hidden');
                renderView(panelData);
                updateRowInList(panelData);
                refreshSummary();
            } catch (e) {
                const errEl = document.getElementById('edit-error');
                if (errEl) {
                    errEl.textContent = firstError(e) || 'Failed to save. Please try again.';
                    errEl.classList.remove('hidden');
                }
            } finally {
                btnSaveEdit.disabled = false;
                btnSaveEdit.textContent = 'Save Changes';
            }
        });

        // ── Service record form ───────────────────────────────────────────────
        function bindServiceForm() {
            const submit = document.getElementById('svc-submit');
            if (!submit) return;
            submit.addEventListener('click', async () => {
                const date     = document.getElementById('svc-date')?.value;
                const type     = document.getElementById('svc-type')?.value;
                const cost     = document.getElementById('svc-cost')?.value;
                const errEl    = document.getElementById('svc-error');

                if (!date || !type || cost === '' || cost === null) {
                    errEl.textContent = 'Please fill in required service fields';
                    errEl.classList.remove('hidden');
                    return;
                }
                errEl.classList.add('hidden');
                submit.disabled = true;
                submit.textContent = 'Adding…';

                try {
                    const res = await fetch(`${BASE_URL}/${currentEquipmentId}/service-records`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({
                            service_date:     date,
                            service_type:     type,
                            cost:             cost,
                            service_provider: document.getElementById('svc-provider')?.value.trim() || null,
                            notes:            document.getElementById('svc-notes')?.value.trim() || null,
                        }),
                    });
                    if (!res.ok) throw await res.json();
                    const record = await res.json();

                    // Prepend to service list
                    const list = document.getElementById('svc-list');
                    if (list) {
                        const empty = list.querySelector('p');
                        if (empty) empty.remove();
                        list.insertAdjacentHTML('afterbegin', serviceRecordCard(record));
                        bindDeleteServiceRecord(list.querySelector(`[data-record-id="${record.id}"]`));
                    }

                    // Update panelData
                    if (panelData) panelData.service_records.unshift(record);

                    // Reset form
                    document.getElementById('svc-date').value = todayStr();
                    document.getElementById('svc-type').value = '';
                    document.getElementById('svc-cost').value = '';
                    document.getElementById('svc-provider').value = '';
                    document.getElementById('svc-notes').value = '';
                } catch (e) {
                    document.getElementById('svc-error').textContent = firstError(e) || 'Failed to save';
                    document.getElementById('svc-error').classList.remove('hidden');
                } finally {
                    submit.disabled = false;
                    submit.textContent = 'Add Service Record';
                }
            });

            // Bind delete buttons already in the list
            document.querySelectorAll('#svc-list .svc-delete-btn').forEach(bindDeleteServiceRecord);
        }

        function bindDeleteServiceRecord(btn) {
            if (!btn) return;
            btn.addEventListener('click', async () => {
                const recordId = btn.dataset.recordId;
                if (!confirm('Delete this service record?')) return;
                try {
                    const res = await fetch(`${BASE_URL}/${currentEquipmentId}/service-records/${recordId}`, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    });
                    if (!res.ok) throw new Error();
                    btn.closest('[data-record-id]')?.remove();
                    if (panelData) {
                        panelData.service_records = panelData.service_records.filter(r => r.id != recordId);
                    }
                } catch { alert('Failed to delete service record.'); }
            });
        }

        // ── Delete modal ──────────────────────────────────────────────────────
        let deleteTargetId = null;

        function triggerDelete(btn) {
            deleteTargetId = btn.dataset.id;
            const name = btn.dataset.name || 'this item';
            document.getElementById('del-modal-msg').innerHTML =
                `This action cannot be undone. This will permanently delete the equipment <strong>${escHtml(name)}</strong> and all its service history.`;
            delModal.style.display = 'flex';
        }

        document.getElementById('del-cancel').addEventListener('click', () => {
            delModal.style.display = 'none';
            deleteTargetId = null;
        });

        delModal.addEventListener('click', e => { if (e.target === delModal) { delModal.style.display = 'none'; deleteTargetId = null; } });

        document.getElementById('del-confirm').addEventListener('click', async () => {
            if (!deleteTargetId) return;
            const btn = document.getElementById('del-confirm');
            btn.disabled = true;
            btn.textContent = 'Deleting…';
            try {
                const res = await fetch(`${BASE_URL}/${deleteTargetId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                });
                if (!res.ok) throw new Error();
                // Remove from DOM
                document.querySelectorAll(`[data-id="${deleteTargetId}"]`).forEach(el => el.closest('tr,div.eq-row')?.remove());
                if (currentEquipmentId == deleteTargetId) closePanel();
                delModal.style.display = 'none';
                deleteTargetId = null;
                refreshSummary();
            } catch {
                alert('Failed to delete. Please try again.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Delete';
            }
        });

        // ── DOM helpers ───────────────────────────────────────────────────────
        function appendToList(eq) {
            const tbody = document.getElementById('eq-table-body');
            const cardList = document.getElementById('eq-card-list');
            const statusColor = statusColors[eq.status] || '#888';

            // Remove "no equipment" placeholder if present
            tbody?.querySelector('td[colspan]')?.closest('tr')?.remove();
            cardList?.querySelector('p.py-8')?.remove();

            if (tbody) {
                const tr = document.createElement('tr');
                tr.className = 'eq-row cursor-pointer border-t transition hover:opacity-90';
                tr.style.cssText = 'border-color:var(--app-border);background:var(--app-panel)';
                tr.dataset.id = eq.id;
                tr.innerHTML = `
                    <td class="px-4 py-3 font-medium" style="color:var(--app-text)">${escHtml(eq.name)}</td>
                    <td class="px-4 py-3 text-xs"><span class="rounded-full px-2.5 py-1 font-semibold" style="background:var(--app-panel-strong);color:var(--app-text-muted)">${escHtml(typeLabels[eq.type]??eq.type)}</span></td>
                    <td class="px-4 py-3" style="color:var(--app-text-muted)">${escHtml(eq.brand??'—')}</td>
                    <td class="px-4 py-3"><span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold" style="background:color-mix(in srgb,${statusColor} 15%,transparent);color:${statusColor}"><span class="h-1.5 w-1.5 rounded-full" style="background:currentColor"></span>${escHtml(statusLabels[eq.status]??eq.status)}</span></td>
                    <td class="px-4 py-3 text-sm" style="color:var(--app-text-muted)">${escHtml(eq.location??'—')}</td>
                    <td class="px-4 py-3 text-sm" style="color:var(--app-text-muted)">${escHtml(eq.purchase_date_fmt??'—')}</td>
                    <td class="px-4 py-3 text-sm" style="color:${eq.warranty_expired?'#ef4444':'var(--app-text-muted)'}">${escHtml(eq.warranty_expiry_fmt??'—')}</td>
                    <td class="px-4 py-3 text-right"><div class="inline-flex items-center gap-1">
                        <button type="button" class="eq-view-btn inline-flex items-center justify-center rounded-lg p-1.5 transition hover:opacity-80" style="color:var(--app-text-muted)" data-id="${eq.id}" title="View details"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                        ${CAN_EDIT?`<button type="button" class="eq-edit-btn inline-flex items-center justify-center rounded-lg p-1.5 transition hover:opacity-80" style="color:var(--app-text-muted)" data-id="${eq.id}" title="Edit"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>`:''}
                        ${CAN_DELETE?`<button type="button" class="eq-delete-btn inline-flex items-center justify-center rounded-lg p-1.5 transition hover:opacity-80" style="color:var(--app-text-muted)" data-id="${eq.id}" data-name="${escAttr(eq.name)}" title="Delete"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>`:''}
                    </div></td>`;
                tbody.appendChild(tr);
            }

            if (cardList) {
                const card = document.createElement('div');
                card.className = 'eq-row app-panel cursor-pointer rounded-2xl border p-4 transition active:opacity-80';
                card.style.borderColor = 'var(--app-border)';
                card.dataset.id = eq.id;
                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-semibold" style="color:var(--app-text)">${escHtml(eq.name)}</p>
                            <p class="mt-0.5 text-xs" style="color:var(--app-text-muted)">${escHtml(eq.brand ?? '')}${eq.brand && eq.location ? ' · ' : ''}${escHtml(eq.location ?? '')}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1.5">
                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                                  style="background:color-mix(in srgb, ${statusColor} 15%, transparent);color:${statusColor}">
                                ${escHtml(statusLabels[eq.status] ?? eq.status)}
                            </span>
                            ${CAN_EDIT ? `<button type="button" class="eq-edit-btn p-1" style="color:var(--app-text-muted)" data-id="${eq.id}" title="Edit"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>` : ''}
                            ${CAN_DELETE ? `<button type="button" class="eq-delete-btn p-1" style="color:var(--app-text-muted)" data-id="${eq.id}" data-name="${escAttr(eq.name)}" title="Delete"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>` : ''}
                        </div>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium"
                              style="background:var(--app-panel-strong);color:var(--app-text-muted)">
                            ${escHtml(typeLabels[eq.type] ?? eq.type)}
                        </span>
                    </div>`;
                cardList.prepend(card);
            }
        }

        function updateRowInList(eq) {
            const statusColor = statusColors[eq.status] || '#888';
            document.querySelectorAll(`.eq-row[data-id="${eq.id}"]`).forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 7) {
                    cells[0].textContent = eq.name;
                    cells[1].querySelector('span').textContent = typeLabels[eq.type] ?? eq.type;
                    cells[2].textContent = eq.brand ?? '—';
                    cells[3].querySelector('span').style.background = `color-mix(in srgb, ${statusColor} 15%, transparent)`;
                    cells[3].querySelector('span').style.color = statusColor;
                    cells[3].querySelector('span span').style.background = 'currentColor';
                    cells[3].querySelector('span').lastChild.textContent = ' ' + (statusLabels[eq.status] ?? eq.status);
                    cells[4].textContent = eq.location ?? '—';
                    cells[5].textContent = eq.purchase_date_fmt ?? '—';
                    cells[6].textContent = eq.warranty_expiry_fmt ?? '—';
                }
                // For mobile cards
                const nameEl = row.querySelector('.font-semibold');
                if (nameEl) nameEl.textContent = eq.name;
            });
        }

        // ── Summary refresh ────────────────────────────────────────────────────
        async function refreshSummary() {
            try {
                const res = await fetch(`${BASE_URL}/summary`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
                });
                if (!res.ok) return;
                const s = await res.json();
                ['total','operational','maintenance','broken'].forEach(k => {
                    const el = document.getElementById(`eq-count-${k}`);
                    if (el) el.textContent = s[k];
                });
            } catch {}
        }

        // ── Utilities ──────────────────────────────────────────────────────────
        function escHtml(str) {
            return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function escAttr(str) {
            return String(str ?? '').replace(/"/g,'&quot;');
        }
        function todayStr() {
            return new Date().toISOString().slice(0,10);
        }
        function firstError(e) {
            if (e?.errors) return Object.values(e.errors).flat()[0];
            if (e?.message) return e.message;
            return null;
        }

        // Bottom-sheet on mobile: panel is full-height from bottom.
        // Uses inline style.transform (not CSS classes) to hide the panel so the
        // correct transform is applied regardless of Tailwind CSS purging.
        function updatePanelLayout() {
            if (window.innerWidth < 640) {
                panel.classList.remove('inset-y-0','right-0','translate-x-full');
                panel.classList.add('inset-x-0','bottom-0','top-auto','rounded-t-3xl');
                panel.style.removeProperty('border-left');
                panel.style.borderTop = '1px solid var(--app-border)';
            } else {
                panel.classList.remove('inset-x-0','bottom-0','top-auto','translate-y-full','rounded-t-3xl');
                panel.classList.add('inset-y-0','right-0');
                panel.style.borderLeft = '1px solid var(--app-border)';
                panel.style.removeProperty('border-top');
            }
            // Re-apply the correct hide/show transform for the new layout
            if (panelOpen) _showPanelTransform();
            else _hidePanelTransform();
        }
        updatePanelLayout();
        window.addEventListener('resize', updatePanelLayout);

        // Reset panel/modals on bfcache restore (browser Back button)
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                panelOpen = false;
                currentEquipmentId = null;
                overlay.classList.add('hidden');
                _hidePanelTransform();
                if (delModal) delModal.style.display = 'none';
            }
        });
    })();
    </script>

    @push('styles')
        <style>
            .eq-desktop-list { display: none; }
            .eq-mobile-list { display: block; }

            @media (min-width: 640px) {
                .eq-desktop-list { display: block; }
                .eq-mobile-list { display: none; }
            }
        </style>
    @endpush
</x-layouts.admin>
