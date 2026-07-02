<x-layouts.admin :title="__('reports.nav.revenue')">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('reports.nav.revenue') }}</h1>
        <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('reports.revenue.subtitle') }}</p>
    </div>
    <a href="{{ route('tenant.reports.index') }}" class="text-sm" style="color:var(--app-text-muted)">← {{ __('reports.nav.reports') }}</a>
</div>

@include('tenant.reports._filters', [
    'exportRoute' => route('tenant.reports.revenue.export') . '?',
])

{{-- KPI cards --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    @php
        $kpiCards = [
            ['label' => __('reports.revenue.kpi.total'),    'value' => '₹' . number_format($kpis['total'] / 100, 0), 'sub' => $kpis['vsChange'] !== null ? (($kpis['vsChange'] >= 0 ? '+' : '') . $kpis['vsChange'] . '% vs prev') : null, 'up' => ($kpis['vsChange'] ?? 0) >= 0],
            ['label' => __('reports.revenue.kpi.count'),    'value' => number_format($kpis['count']),                'sub' => __('reports.revenue.kpi.transactions')],
            ['label' => __('reports.revenue.kpi.avg'),      'value' => '₹' . number_format($kpis['avg'] / 100, 0),  'sub' => __('reports.revenue.kpi.per_txn')],
            ['label' => __('reports.revenue.kpi.gst'),      'value' => '₹' . number_format($kpis['gst'] / 100, 0),  'sub' => __('reports.revenue.kpi.gst_collected')],
            ['label' => __('reports.revenue.kpi.dues'),     'value' => '₹' . number_format($kpis['pendingDues'] / 100, 0), 'sub' => __('reports.revenue.kpi.outstanding'), 'warn' => true],
        ];
    @endphp
    @foreach ($kpiCards as $card)
        <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <p class="text-xs mb-1" style="color:var(--app-text-muted)">{{ $card['label'] }}</p>
            <p class="text-xl font-bold" style="color:{{ ($card['warn'] ?? false) ? '#f87171' : 'var(--app-text)' }}">{{ $card['value'] }}</p>
            @if (isset($card['sub']))
                <p class="text-xs mt-0.5 {{ isset($card['up']) ? ($card['up'] ? 'text-emerald-400' : 'text-red-400') : '' }}"
                   style="{{ isset($card['up']) ? '' : 'color:var(--app-text-muted)' }}">{{ $card['sub'] }}</p>
            @endif
        </div>
    @endforeach
</div>

{{-- Charts row 1: Trend + By Method --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    <div class="lg:col-span-2 rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <h3 class="text-sm font-semibold mb-3" style="color:var(--app-text)">{{ __('reports.revenue.chart.trend') }}</h3>
        <canvas id="revTrend" height="120"></canvas>
    </div>
    <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <h3 class="text-sm font-semibold mb-3" style="color:var(--app-text)">{{ __('reports.revenue.chart.by_method') }}</h3>
        <canvas id="revMethod" height="180"></canvas>
    </div>
</div>

{{-- Charts row 2: By Plan + By Branch --}}
<div class="grid grid-cols-1 {{ $byBranch ? 'lg:grid-cols-2' : '' }} gap-4 mb-6">
    @if ($byPlan->isNotEmpty())
        <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <h3 class="text-sm font-semibold mb-3" style="color:var(--app-text)">{{ __('reports.revenue.chart.by_plan') }}</h3>
            <canvas id="revPlan" height="140"></canvas>
        </div>
    @endif
    @if ($byBranch && $byBranch->isNotEmpty())
        <div class="rounded-2xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <h3 class="text-sm font-semibold mb-3" style="color:var(--app-text)">{{ __('reports.revenue.chart.by_branch') }}</h3>
            <canvas id="revBranch" height="140"></canvas>
        </div>
    @endif
</div>

{{-- Top 10 Members --}}
@if ($topMembers->isNotEmpty())
    <div class="rounded-2xl mb-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <div class="px-4 py-3 border-b" style="border-color:var(--app-border)">
            <h3 class="text-sm font-semibold" style="color:var(--app-text)">{{ __('reports.revenue.top_members') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="color:var(--app-text-muted)">
                        <th class="text-left px-4 py-2 font-medium">#</th>
                        <th class="text-left px-4 py-2 font-medium">{{ __('reports.revenue.col.member') }}</th>
                        <th class="text-left px-4 py-2 font-medium">{{ __('reports.revenue.col.plan') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.revenue.col.payments') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.revenue.col.total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topMembers as $i => $m)
                        <tr class="border-t" style="border-color:var(--app-border);color:var(--app-text)">
                            <td class="px-4 py-2.5 text-xs" style="color:var(--app-text-muted)">{{ $i + 1 }}</td>
                            <td class="px-4 py-2.5 font-medium">{{ $m['name'] }}</td>
                            <td class="px-4 py-2.5 text-xs" style="color:var(--app-text-muted)">{{ $m['plan'] }}</td>
                            <td class="px-4 py-2.5 text-right">{{ $m['cnt'] }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold">₹{{ number_format($m['total'] / 100, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Daily breakdown --}}
@if ($daily->isNotEmpty())
    <div class="rounded-2xl" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <div class="px-4 py-3 border-b" style="border-color:var(--app-border)">
            <h3 class="text-sm font-semibold" style="color:var(--app-text)">{{ __('reports.revenue.daily_breakdown') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="color:var(--app-text-muted)">
                        <th class="text-left px-4 py-2 font-medium">{{ __('reports.revenue.col.date') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.revenue.col.payments') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.revenue.col.subtotal') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.revenue.col.gst') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.revenue.col.total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daily as $row)
                        <tr class="border-t" style="border-color:var(--app-border);color:var(--app-text)">
                            <td class="px-4 py-2.5">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                            <td class="px-4 py-2.5 text-right">{{ $row->cnt }}</td>
                            <td class="px-4 py-2.5 text-right">₹{{ number_format($row->subtotal / 100, 0) }}</td>
                            <td class="px-4 py-2.5 text-right text-xs" style="color:var(--app-text-muted)">₹{{ number_format($row->gst / 100, 0) }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold">₹{{ number_format($row->total / 100, 0) }}</td>
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

const trendData = @json($trend);
const methodData = @json($byMethod);
const planData   = @json($byPlan);
const branchData = @json($byBranch ?? []);

// Trend line
if (trendData.length) {
    new Chart(document.getElementById('revTrend'), {
        type: 'line',
        data: {
            labels: trendData.map(r => r.date),
            datasets: [{
                label: '₹ Revenue',
                data: trendData.map(r => Math.round(r.total / 100)),
                borderColor: BRAND,
                backgroundColor: BRAND + '18',
                fill: true,
                tension: 0.3,
                pointRadius: trendData.length > 30 ? 0 : 3,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor() }, ticks: { color: textColor(), maxTicksLimit: 8 } },
                y: { grid: { color: gridColor() }, ticks: { color: textColor(), callback: v => '₹' + v.toLocaleString('en-IN') } }
            }
        }
    });
}

// Method donut
if (methodData.length) {
    new Chart(document.getElementById('revMethod'), {
        type: 'doughnut',
        data: {
            labels: methodData.map(r => r.method.toUpperCase()),
            datasets: [{ data: methodData.map(r => Math.round(r.total / 100)), backgroundColor: PALETTE }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: textColor(), boxWidth: 12 } },
                tooltip: { callbacks: { label: ctx => ' ₹' + ctx.parsed.toLocaleString('en-IN') } }
            }
        }
    });
}

// Plan bar
if (planData.length) {
    new Chart(document.getElementById('revPlan'), {
        type: 'bar',
        data: {
            labels: planData.map(r => r.label),
            datasets: [{ label: '₹', data: planData.map(r => Math.round(r.total / 100)), backgroundColor: PALETTE }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor() } },
                y: { grid: { color: gridColor() }, ticks: { color: textColor(), callback: v => '₹' + v.toLocaleString('en-IN') } }
            }
        }
    });
}

// Branch bar
if (branchData.length) {
    new Chart(document.getElementById('revBranch'), {
        type: 'bar',
        data: {
            labels: branchData.map(r => r.label),
            datasets: [{ label: '₹', data: branchData.map(r => Math.round(r.total / 100)), backgroundColor: PALETTE }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor() } },
                y: { grid: { color: gridColor() }, ticks: { color: textColor(), callback: v => '₹' + v.toLocaleString('en-IN') } }
            }
        }
    });
}
</script>
@endpush

</x-layouts.admin>
