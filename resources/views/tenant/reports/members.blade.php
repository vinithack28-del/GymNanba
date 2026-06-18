<x-layouts.admin :title="__('reports.nav.members')">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('reports.nav.members') }}</h1>
        <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('reports.members.subtitle') }}</p>
    </div>
    <a href="{{ route('tenant.reports.index') }}" class="text-sm" style="color:var(--app-text-muted)">← {{ __('reports.nav.reports') }}</a>
</div>

@include('tenant.reports._filters', [
    'exportRoute' => route('tenant.reports.members.export') . '?',
])

{{-- KPI cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $vsNew = $kpis['prevNew'] > 0 ? round((($kpis['new'] - $kpis['prevNew']) / $kpis['prevNew']) * 100, 1) : null;
    @endphp
    <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <p class="text-xs mb-1" style="color:var(--app-text-muted)">{{ __('reports.members.kpi.new') }}</p>
        <p class="text-2xl font-bold" style="color:var(--app-text)">{{ number_format($kpis['new']) }}</p>
        @if ($vsNew !== null)
            <p class="text-xs mt-0.5 {{ $vsNew >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ $vsNew >= 0 ? '+' : '' }}{{ $vsNew }}% vs prev</p>
        @endif
    </div>
    <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <p class="text-xs mb-1" style="color:var(--app-text-muted)">{{ __('reports.members.kpi.churned') }}</p>
        <p class="text-2xl font-bold text-red-400">{{ number_format($kpis['churned']) }}</p>
        <p class="text-xs mt-0.5" style="color:var(--app-text-muted)">{{ $kpis['churnRate'] }}% {{ __('reports.members.kpi.churn_rate') }}</p>
    </div>
    <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <p class="text-xs mb-1" style="color:var(--app-text-muted)">{{ __('reports.members.kpi.retention') }}</p>
        <p class="text-2xl font-bold text-emerald-400">{{ $kpis['retentionRate'] }}%</p>
    </div>
    <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <p class="text-xs mb-1" style="color:var(--app-text-muted)">{{ __('reports.members.kpi.net_growth') }}</p>
        <p class="text-2xl font-bold {{ $kpis['netGrowth'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
            {{ $kpis['netGrowth'] >= 0 ? '+' : '' }}{{ number_format($kpis['netGrowth']) }}
        </p>
    </div>
</div>

{{-- Charts row 1: Trend + Gender donut --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    <div class="lg:col-span-2 rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <h3 class="text-sm font-semibold mb-3" style="color:var(--app-text)">{{ __('reports.members.chart.trend') }}</h3>
        <canvas id="memTrend" height="120"></canvas>
    </div>
    <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <h3 class="text-sm font-semibold mb-3" style="color:var(--app-text)">{{ __('reports.members.chart.gender') }}</h3>
        <canvas id="memGender" height="180"></canvas>
    </div>
</div>

{{-- Charts row 2: By Plan donut + Age bar --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <h3 class="text-sm font-semibold mb-3" style="color:var(--app-text)">{{ __('reports.members.chart.by_plan') }}</h3>
        <canvas id="memPlan" height="160"></canvas>
    </div>
    <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <h3 class="text-sm font-semibold mb-3" style="color:var(--app-text)">{{ __('reports.members.chart.age_group') }}</h3>
        <canvas id="memAge" height="160"></canvas>
    </div>
</div>

{{-- By Branch table --}}
@if ($byBranch->isNotEmpty() && $branches->count() > 1)
    <div class="rounded-2xl mb-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <div class="px-4 py-3 border-b" style="border-color:var(--app-border)">
            <h3 class="text-sm font-semibold" style="color:var(--app-text)">{{ __('reports.members.by_branch') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="color:var(--app-text-muted)">
                        <th class="text-left px-4 py-2 font-medium">{{ __('common.branch') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.members.col.count') }}</th>
                        <th class="text-right px-4 py-2 font-medium">%</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = $byBranch->sum('cnt'); @endphp
                    @foreach ($byBranch as $row)
                        <tr class="border-t" style="border-color:var(--app-border);color:var(--app-text)">
                            <td class="px-4 py-2.5">{{ $row['label'] }}</td>
                            <td class="px-4 py-2.5 text-right">{{ number_format($row['cnt']) }}</td>
                            <td class="px-4 py-2.5 text-right text-xs" style="color:var(--app-text-muted)">
                                {{ $total > 0 ? round($row['cnt'] / $total * 100, 1) : 0 }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Month-on-month --}}
@if ($monthlyComparison->isNotEmpty())
    <div class="rounded-2xl" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <div class="px-4 py-3 border-b" style="border-color:var(--app-border)">
            <h3 class="text-sm font-semibold" style="color:var(--app-text)">{{ __('reports.members.monthly_comparison') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="color:var(--app-text-muted)">
                        <th class="text-left px-4 py-2 font-medium">{{ __('reports.members.col.month') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.members.col.new') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.members.col.churned') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.members.col.net') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($monthlyComparison->sortByDesc('month') as $row)
                        <tr class="border-t" style="border-color:var(--app-border);color:var(--app-text)">
                            <td class="px-4 py-2.5">{{ \Carbon\Carbon::createFromFormat('Y-m', $row['month'])->format('M Y') }}</td>
                            <td class="px-4 py-2.5 text-right text-emerald-400">+{{ $row['new'] }}</td>
                            <td class="px-4 py-2.5 text-right text-red-400">-{{ $row['churned'] }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold {{ $row['net'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $row['net'] >= 0 ? '+' : '' }}{{ $row['net'] }}
                            </td>
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

const trendData  = @json($trend);
const planData   = @json($byPlan);
const genderData = @json($byGender);
const ageData    = @json($byAge);

if (trendData.length) {
    new Chart(document.getElementById('memTrend'), {
        type: 'line',
        data: {
            labels: trendData.map(r => r.date),
            datasets: [{ label: 'New Members', data: trendData.map(r => r.cnt), borderColor: BRAND, backgroundColor: BRAND + '18', fill: true, tension: 0.3, pointRadius: trendData.length > 30 ? 0 : 3 }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { color: gridColor() }, ticks: { color: textColor(), maxTicksLimit: 8 } }, y: { grid: { color: gridColor() }, ticks: { color: textColor() } } } }
    });
}

if (genderData.length) {
    new Chart(document.getElementById('memGender'), {
        type: 'doughnut',
        data: { labels: genderData.map(r => r.label), datasets: [{ data: genderData.map(r => r.cnt), backgroundColor: PALETTE }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { color: textColor(), boxWidth: 12 } } } }
    });
}

if (planData.length) {
    new Chart(document.getElementById('memPlan'), {
        type: 'doughnut',
        data: { labels: planData.map(r => r.label), datasets: [{ data: planData.map(r => r.cnt), backgroundColor: PALETTE }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { color: textColor(), boxWidth: 12 } } } }
    });
}

if (ageData.length) {
    new Chart(document.getElementById('memAge'), {
        type: 'bar',
        data: { labels: ageData.map(r => r.label), datasets: [{ label: 'Members', data: ageData.map(r => r.cnt), backgroundColor: PALETTE[0] }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { color: textColor() } }, y: { grid: { color: gridColor() }, ticks: { color: textColor() } } } }
    });
}
</script>
@endpush

</x-layouts.admin>
