<x-layouts.admin :title="$moduleTitles[$moduleKey]">
@include('tenant.assess._styles')
<div class="as-shell">
    <div class="as-head"><div><div class="as-title">{{ $moduleTitles[$moduleKey] }}</div><div class="as-sub">Cardiorespiratory, strength, endurance, and flexibility history.</div></div></div>
    @include('tenant.assess._nav')
    @include('tenant.assess._member-picker', ['action' => route('tenant.assess.fitness'), 'member' => $member, 'extra' => ['tab' => $tab]])
    <div class="as-tabs">
        @foreach (['cardio' => 'Cardiorespiratory', 'strength' => 'Muscular Strength', 'endurance' => 'Muscular Endurance', 'flexibility' => 'Flexibility'] as $tabKey => $tabLabel)
            <a href="{{ route('tenant.assess.fitness', ['member_id' => $selectedMemberId, 'tab' => $tabKey]) }}" class="as-tab {{ $tab === $tabKey ? 'as-tab-active' : '' }}">{{ $tabLabel }}</a>
        @endforeach
    </div>
    @if ($member && ($canAdd || ($editingRecord && $canEdit)))
        <form method="POST" action="{{ $editingRecord ? route('tenant.assess.fitness.update', $editingRecord) : route('tenant.assess.fitness.store') }}" class="as-panel">
            @csrf @if ($editingRecord) @method('PUT') @endif
            <input type="hidden" name="member_id" value="{{ $member->id }}"><input type="hidden" name="tab" value="{{ $tab }}">
            <div class="as-grid">
                <div class="as-col-4"><label class="as-label">Measurement date</label><input class="as-input" type="date" name="measurement_date" value="{{ old('measurement_date', optional($editingRecord?->assessment_date)->toDateString() ?? now()->toDateString()) }}" required></div>
                <div class="as-col-4"><label class="as-label">Next measurement date</label><input class="as-input" type="date" name="next_measurement_date" value="{{ old('next_measurement_date', optional($editingRecord?->next_assessment_date)->toDateString()) }}"></div>
                @if ($tab === 'cardio')
                    <div class="as-col-4"><label class="as-label">Test type</label><select class="as-select" name="test_type"><option value="cooper_12_min">12 min walk/run</option><option value="run_1_5_mile">1.5 mile run</option><option value="walk_1_mile">1 mile walk</option></select></div>
                    <div class="as-col-4"><label class="as-label">Test value</label><input class="as-input" step="0.01" type="number" name="test_value" value="{{ old('test_value', data_get($editingRecord?->payload, 'test_value')) }}" required></div>
                    <div class="as-col-4"><label class="as-label">HRR</label><input class="as-input" step="0.01" type="number" name="hrr" value="{{ old('hrr', data_get($editingRecord?->payload, 'hrr')) }}"></div>
                @elseif ($tab === 'strength')
                    <div class="as-col-4"><label class="as-label">Test name</label><input class="as-input" name="test_name" value="{{ old('test_name', data_get($editingRecord?->payload, 'test_name', $editingRecord->title ?? '')) }}" required></div>
                    <div class="as-col-4"><label class="as-label">Test value</label><input class="as-input" step="0.01" type="number" name="test_value" value="{{ old('test_value', data_get($editingRecord?->payload, 'test_value')) }}" required></div>
                    <div class="as-col-4"><label class="as-label">Unit</label><select class="as-select" name="unit"><option value="kg">kg</option><option value="N">N</option><option value="lbs">lbs</option></select></div>
                @elseif ($tab === 'endurance')
                    <div class="as-col-6"><label class="as-label">Test name</label><input class="as-input" name="test_name" value="{{ old('test_name', data_get($editingRecord?->payload, 'test_name', $editingRecord->title ?? '')) }}" required></div>
                    <div class="as-col-6"><label class="as-label">Reps</label><input class="as-input" type="number" name="reps" value="{{ old('reps', data_get($editingRecord?->payload, 'reps')) }}" required></div>
                    <div class="as-col-12"><label class="as-label">Interpretation</label><input class="as-input" name="interpretation" value="{{ old('interpretation', data_get($editingRecord?->payload, 'interpretation')) }}"></div>
                @else
                    <div class="as-col-6"><label class="as-label">Test name</label><input class="as-input" name="test_name" value="{{ old('test_name', data_get($editingRecord?->payload, 'test_name', $editingRecord->title ?? 'Sit-and-reach')) }}" required></div>
                    <div class="as-col-6"><label class="as-label">Distance / value (cm)</label><input class="as-input" step="0.01" type="number" name="distance_cm" value="{{ old('distance_cm', data_get($editingRecord?->payload, 'distance_cm')) }}" required></div>
                    <div class="as-col-12"><label class="as-label">Interpretation</label><input class="as-input" name="interpretation" value="{{ old('interpretation', data_get($editingRecord?->payload, 'interpretation')) }}"></div>
                @endif
                <div class="as-col-12"><label class="as-label">Notes</label><textarea class="as-textarea" name="notes">{{ old('notes', $editingRecord->notes ?? '') }}</textarea></div>
            </div>
            <div class="as-actions" style="margin-top:1rem"><button class="as-btn as-btn-primary">{{ $editingRecord ? 'Save Test' : 'New Test' }}</button></div>
        </form>
    @endif
    <div class="as-panel">
        @if (! $member)<div class="as-empty">Select a client to view {{ $tab }} tests.</div>
        @elseif ($records->isEmpty())<div class="as-empty">No tests recorded yet.</div>
        @else
            <div class="as-table-wrap"><table class="as-table"><thead><tr><th>Date</th><th>Test</th><th>Value</th><th>Next</th><th>Actions</th></tr></thead><tbody>@foreach($records as $record)<tr><td>{{ $record->assessment_date?->format('d M Y') }}</td><td>{{ $record->title }}</td><td>{{ data_get($record->payload, 'vo2max', data_get($record->payload, 'test_value', data_get($record->payload, 'reps', data_get($record->payload, 'distance_cm', '—')))) }}</td><td>{{ $record->next_assessment_date?->format('d M Y') ?? '—' }}</td><td class="as-actions">@if($canEdit)<a class="as-btn as-btn-secondary" href="{{ route('tenant.assess.fitness', ['member_id' => $member->id, 'tab' => $tab, 'edit' => $record->id]) }}">Edit</a>@endif @if($canDelete)<form id="del-fi-{{ $record->id }}" method="POST" action="{{ route('tenant.assess.records.destroy', $record) }}">@csrf @method('DELETE')<input type="hidden" name="confirm_name"><input type="hidden" name="tab" value="{{ $tab }}"><button type="button" class="as-btn as-btn-danger" onclick="assessConfirmDelete('del-fi-{{ $record->id }}', @json($member->name))">Delete</button></form>@endif</td></tr>@endforeach</tbody></table></div>
        @endif
    </div>
</div>
</x-layouts.admin>
