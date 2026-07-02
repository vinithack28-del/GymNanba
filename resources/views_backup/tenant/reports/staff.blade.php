<x-layouts.admin :title="__('reports.nav.staff')">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('reports.nav.staff') }}</h1>
        <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('reports.staff.subtitle') }}</p>
    </div>
    <a href="{{ route('tenant.reports.index') }}" class="text-sm" style="color:var(--app-text-muted)">← {{ __('reports.nav.reports') }}</a>
</div>

@include('tenant.reports._filters', [
    'exportRoute' => route('tenant.reports.staff.export') . '?',
    'plans'       => null,
])

{{-- Attendance summary --}}
<div class="rounded-2xl mb-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
    <div class="px-4 py-3 border-b" style="border-color:var(--app-border)">
        <h3 class="text-sm font-semibold" style="color:var(--app-text)">{{ __('reports.staff.section.attendance') }}</h3>
    </div>
    @if (count($attendanceSummary) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="color:var(--app-text-muted)">
                        <th class="text-left px-4 py-2 font-medium">{{ __('reports.staff.col.name') }}</th>
                        <th class="text-left px-4 py-2 font-medium">{{ __('reports.staff.col.role') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.staff.col.days_present') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.staff.col.total_hours') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendanceSummary as $row)
                        <tr class="border-t" style="border-color:var(--app-border);color:var(--app-text)">
                            <td class="px-4 py-2.5 font-medium">{{ $row->name }}</td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs px-2 py-0.5 rounded-lg" style="background:var(--app-panel-strong);color:var(--app-text-muted)">
                                    {{ ucfirst(str_replace('_', ' ', $row->role)) }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-right">{{ $row->days_present }}</td>
                            <td class="px-4 py-2.5 text-right">{{ round($row->total_minutes / 60, 1) }}h</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="px-4 py-6 text-sm text-center" style="color:var(--app-text-muted)">{{ __('reports.staff.empty') }}</p>
    @endif
</div>

{{-- Classes by trainer --}}
<div class="rounded-2xl mb-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
    <div class="px-4 py-3 border-b" style="border-color:var(--app-border)">
        <h3 class="text-sm font-semibold" style="color:var(--app-text)">{{ __('reports.staff.section.classes') }}</h3>
    </div>
    @if (count($classesByTrainer) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="color:var(--app-text-muted)">
                        <th class="text-left px-4 py-2 font-medium">{{ __('reports.staff.col.trainer') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.staff.col.scheduled') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.staff.col.held') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.staff.col.cancelled') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.staff.col.pct_held') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($classesByTrainer as $row)
                        @php $pct = $row->scheduled > 0 ? round($row->held / $row->scheduled * 100) : 0; @endphp
                        <tr class="border-t" style="border-color:var(--app-border);color:var(--app-text)">
                            <td class="px-4 py-2.5 font-medium">{{ $row->trainer_name }}</td>
                            <td class="px-4 py-2.5 text-right">{{ $row->scheduled }}</td>
                            <td class="px-4 py-2.5 text-right text-emerald-400">{{ $row->held }}</td>
                            <td class="px-4 py-2.5 text-right text-red-400">{{ $row->cancelled }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <span class="{{ $pct >= 80 ? 'text-emerald-400' : ($pct >= 50 ? 'text-amber-400' : 'text-red-400') }}">
                                    {{ $pct }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="px-4 py-6 text-sm text-center" style="color:var(--app-text-muted)">{{ __('reports.staff.empty') }}</p>
    @endif
</div>

{{-- Fees collected --}}
<div class="rounded-2xl mb-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
    <div class="px-4 py-3 border-b" style="border-color:var(--app-border)">
        <h3 class="text-sm font-semibold" style="color:var(--app-text)">{{ __('reports.staff.section.fees') }}</h3>
    </div>
    @if (count($feesCollected) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="color:var(--app-text-muted)">
                        <th class="text-left px-4 py-2 font-medium">{{ __('reports.staff.col.name') }}</th>
                        <th class="text-left px-4 py-2 font-medium">{{ __('reports.staff.col.role') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.staff.col.payment_count') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.staff.col.total_collected') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($feesCollected as $row)
                        <tr class="border-t" style="border-color:var(--app-border);color:var(--app-text)">
                            <td class="px-4 py-2.5 font-medium">{{ $row->name }}</td>
                            <td class="px-4 py-2.5 text-xs" style="color:var(--app-text-muted)">{{ ucfirst(str_replace('_', ' ', $row->role)) }}</td>
                            <td class="px-4 py-2.5 text-right">{{ $row->payment_count }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold">₹{{ number_format($row->total / 100, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="px-4 py-6 text-sm text-center" style="color:var(--app-text-muted)">{{ __('reports.staff.empty') }}</p>
    @endif
</div>

{{-- POS sales --}}
<div class="rounded-2xl" style="background:var(--app-panel);border:1px solid var(--app-border)">
    <div class="px-4 py-3 border-b" style="border-color:var(--app-border)">
        <h3 class="text-sm font-semibold" style="color:var(--app-text)">{{ __('reports.staff.section.pos') }}</h3>
    </div>
    @if (count($posSales) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="color:var(--app-text-muted)">
                        <th class="text-left px-4 py-2 font-medium">{{ __('reports.staff.col.name') }}</th>
                        <th class="text-left px-4 py-2 font-medium">{{ __('reports.staff.col.role') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.staff.col.bill_count') }}</th>
                        <th class="text-right px-4 py-2 font-medium">{{ __('reports.staff.col.total_sales') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posSales as $row)
                        <tr class="border-t" style="border-color:var(--app-border);color:var(--app-text)">
                            <td class="px-4 py-2.5 font-medium">{{ $row->name }}</td>
                            <td class="px-4 py-2.5 text-xs" style="color:var(--app-text-muted)">{{ ucfirst(str_replace('_', ' ', $row->role)) }}</td>
                            <td class="px-4 py-2.5 text-right">{{ $row->bill_count }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold">₹{{ number_format($row->total / 100, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="px-4 py-6 text-sm text-center" style="color:var(--app-text-muted)">{{ __('reports.staff.empty') }}</p>
    @endif
</div>

</x-layouts.admin>
