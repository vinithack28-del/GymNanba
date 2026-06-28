<x-layouts.admin :title="__('common.tenant_nav.payments')">

@php
    $activeTab = ($activeTab ?? '') === 'history' ? 'history' : 'dues';
@endphp

<div class="flex items-center justify-between mb-6">
    <div class="inline-flex rounded-xl p-1" style="background:var(--app-panel-strong);border:1px solid var(--app-border)">
        <a href="{{ route('tenant.payments.history', array_merge(request()->except(['tab', 'history_page', 'dues_page']), ['tab' => 'dues'])) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition"
           style="{{ $activeTab === 'dues' ? 'background:var(--app-panel);color:var(--app-text);box-shadow:0 1px 2px rgba(15,23,42,0.08)' : 'color:var(--app-text-muted)' }}">
            {{ __('payments.nav.dues') }}
        </a>
        <a href="{{ route('tenant.payments.history', array_merge(request()->except(['tab', 'history_page', 'dues_page']), ['tab' => 'history'])) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition"
           style="{{ $activeTab === 'history' ? 'background:var(--app-panel);color:var(--app-text);box-shadow:0 1px 2px rgba(15,23,42,0.08)' : 'color:var(--app-text-muted)' }}">
            {{ __('payments.nav.history') }}
        </a>
    </div>
    <a href="{{ route('tenant.payments.collect') }}"
       class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-semibold text-white"
       style="background:var(--app-brand)">
        + {{ __('payments.nav.collect') }}
    </a>
</div>

@if ($activeTab === 'history')
    <div class="grid grid-cols-2 sm:grid-cols-2 gap-4 mb-6">
        <div class="rounded-xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <p class="text-xs font-medium uppercase tracking-wide" style="color:var(--app-text-muted)">{{ __('payments.history.today_count') }}</p>
            <p class="text-2xl font-bold mt-1" style="color:var(--app-text)">{{ $todaySummary['count'] }}</p>
        </div>
        <div class="rounded-xl p-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <p class="text-xs font-medium uppercase tracking-wide" style="color:var(--app-text-muted)">{{ __('payments.history.today_total') }}</p>
            <p class="text-2xl font-bold mt-1" style="color:var(--app-brand)">₹{{ number_format($todaySummary['total_paise'] / 100, 0) }}</p>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-4 items-end">
        <input type="hidden" name="tab" value="history">
        <div>
            <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('common.search') }}</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="{{ __('payments.history.search_placeholder') }}"
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
        <div>
            <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('payments.history.method') }}</label>
            <select name="method"
                    class="px-3 py-1.5 rounded-lg border text-sm"
                    style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
                <option value="">{{ __('common.all') }}</option>
                @foreach (\App\Models\Payment::METHODS as $m)
                    <option value="{{ $m }}" {{ request('method') === $m ? 'selected' : '' }}>{{ __('payments.methods.' . $m) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('payments.history.status') }}</label>
            <select name="status"
                    class="px-3 py-1.5 rounded-lg border text-sm"
                    style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
                <option value="">{{ __('common.all') }}</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('payments.status.active') }}</option>
                <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>{{ __('payments.status.voided') }}</option>
            </select>
        </div>
        <div>
            <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('payments.history.date_from') }}</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="px-3 py-1.5 rounded-lg border text-sm"
                   style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
        </div>
        <div>
            <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('payments.history.date_to') }}</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="px-3 py-1.5 rounded-lg border text-sm"
                   style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
        </div>
        <button type="submit"
                class="px-4 py-1.5 rounded-lg text-sm font-medium text-white"
                style="background:var(--app-brand)">{{ __('common.filter') }}</button>
        @if (request()->hasAny(['search','branch_id','method','status','date_from','date_to']))
            <a href="{{ route('tenant.payments.history', ['tab' => 'history']) }}"
               class="px-3 py-1.5 rounded-lg text-sm border"
               style="border-color:var(--app-border);color:var(--app-text-muted)">{{ __('common.clear') }}</a>
        @endif
    </form>

    <div class="rounded-xl overflow-hidden" style="border:1px solid var(--app-border)">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-sm">
                <thead style="background:var(--app-panel-strong)">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('payments.history.receipt') }}</th>
                        <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('payments.history.member') }}</th>
                        <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('payments.history.plan') }}</th>
                        <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('payments.history.method') }}</th>
                        <th class="text-right px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('payments.history.amount') }}</th>
                        <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('payments.history.date') }}</th>
                        <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('payments.history.status') }}</th>
                        <th class="px-4 py-2.5"></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // List is DESC order; first occurrence = newest row per member
                        $firstPaymentIdPerMember = [];
                        $memberTotalDue = [];
                        foreach ($payments as $p) {
                            if (!isset($firstPaymentIdPerMember[$p->member_id])) {
                                $firstPaymentIdPerMember[$p->member_id] = $p->id;
                            }
                            $memberTotalDue[$p->member_id] = ($memberTotalDue[$p->member_id] ?? 0) + (int) $p->due_paise;
                        }
                    @endphp
                    @forelse ($payments as $payment)
                        <tr class="border-t hover:opacity-90 transition-opacity"
                            style="border-color:var(--app-border);background:{{ $payment->status === 'voided' ? 'var(--app-panel-strong)' : 'var(--app-panel)' }}">
                            <td class="px-4 py-3 font-mono text-xs" style="color:var(--app-text)">
                                <a href="{{ route('tenant.payments.receipt', $payment) }}"
                                   class="hover:underline" style="color:var(--app-brand)">
                                    {{ $payment->receipt_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3" style="color:var(--app-text)">
                                <div class="font-medium">{{ $payment->member->name }}</div>
                                <div class="text-xs" style="color:var(--app-text-muted)">{{ $payment->member->phone }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs" style="color:var(--app-text-muted)">
                                {{ $payment->plan?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3" style="color:var(--app-text)">
                                {{ __('payments.methods.' . $payment->method) }}
                                @if ($payment->reference)
                                    <div class="text-xs font-mono" style="color:var(--app-text-muted)">{{ $payment->reference }}</div>
                                @endif
                            </td>
                            @php
                                $isNewestRowForMember = ($firstPaymentIdPerMember[$payment->member_id] ?? null) === $payment->id;
                                $memberDuePaise = $isNewestRowForMember ? ($memberTotalDue[$payment->member_id] ?? 0) : 0;
                                $isActualPartial = $payment->is_partial && $payment->due_paise > 0;
                            @endphp
                            <td class="px-4 py-3 text-right" style="color:var(--app-text)">
                                @if ($isActualPartial)
                                    <div class="font-semibold">₹{{ number_format($payment->paid_paise / 100, 0) }}</div>
                                    <div class="text-xs" style="color:var(--app-text-muted)">of ₹{{ number_format($payment->total_paise / 100, 0) }}</div>
                                @else
                                    <div class="font-semibold">₹{{ number_format($payment->total_paise / 100, 0) }}</div>
                                    @if ($payment->gst_paise > 0)
                                        <div class="text-xs font-normal" style="color:var(--app-text-muted)">
                                            +₹{{ number_format($payment->gst_paise / 100, 0) }} GST
                                        </div>
                                    @endif
                                @endif
                                @if ($memberDuePaise > 0)
                                    <div class="text-xs font-semibold" style="color:#ea580c">₹{{ number_format($memberDuePaise / 100, 0) }} due</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs" style="color:var(--app-text-muted)">
                                {{ $payment->payment_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($payment->status === 'voided')
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">{{ __('payments.status.voided') }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">{{ __('payments.status.active') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('tenant.payments.receipt', $payment) }}"
                                       class="text-xs px-2 py-1 rounded border"
                                       style="border-color:var(--app-border);color:var(--app-text-muted)">
                                        {{ __('payments.history.receipt_btn') }}
                                    </a>
                                    @if ($payment->status === 'active' && auth()->user()->role !== 'receptionist')
                                        <button type="button"
                                                onclick="phOpenVoid({{ $payment->id }}, '{{ $payment->receipt_number }}')"
                                                class="text-xs px-2 py-1 rounded border border-red-300 text-red-600 hover:bg-red-50">
                                            {{ __('payments.history.void_btn') }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm" style="color:var(--app-text-muted)">
                                {{ __('payments.history.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
@else
    @if ($totalDuePaise > 0)
        <div class="rounded-xl p-4 mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
             style="background:color-mix(in srgb, #ef4444 10%, transparent);border:1px solid color-mix(in srgb, #ef4444 40%, transparent)">
            <div>
                <p class="text-sm font-semibold text-red-600">{{ __('payments.dues.total_due') }}</p>
                <p class="text-xs text-red-500 mt-0.5">{{ $duePayments->total() }} {{ __('payments.dues.members_due') }}</p>
            </div>
            <p class="text-2xl font-bold text-red-600">₹{{ number_format($totalDuePaise / 100, 0) }}</p>
        </div>
    @endif

    <form method="GET" class="flex flex-wrap gap-3 mb-4 items-end">
        <input type="hidden" name="tab" value="dues">
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
            <a href="{{ route('tenant.payments.history', ['tab' => 'dues']) }}"
               class="px-3 py-1.5 rounded-lg text-sm border"
               style="border-color:var(--app-border);color:var(--app-text-muted)">{{ __('common.clear') }}</a>
        @endif
    </form>

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
                    @forelse ($duePayments as $dp)
                        @php $isOverdue = $dp->due_date && \Carbon\Carbon::parse($dp->due_date)->isPast(); @endphp
                        <tr class="border-t hover:opacity-90 transition-opacity"
                            style="border-color:var(--app-border);background:var(--app-panel)">
                            <td class="px-4 py-3" style="color:var(--app-text)">
                                <div class="font-medium">{{ $dp->member?->name ?? '—' }}</div>
                                <div class="text-xs" style="color:var(--app-text-muted)">
                                    {{ $dp->member?->member_code }}
                                    @if($dp->member?->phone) · {{ $dp->member->phone }} @endif
                                </div>
                                @if($dp->member?->branch)
                                    <div class="text-xs" style="color:var(--app-text-muted)">{{ $dp->member->branch->name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs" style="color:var(--app-text-muted)">
                                {{ $dp->plan?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs font-mono" style="color:var(--app-text-muted)">
                                {{ $dp->receipt_number }}
                                <div>{{ $dp->payment_date?->format('d M Y') }}</div>
                            </td>
                            <td class="px-4 py-3 text-right" style="color:var(--app-text)">
                                ₹{{ number_format($dp->total_paise / 100, 0) }}
                            </td>
                            <td class="px-4 py-3 text-right" style="color:var(--app-text-muted)">
                                ₹{{ number_format(($dp->total_paise - $dp->due_paise) / 100, 0) }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-red-500">
                                ₹{{ number_format($dp->due_paise / 100, 0) }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($dp->due_date)
                                    <span class="{{ $isOverdue ? 'text-red-500 font-semibold' : '' }}" style="{{ $isOverdue ? '' : 'color:var(--app-text-muted)' }}">
                                        {{ \Carbon\Carbon::parse($dp->due_date)->format('d M Y') }}
                                        @if($isOverdue) <span class="text-xs">(overdue)</span> @endif
                                    </span>
                                @else
                                    <span style="color:var(--app-text-muted)">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('tenant.payments.collect') }}?member_id={{ $dp->member_id }}"
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

    <div class="mt-4">{{ $duePayments->links() }}</div>
@endif

<div id="phVoidModal" class="fixed inset-0 z-[300] hidden flex items-center justify-center" style="background:rgba(0,0,0,0.5)">
    <div class="rounded-xl p-6 w-full max-w-md mx-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <h3 class="font-semibold text-base mb-1" style="color:var(--app-text)">{{ __('payments.void.title') }}</h3>
        <p id="phVoidDesc" class="text-sm mb-4" style="color:var(--app-text-muted)"></p>

        <form id="phVoidForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('payments.void.reason_label') }}</label>
                <select name="void_reason" required
                        class="w-full px-3 py-2 rounded-lg border text-sm"
                        style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    <option value="">— {{ __('payments.void.select_reason') }} —</option>
                    @foreach (\App\Models\Payment::VOID_REASONS as $r)
                        <option value="{{ $r }}">{{ __('payments.void_reasons.' . $r) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="phCloseVoid()"
                        class="px-4 py-2 rounded-lg text-sm border"
                        style="border-color:var(--app-border);color:var(--app-text-muted)">
                    {{ __('common.cancel') }}
                </button>
                <button type="submit"
                        class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600">
                    {{ __('payments.void.confirm') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function phOpenVoid(id, receipt) {
    document.getElementById('phVoidDesc').textContent = '{{ __('payments.void.desc_prefix') }} ' + receipt;
    const base = '{{ rtrim(route('tenant.payments.void', ['payment' => '__ID__']), '') }}';
    document.getElementById('phVoidForm').action = base.replace('__ID__', id);
    document.getElementById('phVoidModal').classList.remove('hidden');
}
function phCloseVoid() {
    document.getElementById('phVoidModal').classList.add('hidden');
}
</script>

</x-layouts.admin>
