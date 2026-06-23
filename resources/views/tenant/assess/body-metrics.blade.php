<x-layouts.admin :title="$moduleTitles[$moduleKey]">
@include('tenant.assess._styles')
<div class="as-shell">
    <div class="as-head">
        <div><div class="as-title">{{ $moduleTitles[$moduleKey] }}</div><div class="as-sub">Track measurements, BMI, and next measurement dates.</div></div>
        <div class="as-actions"><a href="{{ route('tenant.assess.body-metrics.progress', ['member_id' => $selectedMemberId]) }}" class="as-btn as-btn-secondary">Progress Tracking</a></div>
    </div>
    @include('tenant.assess._nav')
    @include('tenant.assess._member-picker', ['action' => route('tenant.assess.body-metrics'), 'member' => $member, 'extra' => array_filter(['from' => request('from'), 'to' => request('to'), 'next_measurement_date' => request('next_measurement_date'), 'per_page' => request('per_page')])])

    <form method="GET" action="{{ route('tenant.assess.body-metrics') }}" class="as-panel-tight as-inline">
        <input type="hidden" name="member_id" value="{{ $selectedMemberId }}">
        <div><label class="as-label">From</label><input class="as-input" type="date" name="from" value="{{ request('from') }}"></div>
        <div><label class="as-label">To</label><input class="as-input" type="date" name="to" value="{{ request('to') }}"></div>
        <div><label class="as-label">Next measurement date</label><input class="as-input" type="date" name="next_measurement_date" value="{{ request('next_measurement_date') }}"></div>
        <button class="as-btn as-btn-primary" type="submit">Filter</button>
    </form>

    @if ($member && ($canAdd || ($editingRecord && $canEdit)))
        <form method="POST" action="{{ $editingRecord ? route('tenant.assess.body-metrics.update', $editingRecord) : route('tenant.assess.body-metrics.store') }}" class="as-panel">
            @csrf @if ($editingRecord) @method('PUT') @endif
            <input type="hidden" name="member_id" value="{{ $member->id }}">
            <div class="as-grid">
                <div class="as-col-4"><label class="as-label">Measurement date</label><input class="as-input" type="date" name="measurement_date" value="{{ old('measurement_date', optional($editingRecord?->assessment_date)->toDateString() ?? now()->toDateString()) }}" required></div>
                <div class="as-col-4"><label class="as-label">Weight (kg)</label><input class="as-input" step="0.01" type="number" name="weight_kg" value="{{ old('weight_kg', data_get($editingRecord?->payload, 'weight_kg')) }}" required></div>
                <div class="as-col-4"><label class="as-label">Height (cm)</label><input class="as-input" step="0.01" type="number" name="height_cm" value="{{ old('height_cm', data_get($editingRecord?->payload, 'height_cm')) }}" required></div>
                <div class="as-col-4"><label class="as-label">Waist (cm)</label><input class="as-input" step="0.01" type="number" name="waist_cm" value="{{ old('waist_cm', data_get($editingRecord?->payload, 'waist_cm')) }}"></div>
                <div class="as-col-4"><label class="as-label">Hip (cm)</label><input class="as-input" step="0.01" type="number" name="hip_cm" value="{{ old('hip_cm', data_get($editingRecord?->payload, 'hip_cm')) }}"></div>
                <div class="as-col-4"><label class="as-label">Neck (cm)</label><input class="as-input" step="0.01" type="number" name="neck_cm" value="{{ old('neck_cm', data_get($editingRecord?->payload, 'neck_cm')) }}"></div>
                <div class="as-col-4"><label class="as-label">Body fat %</label><input class="as-input" step="0.01" type="number" name="body_fat_pct" value="{{ old('body_fat_pct', data_get($editingRecord?->payload, 'body_fat_pct')) }}"></div>
                <div class="as-col-4"><label class="as-label">Next measurement date</label><input class="as-input" type="date" name="next_measurement_date" value="{{ old('next_measurement_date', optional($editingRecord?->next_assessment_date)->toDateString()) }}"></div>
                <div class="as-col-12"><label class="as-label">Notes</label><textarea class="as-textarea" name="notes">{{ old('notes', $editingRecord->notes ?? '') }}</textarea></div>
            </div>
            <div class="as-actions" style="margin-top:1rem"><button class="as-btn as-btn-primary">{{ $editingRecord ? 'Save Metrics' : 'Add Body Metrics' }}</button></div>
        </form>
    @endif

    <div class="as-panel">
        <div class="as-table-wrap">
            <table class="as-table">
                <thead><tr><th>Client</th><th>Date</th><th>Weight</th><th>Height</th><th>BMI</th><th>Body fat</th><th>Next</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse ($records as $record)
                    <tr>
                        <td>{{ $record->member->name }}</td>
                        <td>{{ $record->assessment_date?->format('d M Y') }}</td>
                        <td>{{ data_get($record->payload, 'weight_kg') }}</td>
                        <td>{{ data_get($record->payload, 'height_cm') }}</td>
                        <td>{{ data_get($record->payload, 'bmi') }} <div class="as-help">{{ data_get($record->payload, 'bmi_category') }}</div></td>
                        <td>{{ data_get($record->payload, 'body_fat_pct', '—') }}</td>
                        <td>{{ $record->next_assessment_date?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <div class="inline-flex items-center gap-1.5">
                                @if ($canEdit)
                                    <a href="{{ route('tenant.assess.body-metrics', ['member_id' => $record->member_id, 'edit' => $record->id]) }}"
                                       class="lk-icon-btn lk-icon-btn-edit" title="Edit">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z"/></svg>
                                    </a>
                                @endif
                                <button type="button" class="lk-icon-btn lk-icon-btn-print" onclick="window.print()" title="Print">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                </button>
                                @if ($canDelete)
                                    <button type="button" class="lk-icon-btn lk-icon-btn-delete"
                                            onclick="assessConfirmDelete('del-bm-{{ $record->id }}', @json($record->member->name))"
                                            title="Delete">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                    </button>
                                    <form id="del-bm-{{ $record->id }}" method="POST"
                                          action="{{ route('tenant.assess.records.destroy', $record) }}"
                                          style="display:none">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="confirm_name">
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="as-empty">No body metrics records found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($records->isNotEmpty())
            <div class="mt-4 flex flex-col items-center justify-between gap-3 rounded-[1.5rem] border px-5 py-3 sm:flex-row"
                 style="border-color:var(--app-border);background:var(--app-panel)">
                <p class="text-xs" style="color:var(--app-text-muted)">
                    Showing {{ $records->firstItem() }} to {{ $records->lastItem() }} of {{ number_format($records->total()) }} records
                </p>
                <div class="flex items-center gap-3">
                    <select onchange="window.location='{{ route('tenant.assess.body-metrics') }}?'+new URLSearchParams({...Object.fromEntries(new URLSearchParams(window.location.search)),...{per_page:this.value,page:1}}).toString()"
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
    </div>
</div>

@push('styles')
<style>
.lk-icon-btn { align-items: center; border-radius: 0.5rem; display: inline-flex; height: 2rem; justify-content: center; transition: background 140ms, color 140ms; width: 2rem; }
.lk-icon-btn-edit   { background: color-mix(in srgb, #f59e0b 12%, transparent); color: #f59e0b; }
.lk-icon-btn-edit:hover { background: color-mix(in srgb, #f59e0b 22%, transparent); }
.lk-icon-btn-print  { background: color-mix(in srgb, #3b82f6 12%, transparent); color: #3b82f6; }
.lk-icon-btn-print:hover { background: color-mix(in srgb, #3b82f6 22%, transparent); }
.lk-icon-btn-delete { border: none; cursor: pointer; background: color-mix(in srgb, #ef4444 12%, transparent); color: #ef4444; }
.lk-icon-btn-delete:hover:not(:disabled) { background: color-mix(in srgb, #ef4444 22%, transparent); }
.lk-icon-btn-delete:disabled { cursor: not-allowed; opacity: 0.35; }
</style>
@endpush

</x-layouts.admin>
