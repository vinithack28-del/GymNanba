<x-layouts.admin :title="'Body Metrics Progress'">
@include('tenant.assess._styles')
<div class="as-shell">
    <div class="as-head"><div><div class="as-title">Body Metrics Progress</div><div class="as-sub">Weight, BMI, and body fat history for the selected client.</div></div></div>
    @include('tenant.assess._nav')
    @include('tenant.assess._member-picker', ['action' => route('tenant.assess.body-metrics.progress'), 'member' => $member])
    @if (! $member)
        <div class="as-panel as-empty">Select a client to view progress.</div>
    @else
        <div class="as-panel">
            <div class="as-kv">
                @foreach ($records->take(3) as $record)
                    <div class="as-kv-item">
                        <div class="as-kv-key">{{ $record->assessment_date?->format('d M Y') }}</div>
                        <div class="as-kv-val">Weight {{ data_get($record->payload, 'weight_kg') }} kg</div>
                        <div class="as-help">BMI {{ data_get($record->payload, 'bmi') }} · Body fat {{ data_get($record->payload, 'body_fat_pct', '—') }}</div>
                    </div>
                @endforeach
            </div>
            <div class="as-table-wrap" style="margin-top:1rem">
                <table class="as-table"><thead><tr><th>Date</th><th>Weight</th><th>BMI</th><th>Body fat</th></tr></thead><tbody>@foreach ($records as $record)<tr><td>{{ $record->assessment_date?->format('d M Y') }}</td><td>{{ data_get($record->payload, 'weight_kg') }}</td><td>{{ data_get($record->payload, 'bmi') }}</td><td>{{ data_get($record->payload, 'body_fat_pct', '—') }}</td></tr>@endforeach</tbody></table>
            </div>
        </div>
    @endif
</div>
</x-layouts.admin>
