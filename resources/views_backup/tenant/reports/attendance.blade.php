<x-layouts.admin :title="__('reports.nav.attendance')">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('reports.nav.attendance') }}</h1>
        <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('reports.attendance.subtitle') }}</p>
    </div>
    <a href="{{ route('tenant.reports.index') }}" class="text-sm" style="color:var(--app-text-muted)">← {{ __('reports.nav.reports') }}</a>
</div>

@include('tenant.reports._filters', [
    'exportRoute' => route('tenant.reports.attendance.export') . '?',
    'plans'       => null,
])

{{-- KPI cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <p class="text-xs mb-1" style="color:var(--app-text-muted)">{{ __('reports.attendance.kpi.total') }}</p>
        <p class="text-2xl font-bold" style="color:var(--app-text)">{{ number_format($kpis['total']) }}</p>
    </div>
    <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <p class="text-xs mb-1" style="color:var(--app-text-muted)">{{ __('reports.attendance.kpi.unique') }}</p>
        <p class="text-2xl font-bold" style="color:var(--app-text)">{{ number_format($kpis['unique']) }}</p>
    </div>
    <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <p class="text-xs mb-1" style="color:var(--app-text-muted)">{{ __('reports.attendance.kpi.walkins') }}</p>
        <p class="text-2xl font-bold" style="color:var(--app-text)">{{ number_format($kpis['walkins']) }}</p>
    </div>
    <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <p class="text-xs mb-1" style="color:var(--app-text-muted)">{{ __('reports.attendance.kpi.avg_per_member') }}</p>
        <p class="text-2xl font-bold" style="color:var(--app-text)">{{ $kpis['avgPerMember'] }}</p>
        <p class="text-xs mt-0.5" style="color:var(--app-text-muted)">{{ __('reports.attendance.kpi.visits') }}</p>
    </div>
</div>

{{-- Charts row 1: Trend + Method donut --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    <div class="lg:col-span-2 rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <h3 class="text-sm font-semibold mb-3" style="color:var(--app-text)">{{ __('reports.attendance.chart.trend') }}</h3>
        <canvas id="attTrend" height="120"></canvas>
    </div>
    <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <h3 class="text-sm font-semibold mb-3" style="color:var(--app-text)">{{ __('reports.attendance.chart.by_method') }}</h3>
        <canvas id="attMethod" height="180"></canvas>
    </div>
</div>

{{-- Stacked bar: members vs walkins --}}
@if (count($stackedBar) > 0)
    <div class="rounded-2xl p-4 mb-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <h3 class="text-sm font-semibold mb-3" style="color:var(--app-text)">{{ __('reports.attendance.chart.daily_breakdown') }}</h3>
        <canvas id="attStacked" height="110"></canvas>
    </div>
@endif

{{-- Heatmap 7×24 --}}
<div class="rounded-2xl p-4 mb-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
    <h3 class="text-sm font-semibold mb-4" style="color:var(--app-text)">{{ __('reports.attendance.chart.heatmap') }}</h3>

    @php
        $days  = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        $hours = range(0, 23);
        $max   = $heatmapMax ?: 1;
    @endphp

    {{-- Hour labels --}}
    <div class="overflow-x-auto">
        <div style="display:grid;grid-template-columns:3rem repeat(24,1fr);gap:2px;min-width:680px">
            {{-- Header row --}}
            <div></div>
            @foreach ($hours as $h)
                <div class="text-center text-xs" style="color:var(--app-text-muted)">{{ $h }}</div>
            @endforeach

            {{-- Data rows --}}
            @foreach ($days as $di => $day)
                <div class="text-xs self-center pr-2 text-right" style="color:var(--app-text-muted)">{{ $day }}</div>
                @foreach ($hours as $h)
                    @php
                        $val   = $heatmap[$di][$h] ?? 0;
                        $alpha = $max > 0 ? round($val / $max, 2) : 0;
                    @endphp
                    <div title="{{ $val }} check-ins"
                         style="height:20px;border-radius:4px;background:color-mix(in srgb,var(--app-brand) {{ round($alpha * 100) }}%,var(--app-border))">
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex items-center gap-2 mt-3">
        <span class="text-xs" style="color:var(--app-text-muted)">{{ __('reports.attendance.heatmap.less') }}</span>
        @foreach ([0, 0.25, 0.5, 0.75, 1] as $a)
            <div style="width:16px;height:16px;border-radius:3px;background:color-mix(in srgb,var(--app-brand) {{ round($a * 100) }}%,var(--app-border))"></div>
        @endforeach
        <span class="text-xs" style="color:var(--app-text-muted)">{{ __('reports.attendance.heatmap.more') }}</span>
    </div>
</div>

{{-- Class attendance summary --}}
@if (count($classSummary) > 0)
    <div class="rounded-2xl" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <div class="px-4 py-3 border-b" style="border-color:var(--app-border)">
            <h3 class="text-sm font-semibold" style="color:var(--app-text)">{{ __('reports.attendance.class_summary') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="color:var(--app-text-muted)">
                        <th class="text-left px-4 py-2 font-medium">{{ __('reports.attendance.col.class') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.attendance.col.sessions') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.attendance.col.attendees') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.attendance.col.avg_fill') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($classSummary as $row)
                        <tr class="border-t" style="border-color:var(--app-border);color:var(--app-text)">
                            <td class="px-4 py-2.5 font-medium">{{ $row->class_name }}</td>
                            <td class="px-4 py-2.5 text-right">{{ $row->sessions }}</td>
                            <td class="px-4 py-2.5 text-right">{{ number_format($row->total_attendees) }}</td>
                            <td class="px-4 py-2.5 text-right">{{ $row->avg_per_session }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const BRAND   = getComputedStyle(document.documentElement).getPropertyValue('--app-brand').trim() || '#6366f1';
const PALETTE = ['#6366f1','#22d3ee','#f59e0b','#10b981','#f87171','#a78bfa'];
const gridColor = () => getComputedStyle(document.documentElement).getPropertyValue('--app-border').trim() || '#334155';
const textColor = () => getComputedStyle(document.documentElement).getPropertyValue('--app-text').trim() || '#e2e8f0';

const trendData   = @json($trend);
const methodData  = @json($byMethod);
const stackedData = @json($stackedBar);

if (trendData.length) {
    new Chart(document.getElementById('attTrend'), {
        type: 'line',
        data: {
            labels: trendData.map(r => r.date),
            datasets: [{ label: 'Check-ins', data: trendData.map(r => r.cnt), borderColor: BRAND, backgroundColor: BRAND + '18', fill: true, tension: 0.3, pointRadius: trendData.length > 30 ? 0 : 3 }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { color: gridColor() }, ticks: { color: textColor(), maxTicksLimit: 8 } }, y: { grid: { color: gridColor() }, ticks: { color: textColor() } } } }
    });
}

if (methodData.length) {
    new Chart(document.getElementById('attMethod'), {
        type: 'doughnut',
        data: { labels: methodData.map(r => r.label), datasets: [{ data: methodData.map(r => r.cnt), backgroundColor: PALETTE }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { color: textColor(), boxWidth: 12 } } } }
    });
}

if (stackedData.length && document.getElementById('attStacked')) {
    new Chart(document.getElementById('attStacked'), {
        type: 'bar',
        data: {
            labels: stackedData.map(r => r.date),
            datasets: [
                { label: 'Members',  data: stackedData.map(r => r.members),  backgroundColor: PALETTE[0], stack: 's' },
                { label: 'Walk-ins', data: stackedData.map(r => r.walkins),  backgroundColor: PALETTE[2], stack: 's' },
            ]
        },
        options: { responsive: true, plugins: { legend: { labels: { color: textColor(), boxWidth: 12 } } }, scales: { x: { stacked: true, grid: { display: false }, ticks: { color: textColor(), maxTicksLimit: 10 } }, y: { stacked: true, grid: { color: gridColor() }, ticks: { color: textColor() } } } }
    });
}
</script>
@endpush

</x-layouts.admin>
