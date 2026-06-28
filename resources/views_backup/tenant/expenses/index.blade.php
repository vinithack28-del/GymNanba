<x-layouts.admin :title="__('expenses.nav.expenses')">

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('expenses.nav.expenses') }}</h1>
        <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('expenses.index.subtitle') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('tenant.expenses.export', request()->query()) }}"
           class="px-3 py-1.5 text-sm rounded-lg border"
           style="border-color:var(--app-border);color:var(--app-text-muted)">
            ↓ CSV
        </a>
        <a href="{{ route('tenant.expenses.create') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
           style="background:var(--app-brand)">
            + {{ __('expenses.nav.add') }}
        </a>
    </div>
</div>

{{-- Monthly summary panel --}}
<div class="rounded-xl p-5 mb-5" style="background:var(--app-panel);border:1px solid var(--app-border)">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide" style="color:var(--app-text-muted)">{{ __('expenses.summary.this_month') }}</p>
            <p class="text-2xl font-bold mt-0.5" style="color:var(--app-text)">₹{{ number_format($summary['thisTotal'] / 100, 0) }}</p>
        </div>
        @if ($summary['vsLastPct'] !== null)
            <div class="text-sm px-3 py-1 rounded-full {{ $summary['vsLastPct'] > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                {{ $summary['vsLastPct'] > 0 ? '↑' : '↓' }} {{ abs($summary['vsLastPct']) }}% {{ __('expenses.summary.vs_last_month') }}
            </div>
        @endif
    </div>

    @if (count($summary['byCategory']))
        <div class="space-y-2">
            @foreach ($summary['byCategory'] as $cat)
                <div class="flex items-center gap-3">
                    <span class="w-28 text-xs truncate" style="color:var(--app-text-muted)">{{ __('expenses.categories.' . $cat['category']) }}</span>
                    <div class="flex-1 h-2 rounded-full overflow-hidden" style="background:var(--app-panel-strong)">
                        <div class="h-2 rounded-full" style="width:{{ $cat['pct'] }}%;background:var(--app-brand)"></div>
                    </div>
                    <span class="text-xs font-medium w-10 text-right" style="color:var(--app-text)">{{ $cat['pct'] }}%</span>
                    <span class="text-xs w-24 text-right" style="color:var(--app-text-muted)">₹{{ number_format($cat['total'] / 100, 0) }}</span>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm" style="color:var(--app-text-muted)">{{ __('expenses.summary.no_data') }}</p>
    @endif
</div>

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-4 items-end">
    <div>
        <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('common.search') }}</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="{{ __('expenses.index.search_placeholder') }}"
               class="px-3 py-1.5 rounded-lg border text-sm w-48"
               style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
    </div>
    <div>
        <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.category') }}</label>
        <select name="category"
                class="px-3 py-1.5 rounded-lg border text-sm"
                style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
            <option value="">{{ __('common.all') }}</option>
            @foreach (array_keys(\App\Models\Expense::CATEGORIES) as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ __('expenses.categories.' . $cat) }}</option>
            @endforeach
        </select>
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
        <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('expenses.form.method') }}</label>
        <select name="method"
                class="px-3 py-1.5 rounded-lg border text-sm"
                style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
            <option value="">{{ __('common.all') }}</option>
            @foreach (\App\Models\Expense::METHODS as $m)
                <option value="{{ $m }}" {{ request('method') === $m ? 'selected' : '' }}>{{ __('expenses.methods.' . $m) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('common.status') }}</label>
        <select name="status"
                class="px-3 py-1.5 rounded-lg border text-sm"
                style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
            <option value="">{{ __('common.all') }}</option>
            @foreach (\App\Models\Expense::STATUSES as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ __('expenses.status.' . $s) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('common.from') }}</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="px-3 py-1.5 rounded-lg border text-sm"
               style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
    </div>
    <div>
        <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('common.to') }}</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="px-3 py-1.5 rounded-lg border text-sm"
               style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
    </div>
    <button type="submit"
            class="px-4 py-1.5 rounded-lg text-sm font-medium text-white"
            style="background:var(--app-brand)">{{ __('common.filter') }}</button>
    @if (request()->hasAny(['search','category','branch_id','method','status','date_from','date_to']))
        <a href="{{ route('tenant.expenses.index') }}"
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
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('expenses.table.date') }}</th>
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('expenses.table.category') }}</th>
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('expenses.table.description') }}</th>
                    <th class="text-right px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('expenses.table.amount') }}</th>
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('expenses.table.method') }}</th>
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('expenses.table.branch') }}</th>
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('expenses.table.status') }}</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expenses as $expense)
                    <tr class="border-t hover:opacity-90 transition-opacity"
                        style="border-color:var(--app-border);background:var(--app-panel)">
                        <td class="px-4 py-3 text-xs whitespace-nowrap" style="color:var(--app-text-muted)">
                            {{ $expense->date->format('d M Y') }}
                            @if ($expense->is_recurring)
                                <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-600">↻</span>
                            @endif
                        </td>
                        <td class="px-4 py-3" style="color:var(--app-text)">
                            <div>{{ __('expenses.categories.' . $expense->category) }}</div>
                            @if ($expense->sub_category)
                                <div class="text-xs" style="color:var(--app-text-muted)">{{ __('expenses.sub_categories.' . $expense->category . '.' . $expense->sub_category) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 max-w-[220px]" style="color:var(--app-text)">
                            <div class="truncate">{{ $expense->description }}</div>
                            @if ($expense->vendor)
                                <div class="text-xs truncate" style="color:var(--app-text-muted)">{{ $expense->vendor }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold whitespace-nowrap" style="color:var(--app-text)">
                            ₹{{ number_format($expense->amount_paise / 100, 0) }}
                            @if ($expense->gst_paise > 0)
                                <div class="text-xs font-normal" style="color:var(--app-text-muted)">+₹{{ number_format($expense->gst_paise / 100, 0) }} GST</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs" style="color:var(--app-text)">
                            {{ __('expenses.methods.' . $expense->method) }}
                        </td>
                        <td class="px-4 py-3 text-xs" style="color:var(--app-text-muted)">
                            {{ $expense->branch?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $badge = match($expense->status) {
                                    'approved' => 'bg-green-100 text-green-700',
                                    'pending'  => 'bg-amber-100 text-amber-700',
                                    'rejected' => 'bg-red-100 text-red-600',
                                    default    => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $badge }}">
                                {{ __('expenses.status.' . $expense->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5 justify-end">
                                {{-- Approve/Reject for pending --}}
                                @if ($expense->status === 'pending' && auth()->user()->role === 'tenant_owner')
                                    <form method="POST" action="{{ route('tenant.expenses.approve', $expense) }}">
                                        @csrf
                                        <button type="submit" class="text-xs px-2 py-1 rounded border border-green-400 text-green-700">✓</button>
                                    </form>
                                    <button type="button"
                                            onclick="exOpenReject({{ $expense->id }})"
                                            class="text-xs px-2 py-1 rounded border border-red-300 text-red-600">✕</button>
                                @endif
                                <a href="{{ route('tenant.expenses.edit', $expense) }}"
                                   class="text-xs px-2 py-1 rounded border"
                                   style="border-color:var(--app-border);color:var(--app-text-muted)">{{ __('common.edit') }}</a>
                                @if (in_array(auth()->user()->role, ['tenant_owner','accountant']))
                                    <form method="POST" action="{{ route('tenant.expenses.destroy', $expense) }}"
                                          onsubmit="return confirm('{{ __('expenses.confirm_delete') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="text-xs px-2 py-1 rounded border border-red-200 text-red-500">{{ __('common.delete') }}</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="flex flex-col items-center justify-center py-16 px-4">
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
                                     style="background:var(--app-panel-strong)">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--app-text-muted)">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                                    </svg>
                                </div>
                                <p class="text-base font-semibold mb-1" style="color:var(--app-text)">{{ __('expenses.index.empty_title') }}</p>
                                <p class="text-sm mb-5 text-center max-w-xs" style="color:var(--app-text-muted)">{{ __('expenses.index.empty_desc') }}</p>
                                <a href="{{ route('tenant.expenses.create') }}"
                                   class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
                                   style="background:var(--app-brand)">
                                    + {{ __('expenses.nav.add') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $expenses->links() }}</div>

{{-- Reject modal --}}
<div id="exRejectModal" class="fixed inset-0 z-[300] hidden flex items-center justify-center" style="background:rgba(0,0,0,0.5)">
    <div class="rounded-xl p-6 w-full max-w-md mx-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <h3 class="font-semibold mb-3" style="color:var(--app-text)">{{ __('expenses.reject.title') }}</h3>
        <form id="exRejectForm" method="POST">
            @csrf
            <textarea name="rejection_reason" required rows="3" maxlength="500"
                      placeholder="{{ __('expenses.reject.reason_placeholder') }}"
                      class="w-full px-3 py-2 rounded-lg border text-sm mb-4"
                      style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)"></textarea>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="exCloseReject()"
                        class="px-4 py-2 rounded-lg text-sm border"
                        style="border-color:var(--app-border);color:var(--app-text-muted)">{{ __('common.cancel') }}</button>
                <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600">
                    {{ __('expenses.reject.confirm') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const EX_REJECT_BASE = '{{ rtrim(route('tenant.expenses.reject', ['expense' => '__ID__']), '') }}';
function exOpenReject(id) {
    document.getElementById('exRejectForm').action = EX_REJECT_BASE.replace('__ID__', id);
    document.getElementById('exRejectModal').classList.remove('hidden');
}
function exCloseReject() {
    document.getElementById('exRejectModal').classList.add('hidden');
}
</script>

</x-layouts.admin>
