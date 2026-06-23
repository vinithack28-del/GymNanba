<x-layouts.admin :title="$moduleTitles[$moduleKey]">
@include('tenant.assess._styles')

<div class="as-shell">
    <div class="as-head">
        <div>
            <div class="as-title">{{ $moduleTitles[$moduleKey] }}</div>
            <div class="as-sub">Latest assessment summary for the selected client.</div>
        </div>
        <button type="button" onclick="window.print()" class="as-btn as-btn-secondary" {{ $summary && $summary['sections_completed'] > 0 ? '' : 'disabled' }}>Download / Print</button>
    </div>

    @include('tenant.assess._nav')
    @include('tenant.assess._member-picker', ['action' => route('tenant.assess.report'), 'member' => $member])

    @if (! $member)
        <div class="as-panel as-empty">Select a client to load the assessment report.</div>
    @else
        <div class="as-stats">
            <div class="as-stat"><div class="as-stat-label">Overall score</div><div class="as-stat-value">{{ $summary['overall_score'] }}%</div></div>
            <div class="as-stat"><div class="as-stat-label">Sections completed</div><div class="as-stat-value">{{ $summary['sections_completed'] }} / 9</div></div>
            <div class="as-stat"><div class="as-stat-label">Last updated</div><div class="as-stat-value" style="font-size:1rem">{{ $summary['last_updated']?->format('d M Y') ?? '—' }}</div></div>
            <div class="as-stat"><div class="as-stat-label">Risk flags</div><div class="as-stat-value" style="font-size:1rem">{{ count($summary['risk_flags']) ? implode(', ', $summary['risk_flags']) : 'None' }}</div></div>
        </div>

        <div class="as-grid">
            @php
                $sections = [
                    'PAR-Q+' => $records['parq'] ?? null,
                    'Vitals' => $records['vitals'] ?? null,
                    'Body Composition' => $records['body_metrics'] ?? null,
                    'Posture' => $records['posture'] ?? null,
                    'Cardiorespiratory' => $records['fitness_cardio'] ?? null,
                    'Muscular Strength' => $records['fitness_strength'] ?? null,
                    'Muscular Endurance' => $records['fitness_endurance'] ?? null,
                    'Flexibility' => $records['fitness_flexibility'] ?? null,
                    'Balance' => $records['balance'] ?? null,
                ];
            @endphp
            @foreach ($sections as $label => $record)
                <div class="as-col-4">
                    <div class="as-panel-tight">
                        <div class="as-label">{{ $label }}</div>
                        @if ($record)
                            <div class="as-kv-key">Date</div>
                            <div class="as-kv-val">{{ $record->assessment_date?->format('d M Y') ?? '—' }}</div>
                            @if ($record->status)
                                <div class="as-help" style="margin-top:.35rem">Status: {{ str($record->status)->replace('_', ' ')->title() }}</div>
                            @endif
                            @if ($record->type === \App\Models\MemberAssessment::TYPE_BODY_METRICS)
                                <div class="as-help" style="margin-top:.35rem">BMI {{ data_get($record->payload, 'bmi', '—') }} · {{ data_get($record->payload, 'bmi_category', '—') }}</div>
                            @elseif ($record->type === \App\Models\MemberAssessment::TYPE_VITALS)
                                <div class="as-help" style="margin-top:.35rem">HR {{ data_get($record->payload, 'hr_bpm', '—') }} bpm · BP {{ data_get($record->payload, 'bp_systolic', '—') }}/{{ data_get($record->payload, 'bp_diastolic', '—') }}</div>
                            @elseif (str_starts_with($record->type, 'fitness_'))
                                <div class="as-help" style="margin-top:.35rem">{{ $record->title }}</div>
                            @endif
                        @else
                            <div class="as-help">No data available</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
</x-layouts.admin>
