<x-layouts.admin :title="__('payments.nav.dues')">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('payments.nav.dues') }}</h1>
        <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('payments.dues.subtitle') }}</p>
    </div>
    <a href="{{ route('tenant.payments.collect') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
       style="background:var(--app-brand)">
        + {{ __('payments.nav.collect') }}
    </a>
</div>

{{-- Total due banner --}}
@if ($totalDuePaise > 0)
    <div class="rounded-xl p-4 mb-6 flex items-center justify-between"
         style="background:color-mix(in srgb, #ef4444 10%, transparent);border:1px solid color-mix(in srgb, #ef4444 40%, transparent)">
        <div>
            <p class="text-sm font-semibold text-red-600">{{ __('payments.dues.total_due') }}</p>
            <p class="text-xs text-red-500 mt-0.5">{{ $payments->total() }} {{ __('payments.dues.members_due') }}</p>
        </div>
        <p class="text-2xl font-bold text-red-600">₹{{ number_format($totalDuePaise / 100, 0) }}</p>
    </div>
@endif

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-4 items-end">
    <div>
        <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('common.search') }}</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="{{ __('payments.dues.search_placeholder') }}"
               class="px-3 py-1.5 rounded-lg border text-sm w-52"
               style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
    </div>
    <div>
        <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('common.branch') }}</label>
        <select name="branch_id"
                class="px-3 py-1.5 rounded-lg border text-sm"
                style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
            <option value="">{{ __('common.all') }}</option>
            @foreach ($branches as $b)
                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit"
            class="px-4 py-1.5 rounded-lg text-sm font-medium text-white"
            style="background:var(--app-brand)">{{ __('common.filter') }}</button>
    @if (request()->hasAny(['search','branch_id']))
        <a href="{{ route('tenant.payments.dues') }}"
           class="px-3 py-1.5 rounded-lg text-sm border"
           style="border-color:var(--app-border);color:var(--app-text-muted)">{{ __('common.clear') }}</a>
    @endif
</form>

{{-- Table --}}
<div class="rounded-xl overflow-hidden" style="border:1px solid var(--app-border)">
    <div class="overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead style="background:var(--app-panel-strong)">
                <tr>
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('payments.dues.member') }}</th>
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('payments.history.plan') }}</th>
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('payments.history.receipt') }}</th>
                    <th class="text-right px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('payments.history.total') }}</th>
                    <th class="text-right px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">Paid</th>
                    <th class="text-right px-4 py-2.5 font-medium text-red-500">{{ __('payments.dues.amount_due') }}</th>
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">Due Date</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    @php $isOverdue = $payment->due_date && \Carbon\Carbon::parse($payment->due_date)->isPast(); @endphp
                    <tr class="border-t hover:opacity-90 transition-opacity"
                        style="border-color:var(--app-border);background:var(--app-panel)">
                        <td class="px-4 py-3" style="color:var(--app-text)">
                            <div class="font-medium">{{ $payment->member?->name ?? '—' }}</div>
                            <div class="text-xs" style="color:var(--app-text-muted)">
                                {{ $payment->member?->member_code }}
                                @if($payment->member?->phone) · {{ $payment->member->phone }} @endif
                            </div>
                            @if($payment->member?->branch)
                                <div class="text-xs" style="color:var(--app-text-muted)">{{ $payment->member->branch->name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs" style="color:var(--app-text-muted)">
                            {{ $payment->plan?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs font-mono" style="color:var(--app-text-muted)">
                            {{ $payment->receipt_number }}
                            <div class="text-xs">{{ $payment->payment_date?->format('d M Y') }}</div>
                        </td>
                        <td class="px-4 py-3 text-right" style="color:var(--app-text)">
                            ₹{{ number_format($payment->total_paise / 100, 0) }}
                        </td>
                        <td class="px-4 py-3 text-right" style="color:var(--app-text-muted)">
                            ₹{{ number_format($payment->paid_paise / 100, 0) }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-red-500">
                            ₹{{ number_format($payment->due_paise / 100, 0) }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($payment->due_date)
                                <span class="{{ $isOverdue ? 'text-red-500 font-semibold' : '' }}" style="{{ $isOverdue ? '' : 'color:var(--app-text-muted)' }}">
                                    {{ \Carbon\Carbon::parse($payment->due_date)->format('d M Y') }}
                                    @if($isOverdue) <span class="text-xs">(overdue)</span> @endif
                                </span>
                            @else
                                <span style="color:var(--app-text-muted)">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('tenant.payments.collect') }}?member_id={{ $payment->member_id }}"
                               class="text-xs px-3 py-1 rounded-lg text-white whitespace-nowrap"
                               style="background:var(--app-brand)">
                                {{ __('payments.dues.collect_btn') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm" style="color:var(--app-text-muted)">
                            {{ __('payments.dues.empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $payments->links() }}</div>

</x-layouts.admin>
