<x-layouts.admin :title="$moduleTitles[$moduleKey]">
@include('tenant.assess._styles')
<div class="as-shell">
    <div class="as-head"><div><div class="as-title">{{ $moduleTitles[$moduleKey] }}</div><div class="as-sub">Resting heart rate and blood pressure history.</div></div><button class="as-btn as-btn-secondary" onclick="location.reload()">Refresh</button></div>
    @include('tenant.assess._nav')
    @include('tenant.assess._member-picker', ['action' => route('tenant.assess.vitals'), 'member' => $member])
    @if ($member && ($canAdd || ($editingRecord && $canEdit)))
        <form method="POST" action="{{ $editingRecord ? route('tenant.assess.vitals.update', $editingRecord) : route('tenant.assess.vitals.store') }}" class="as-panel">
            @csrf @if ($editingRecord) @method('PUT') @endif
            <input type="hidden" name="member_id" value="{{ $member->id }}">
            <div class="as-grid">
                <div class="as-col-4"><label class="as-label">Measurement date</label><input class="as-input" type="date" name="measurement_date" value="{{ old('measurement_date', optional($editingRecord?->assessment_date)->toDateString() ?? now()->toDateString()) }}" required></div>
                <div class="as-col-4"><label class="as-label">HR (bpm)</label><input class="as-input" type="number" name="hr_bpm" value="{{ old('hr_bpm', data_get($editingRecord?->payload, 'hr_bpm')) }}" required></div>
                <div class="as-col-4"><label class="as-label">Next check date</label><input class="as-input" type="date" name="next_check_date" value="{{ old('next_check_date', optional($editingRecord?->next_assessment_date)->toDateString()) }}"></div>
                <div class="as-col-6"><label class="as-label">BP Systolic</label><input class="as-input" type="number" name="bp_systolic" value="{{ old('bp_systolic', data_get($editingRecord?->payload, 'bp_systolic')) }}" required></div>
                <div class="as-col-6"><label class="as-label">BP Diastolic</label><input class="as-input" type="number" name="bp_diastolic" value="{{ old('bp_diastolic', data_get($editingRecord?->payload, 'bp_diastolic')) }}" required></div>
                <div class="as-col-12"><label class="as-label">Notes</label><textarea class="as-textarea" name="notes">{{ old('notes', $editingRecord->notes ?? '') }}</textarea></div>
            </div>
            <div class="as-actions" style="margin-top:1rem"><button class="as-btn as-btn-primary">{{ $editingRecord ? 'Save Record' : 'New Record' }}</button></div>
        </form>
    @endif
    <div class="as-panel">
        @if (! $member)<div class="as-empty">Select a client to view vitals history.</div>
        @elseif ($records->isEmpty())<div class="as-empty">No vitals records yet.</div>
        @else
            <div class="as-table-wrap"><table class="as-table"><thead><tr><th>Date</th><th>HR</th><th>BP</th><th>Next check</th><th>Actions</th></tr></thead><tbody>@foreach($records as $record)<tr><td>{{ $record->assessment_date?->format('d M Y') }}</td><td>{{ data_get($record->payload,'hr_bpm') }}</td><td>{{ data_get($record->payload,'bp_systolic') }}/{{ data_get($record->payload,'bp_diastolic') }}</td><td>{{ $record->next_assessment_date?->format('d M Y') ?? '—' }}</td><td class="as-actions">@if($canEdit)<a class="as-btn as-btn-secondary" href="{{ route('tenant.assess.vitals', ['member_id' => $member->id, 'edit' => $record->id]) }}">Edit</a>@endif @if($canDelete)<form id="del-vi-{{ $record->id }}" method="POST" action="{{ route('tenant.assess.records.destroy', $record) }}">@csrf @method('DELETE')<input type="hidden" name="confirm_name"><button type="button" class="as-btn as-btn-danger" onclick="assessConfirmDelete('del-vi-{{ $record->id }}', @json($member->name))">Delete</button></form>@endif</td></tr>@endforeach</tbody></table></div>
        @endif
    </div>
</div>
</x-layouts.admin>
