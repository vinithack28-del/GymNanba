<x-layouts.admin :title="$moduleTitles[$moduleKey]">
@include('tenant.assess._styles')
<div class="as-shell">
    <div class="as-head"><div><div class="as-title">{{ $moduleTitles[$moduleKey] }}</div><div class="as-sub">View posture history and maintain posture records.</div></div></div>
    @include('tenant.assess._nav')
    <div class="as-stats">
        <div class="as-stat"><div class="as-stat-label">Total</div><div class="as-stat-value">{{ $summary['total'] }}</div></div>
        <div class="as-stat"><div class="as-stat-label">This month</div><div class="as-stat-value">{{ $summary['this_month'] }}</div></div>
        <div class="as-stat"><div class="as-stat-label">Last month</div><div class="as-stat-value">{{ $summary['last_month'] }}</div></div>
    </div>
    @if ($canAdd || ($editingRecord && $canEdit))
        <form method="POST" action="{{ $editingRecord ? route('tenant.assess.posture.update', $editingRecord) : route('tenant.assess.posture.store') }}" class="as-panel">
            @csrf @if ($editingRecord) @method('PUT') @endif
            <div class="as-grid">
                <div class="as-col-4"><label class="as-label">Client ID</label><input class="as-input" name="member_id" value="{{ old('member_id', $editingRecord->member_id ?? request('member_id')) }}" required></div>
                <div class="as-col-4"><label class="as-label">Assessment date</label><input class="as-input" type="date" name="assessment_date" value="{{ old('assessment_date', optional($editingRecord?->assessment_date)->toDateString() ?? now()->toDateString()) }}" required></div>
                <div class="as-col-4"><label class="as-label">Status</label><select class="as-select" name="status"><option value="reviewed" {{ old('status', $editingRecord->status ?? '') === 'reviewed' ? 'selected' : '' }}>Reviewed</option><option value="pending_review" {{ old('status', $editingRecord->status ?? '') === 'pending_review' ? 'selected' : '' }}>Pending review</option></select></div>
                @foreach (['head_alignment' => 'Head alignment', 'shoulder_alignment' => 'Shoulder alignment', 'spine_curvature' => 'Spine curvature', 'hip_tilt' => 'Hip tilt', 'knee_alignment' => 'Knee alignment', 'foot_position' => 'Foot position'] as $key => $label)
                    <div class="as-col-4"><label class="as-label">{{ $label }}</label><input class="as-input" name="{{ $key }}" value="{{ old($key, data_get($editingRecord?->payload, $key)) }}"></div>
                @endforeach
                <div class="as-col-12"><label class="as-label">Notes</label><textarea class="as-textarea" name="notes">{{ old('notes', $editingRecord->notes ?? '') }}</textarea></div>
            </div>
            <div class="as-actions" style="margin-top:1rem"><button class="as-btn as-btn-primary">{{ $editingRecord ? 'Save Assessment' : 'Add Assessment' }}</button></div>
        </form>
    @endif
    <div class="as-panel">
        <div class="as-table-wrap"><table class="as-table"><thead><tr><th>Client</th><th>Date</th><th>Status</th><th>Summary</th><th>Actions</th></tr></thead><tbody>@forelse($records as $record)<tr><td>{{ $record->member->name }}</td><td>{{ $record->assessment_date?->format('d M Y') }}</td><td>{{ str($record->status)->replace('_',' ')->title() }}</td><td>{{ collect($record->payload)->filter()->take(2)->map(fn($v,$k)=>str($k)->replace('_',' ')->title().': '.$v)->implode(', ') ?: '—' }}</td><td class="as-actions">@if($canEdit)<a class="as-btn as-btn-secondary" href="{{ route('tenant.assess.posture', ['edit' => $record->id]) }}">Edit</a>@endif<button class="as-btn as-btn-secondary" type="button" onclick="window.print()">Print</button>@if($canDelete)<form id="del-po-{{ $record->id }}" method="POST" action="{{ route('tenant.assess.records.destroy', $record) }}">@csrf @method('DELETE')<input type="hidden" name="confirm_name"><button type="button" class="as-btn as-btn-danger" onclick="assessConfirmDelete('del-po-{{ $record->id }}', @json($record->member->name))">Delete</button></form>@endif</td></tr>@empty<tr><td colspan="5" class="as-empty">No posture assessments found.</td></tr>@endforelse</tbody></table></div>
        <div style="margin-top:1rem">{{ $records->links() }}</div>
    </div>
</div>
</x-layouts.admin>
