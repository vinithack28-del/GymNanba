<x-layouts.admin :title="$moduleTitles[$moduleKey]">
@include('tenant.assess._styles')

<style>
.qst-tabs { border-bottom: 1px solid var(--app-border); display: flex; gap: 0; margin-bottom: 1.5rem; }
.qst-tab { align-items: center; border-bottom: 2px solid transparent; color: var(--app-text-muted); cursor: pointer; display: inline-flex; font-size: .9rem; font-weight: 600; gap: .4rem; margin-bottom: -1px; padding: .75rem 1.25rem; text-decoration: none; transition: color .15s, border-color .15s; }
.qst-tab:hover { color: var(--app-text); }
.qst-tab-active { border-bottom-color: var(--app-brand); color: var(--app-brand); }
.qst-tab-inactive { pointer-events: none; opacity: .45; }

.qst-section-head { align-items: flex-start; display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
.qst-section-title { align-items: center; display: flex; gap: .55rem; }
.qst-section-title h3 { color: var(--app-text); font-size: 1.05rem; font-weight: 700; }
.qst-count { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; color: var(--app-text-muted); font-size: .75rem; font-weight: 700; padding: .1rem .6rem; }
.qst-section-sub { color: var(--app-text-muted); font-size: .82rem; margin-top: .2rem; }

.qst-filter { align-items: center; display: flex; flex-wrap: wrap; gap: .6rem; margin-bottom: 1rem; }
.qst-search { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: .9rem; color: var(--app-text-muted); display: flex; flex: 1; gap: .5rem; min-width: 200px; padding: .6rem .9rem; }
.qst-search svg { flex-shrink: 0; height: 1rem; width: 1rem; }
.qst-search input { background: transparent; border: none; color: var(--app-text); font-size: .88rem; outline: none; width: 100%; }
.qst-select { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: .9rem; color: var(--app-text); font-size: .88rem; outline: none; padding: .6rem .9rem; }

.qst-table-wrap { overflow-x: auto; }
.qst-table { border-collapse: collapse; font-size: .88rem; width: 100%; }
.qst-table th { background: var(--app-panel-strong); border-bottom: 1px solid var(--app-border); color: var(--app-text-muted); font-size: .72rem; font-weight: 700; letter-spacing: .05em; padding: .8rem 1rem; text-align: left; text-transform: uppercase; white-space: nowrap; }
.qst-table td { border-top: 1px solid var(--app-border); color: var(--app-text); padding: .85rem 1rem; vertical-align: middle; }
.qst-table tr:hover td { background: color-mix(in srgb, var(--app-border) 25%, transparent); }
.qst-risk-count { align-items: center; background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 999px; display: inline-flex; font-size: .82rem; font-weight: 700; height: 1.75rem; justify-content: center; width: 1.75rem; }
.qst-badge { border-radius: 999px; display: inline-flex; font-size: .75rem; font-weight: 700; padding: .2rem .65rem; white-space: nowrap; }
.qst-badge-low  { background: color-mix(in srgb, #22c55e 14%, transparent); color: #16a34a; }
.qst-badge-mod  { background: color-mix(in srgb, #f59e0b 14%, transparent); color: #b45309; }
.qst-badge-high { background: color-mix(in srgb, #ef4444 14%, transparent); color: #dc2626; }
.qst-badge-yes  { background: color-mix(in srgb, #ef4444 12%, transparent); color: #dc2626; }
.qst-badge-no   { background: color-mix(in srgb, #22c55e 12%, transparent); color: #16a34a; }

/* Action menu */
.qst-act-wrap { display: inline-flex; position: relative; }
.qst-act-btn { align-items: center; background: transparent; border: 1px solid transparent; border-radius: .5rem; color: var(--app-text-muted); cursor: pointer; display: inline-flex; padding: .25rem .4rem; transition: background 120ms, border-color 120ms; }
.qst-act-btn:hover { background: var(--app-panel-strong); border-color: var(--app-border); color: var(--app-text); }
.qst-act-menu { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: .85rem; box-shadow: 0 8px 32px rgba(0,0,0,.28); display: none; min-width: 176px; padding: .35rem; position: fixed; z-index: 200; }
.qst-act-wrap.open .qst-act-menu { display: block; }
.qst-act-item { align-items: center; background: transparent; border: none; border-radius: .5rem; color: var(--app-text); cursor: pointer; display: flex; font-size: .82rem; gap: .5rem; padding: .45rem .65rem; text-decoration: none; transition: background 120ms; width: 100%; text-align: left; }
.qst-act-item:hover { background: color-mix(in srgb, var(--app-border) 70%, transparent); }
.qst-act-divider { border-top: 1px solid var(--app-border); margin: .3rem .4rem; }
.qst-act-danger { color: #ef4444 !important; }

.qst-empty { align-items: center; display: flex; flex-direction: column; gap: .75rem; padding: 4rem 1.5rem; text-align: center; }
.qst-empty-icon { align-items: center; background: color-mix(in srgb, var(--app-brand) 10%, transparent); border-radius: 1.25rem; color: var(--app-brand); display: flex; height: 3.75rem; justify-content: center; width: 3.75rem; }
</style>

<div class="as-shell">
    <div class="as-head">
        <div>
            <div class="as-title">Questionnaire</div>
            <div class="as-sub">Manage PAR-Q+ and Physician Clearance questionnaires</div>
        </div>
        @if ($canAdd)
            <a href="{{ route('tenant.assess.questionnaire.create') }}" class="as-btn as-btn-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add PAR-Q+
            </a>
        @endif
    </div>

    @include('tenant.assess._nav')

    @if (session('status'))
        <div class="as-panel" style="background:color-mix(in srgb,#22c55e 10%,transparent);border-color:#22c55e55;color:#16a34a;padding:.85rem 1.1rem">
            {{ session('status') }}
        </div>
    @endif

    {{-- Sub-tabs --}}
    <div class="qst-tabs">
        <span class="qst-tab qst-tab-active">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6M9 16h6M9 8h6M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/></svg>
            PAR-Q+
        </span>
        <span class="qst-tab qst-tab-inactive">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h3"/></svg>
            Physician Clearance
        </span>
    </div>

    <div class="as-panel" style="padding:1.25rem">
        {{-- Section header --}}
        <div class="qst-section-head">
            <div>
                <div class="qst-section-title">
                    <h3>PAR-Q+ Records</h3>
                    <span class="qst-count">{{ $records->total() }}</span>
                </div>
                <p class="qst-section-sub">Physical Activity Readiness Questionnaire records</p>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('tenant.assess.questionnaire') }}" class="qst-filter" id="qst-filter-form">
            <label class="qst-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or phone…" autocomplete="off">
            </label>
            <select name="risk_level" class="qst-select" onchange="this.form.submit()">
                <option value="">All Risk Levels</option>
                <option value="low" @selected(request('risk_level') === 'low')>Low Risk</option>
                <option value="moderate" @selected(request('risk_level') === 'moderate')>Moderate Risk</option>
                <option value="high" @selected(request('risk_level') === 'high')>High Risk</option>
            </select>
            @if (request()->hasAny(['search', 'risk_level']))
                <a href="{{ route('tenant.assess.questionnaire') }}" class="as-btn as-btn-secondary" style="padding:.6rem .9rem;font-size:.82rem">Clear</a>
            @endif
            <button type="submit" class="as-btn as-btn-secondary" style="padding:.6rem .9rem;font-size:.82rem">Search</button>
        </form>

        {{-- Table --}}
        @if ($records->isEmpty())
            <div class="qst-empty">
                <div class="qst-empty-icon">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h6M9 16h6M9 8h6M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/></svg>
                </div>
                @if (request()->hasAny(['search', 'risk_level']))
                    <p class="font-semibold" style="color:var(--app-text)">No records match these filters.</p>
                    <p style="color:var(--app-text-muted);font-size:.85rem">Try adjusting your search or clearing the filters.</p>
                    <a href="{{ route('tenant.assess.questionnaire') }}" class="as-btn as-btn-secondary" style="margin-top:.25rem">Clear filters</a>
                @else
                    <p class="font-semibold" style="color:var(--app-text)">No PAR-Q+ records yet.</p>
                    <p style="color:var(--app-text-muted);font-size:.85rem">Add your first PAR-Q+ questionnaire to get started.</p>
                    @if ($canAdd)
                        <a href="{{ route('tenant.assess.questionnaire.create') }}" class="as-btn as-btn-primary" style="margin-top:.25rem">Add PAR-Q+</a>
                    @endif
                @endif
            </div>
        @else
            <div class="qst-table-wrap">
                <table class="qst-table">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Phone</th>
                            <th>Last Updated</th>
                            <th style="text-align:center">Risk Count</th>
                            <th>Risk Level</th>
                            <th>Clearance Required</th>
                            <th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                        @php
                            $riskCount = collect($record->payload['section1'] ?? [])->where('answer', true)->count();
                            [$riskLabel, $riskClass] = match ($record->status) {
                                'cleared'                    => ['Low Risk',      'qst-badge-low'],
                                'conditional'                => ['Moderate Risk', 'qst-badge-mod'],
                                'medical_clearance_required' => ['High Risk',     'qst-badge-high'],
                                default                      => ['Unknown',       ''],
                            };
                            $clearanceReq = $record->status === 'medical_clearance_required';
                        @endphp
                        <tr>
                            <td>
                                <span class="font-semibold">{{ $record->member?->name ?? '—' }}</span>
                            </td>
                            <td style="color:var(--app-text-muted)">{{ $record->member?->phone ?? '—' }}</td>
                            <td style="color:var(--app-text-muted)">{{ $record->updated_at->format('d M Y') }}</td>
                            <td style="text-align:center">
                                <span class="qst-risk-count">{{ $riskCount }}</span>
                            </td>
                            <td>
                                <span class="qst-badge {{ $riskClass }}">{{ $riskLabel }}</span>
                            </td>
                            <td>
                                <span class="qst-badge {{ $clearanceReq ? 'qst-badge-yes' : 'qst-badge-no' }}">
                                    {{ $clearanceReq ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td style="text-align:right">
                                <div class="qst-act-wrap">
                                    <button type="button" class="qst-act-btn" aria-label="Actions">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                                    </button>
                                    <div class="qst-act-menu">
                                        <a href="{{ route('tenant.assess.questionnaire.edit', $record) }}" class="qst-act-item">
                                            <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            View
                                        </a>
                                        @if ($canEdit)
                                            <a href="{{ route('tenant.assess.questionnaire.edit', $record) }}" class="qst-act-item">
                                                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z"/></svg>
                                                Edit
                                            </a>
                                        @endif
                                        <a href="#" class="qst-act-item" style="opacity:.5;pointer-events:none" title="Coming soon">
                                            <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h3"/></svg>
                                            Physician Clearance Form
                                            <svg class="h-3 w-3 ml-auto shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                        </a>
                                        @if ($canDelete)
                                            <div class="qst-act-divider"></div>
                                            <button type="button" class="qst-act-item qst-act-danger"
                                                onclick="qstDelete({{ $record->id }}, '{{ addslashes($record->member?->name ?? '') }}')">
                                                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                                                Delete
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- Hidden delete form --}}
                                @if ($canDelete)
                                    <form id="qst-del-{{ $record->id }}" method="POST"
                                          action="{{ route('tenant.assess.records.destroy', $record) }}"
                                          style="display:none">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="confirm_name" value="">
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($records->isNotEmpty())
                <div class="mt-4 flex flex-col items-center justify-between gap-3 rounded-[1.5rem] border px-5 py-3 sm:flex-row"
                     style="border-color:var(--app-border);background:var(--app-panel)">
                    <p class="text-xs" style="color:var(--app-text-muted)">
                        Showing {{ $records->firstItem() }} to {{ $records->lastItem() }} of {{ number_format($records->total()) }} records
                    </p>
                    <div class="flex items-center gap-3">
                        <select onchange="window.location='{{ route('tenant.assess.questionnaire') }}?'+new URLSearchParams({...Object.fromEntries(new URLSearchParams(window.location.search)),...{per_page:this.value,page:1}}).toString()"
                                class="rounded-xl border px-3 py-2 text-xs outline-none"
                                style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                            @foreach ([10, 25, 50, 100] as $pp)
                                <option value="{{ $pp }}" @selected($records->perPage() === $pp)>{{ $pp }} / page</option>
                            @endforeach
                        </select>
                        {{ $records->links() }}
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
(() => {
    // Action dropdown toggle
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.qst-act-btn');
        if (btn) {
            e.stopPropagation();
            const wrap = btn.closest('.qst-act-wrap');
            const isOpen = wrap.classList.contains('open');
            document.querySelectorAll('.qst-act-wrap.open').forEach(w => w.classList.remove('open'));
            if (!isOpen) {
                wrap.classList.add('open');
                const menu = wrap.querySelector('.qst-act-menu');
                const rect = btn.getBoundingClientRect();
                menu.style.top = (rect.bottom + 4) + 'px';
                menu.style.right = (window.innerWidth - rect.right) + 'px';
            }
            return;
        }
        if (!e.target.closest('.qst-act-menu')) {
            document.querySelectorAll('.qst-act-wrap.open').forEach(w => w.classList.remove('open'));
        }
    });
})();

function qstDelete(recordId, memberName) {
    const typed = window.prompt('Type ' + memberName + ' to confirm permanent deletion.');
    if (typed === null) return;
    if (typed !== memberName) { alert('Name does not match.'); return; }
    const form = document.getElementById('qst-del-' + recordId);
    if (!form) return;
    form.querySelector('input[name="confirm_name"]').value = typed;
    form.submit();
}
</script>
@endpush

</x-layouts.admin>
