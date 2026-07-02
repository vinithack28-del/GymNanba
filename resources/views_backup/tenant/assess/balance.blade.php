<x-layouts.admin :title="$moduleTitles[$moduleKey]">
@include('tenant.assess._styles')
<div class="as-shell">
    <div class="as-head"><div><div class="as-title">{{ $moduleTitles[$moduleKey] }}</div><div class="as-sub">Y-Balance test history and insight generation.</div></div></div>
    @include('tenant.assess._nav')
    @include('tenant.assess._member-picker', ['action' => route('tenant.assess.balance'), 'member' => $member])
    @if ($member && ($canAdd || ($editingRecord && $canEdit)))
        <form method="POST" action="{{ $editingRecord ? route('tenant.assess.balance.update', $editingRecord) : route('tenant.assess.balance.store') }}" class="as-panel">
            @csrf @if ($editingRecord) @method('PUT') @endif
            <input type="hidden" name="member_id" value="{{ $member->id }}">
            <div class="as-grid">
                <div class="as-col-4"><label class="as-label">Measurement date</label><input class="as-input" type="date" name="measurement_date" value="{{ old('measurement_date', optional($editingRecord?->assessment_date)->toDateString() ?? now()->toDateString()) }}" required></div>
                <div class="as-col-4"><label class="as-label">Next measurement date</label><input class="as-input" type="date" name="next_measurement_date" value="{{ old('next_measurement_date', optional($editingRecord?->next_assessment_date)->toDateString()) }}"></div>
                <div class="as-col-4"><label class="as-label">Limb length (cm)</label><input class="as-input" step="0.01" type="number" name="limb_length_cm" value="{{ old('limb_length_cm', data_get($editingRecord?->payload, 'limb_length_cm')) }}" required></div>
                @foreach (['right_anterior' => 'Right anterior', 'right_posteromedial' => 'Right posteromedial', 'right_posterolateral' => 'Right posterolateral', 'left_anterior' => 'Left anterior', 'left_posteromedial' => 'Left posteromedial', 'left_posterolateral' => 'Left posterolateral'] as $key => $label)
                    <div class="as-col-4"><label class="as-label">{{ $label }}</label><input class="as-input" step="0.01" type="number" name="{{ $key }}" value="{{ old($key, data_get($editingRecord?->payload, str_replace(['right_','left_'], ['right.','left.'], $key))) }}" required></div>
                @endforeach
                <div class="as-col-12"><label class="as-label">Notes</label><textarea class="as-textarea" name="notes">{{ old('notes', $editingRecord->notes ?? '') }}</textarea></div>
            </div>
            <div class="as-actions" style="margin-top:1rem"><button class="as-btn as-btn-primary">{{ $editingRecord ? 'Save Test' : 'New Test' }}</button></div>
        </form>
    @endif
    <div class="as-panel">
        @if (! $member)
            <div class="as-empty">Select a client to view balance tests.</div>
        @elseif ($records->isEmpty())
            <div class="as-empty">No balance tests yet.</div>
        @else
            <div class="as-table-wrap"><table class="as-table"><thead><tr><th>Date</th><th>Status</th><th>Composite R</th><th>Composite L</th><th>Asymmetry</th><th>Actions</th></tr></thead><tbody>@foreach($records as $record)<tr><td>{{ $record->assessment_date?->format('d M Y') }}</td><td>{{ str($record->status)->replace('_',' ')->title() }}</td><td>{{ data_get($record->payload, 'right.composite_pct') }}%</td><td>{{ data_get($record->payload, 'left.composite_pct') }}%</td><td>{{ data_get($record->payload, 'asymmetry_pct') }}%</td><td class="as-actions">@if($canEdit)<a class="as-btn as-btn-secondary" href="{{ route('tenant.assess.balance', ['member_id' => $member->id, 'edit' => $record->id]) }}">Edit</a><form method="POST" action="{{ route('tenant.assess.balance.insight', $record) }}">@csrf<button class="as-btn as-btn-secondary" type="submit">{{ $record->ai_insight ? 'Refresh Insight' : 'Generate Insight' }}</button></form>@endif @if($canDelete)<form id="del-ba-{{ $record->id }}" method="POST" action="{{ route('tenant.assess.records.destroy', $record) }}">@csrf @method('DELETE')<input type="hidden" name="confirm_name"><button type="button" class="as-btn as-btn-danger" onclick="assessConfirmDelete('del-ba-{{ $record->id }}', @json($member->name))">Delete</button></form>@endif</td></tr><tr><td colspan="6" class="as-help">{{ $record->ai_insight ?: 'No AI insight generated yet.' }}</td></tr>@endforeach</tbody></table></div>
        @endif
    </div>
</div>
</x-layouts.admin>
