<x-layouts.admin :title="__('invoices.nav.invoices')">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('invoices.nav.invoices') }}</h1>
        <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('invoices.index.subtitle') }}</p>
    </div>
    @if (in_array(auth()->user()->role, ['tenant_owner','accountant','branch_manager','branch_admin']))
        <a href="{{ route('tenant.invoices.create') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
           style="background:var(--app-brand)">
            + {{ __('invoices.nav.create') }}
        </a>
    @endif
</div>

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-4 items-end">
    <div>
        <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('common.search') }}</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="{{ __('invoices.index.search_placeholder') }}"
               class="px-3 py-1.5 rounded-lg border text-sm w-52"
               style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
    </div>
    <div>
        <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('invoices.index.status') }}</label>
        <select name="status"
                class="px-3 py-1.5 rounded-lg border text-sm"
                style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
            <option value="">{{ __('common.all') }}</option>
            @foreach (\App\Models\Invoice::STATUSES as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ __('invoices.status.' . $s) }}</option>
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
        <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('invoices.index.date_from') }}</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="px-3 py-1.5 rounded-lg border text-sm"
               style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
    </div>
    <div>
        <label class="block text-xs mb-1" style="color:var(--app-text-muted)">{{ __('invoices.index.date_to') }}</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="px-3 py-1.5 rounded-lg border text-sm"
               style="background:var(--app-panel);border-color:var(--app-border);color:var(--app-text)">
    </div>
    <button type="submit"
            class="px-4 py-1.5 rounded-lg text-sm font-medium text-white"
            style="background:var(--app-brand)">{{ __('common.filter') }}</button>
    @if (request()->hasAny(['search','status','branch_id','date_from','date_to','amount_min','amount_max']))
        <a href="{{ route('tenant.invoices.index') }}"
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
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('invoices.table.number') }}</th>
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('invoices.table.date') }}</th>
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('invoices.table.member') }}</th>
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('invoices.table.description') }}</th>
                    <th class="text-right px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('invoices.table.subtotal') }}</th>
                    <th class="text-right px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('invoices.table.gst') }}</th>
                    <th class="text-right px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('invoices.table.total') }}</th>
                    <th class="text-left px-4 py-2.5 font-medium" style="color:var(--app-text-muted)">{{ __('invoices.table.status') }}</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    @php
                        $desc = collect($invoice->line_items)->pluck('description')->implode(', ');
                    @endphp
                    <tr class="border-t hover:opacity-90 transition-opacity"
                        style="border-color:var(--app-border);background:{{ $invoice->status === 'void' ? 'var(--app-panel-strong)' : 'var(--app-panel)' }}">
                        <td class="px-4 py-3 font-mono text-xs" style="color:var(--app-brand)">
                            <a href="{{ route('tenant.invoices.show', $invoice) }}" class="hover:underline">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-xs" style="color:var(--app-text-muted)">
                            {{ $invoice->invoice_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3" style="color:var(--app-text)">
                            <div class="font-medium">{{ $invoice->member->name }}</div>
                            <div class="text-xs" style="color:var(--app-text-muted)">{{ $invoice->member->phone }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs max-w-[160px] truncate" style="color:var(--app-text-muted)" title="{{ $desc }}">
                            {{ $desc }}
                        </td>
                        <td class="px-4 py-3 text-right" style="color:var(--app-text)">
                            ₹{{ number_format($invoice->subtotal_paise / 100, 0) }}
                        </td>
                        <td class="px-4 py-3 text-right" style="color:var(--app-text-muted)">
                            ₹{{ number_format($invoice->gst_paise / 100, 0) }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold" style="color:var(--app-text)">
                            ₹{{ number_format($invoice->total_paise / 100, 0) }}
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $badge = match($invoice->status) {
                                    'paid'    => 'bg-green-100 text-green-700',
                                    'unpaid'  => 'bg-amber-100 text-amber-700',
                                    'partial' => 'bg-blue-100 text-blue-700',
                                    'void'    => 'bg-gray-100 text-gray-500 line-through',
                                    default   => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $badge }}">
                                {{ __('invoices.status.' . $invoice->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="{{ route('tenant.invoices.show', $invoice) }}"
                                   class="text-xs px-2 py-1 rounded border"
                                   style="border-color:var(--app-border);color:var(--app-text-muted)">
                                    {{ __('invoices.table.view_btn') }}
                                </a>
                                @if ($invoice->status !== 'void' && in_array(auth()->user()->role, ['tenant_owner','accountant']))
                                    <button type="button"
                                            onclick="ivOpenVoid({{ $invoice->id }}, '{{ $invoice->invoice_number }}')"
                                            class="text-xs px-2 py-1 rounded border border-red-300 text-red-600">
                                        {{ __('invoices.table.void_btn') }}
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="flex flex-col items-center justify-center py-16 px-4">
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
                                     style="background:var(--app-panel-strong)">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--app-text-muted)">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                    </svg>
                                </div>
                                <p class="text-base font-semibold mb-1" style="color:var(--app-text)">{{ __('invoices.index.empty_title') }}</p>
                                <p class="text-sm mb-5 text-center max-w-xs" style="color:var(--app-text-muted)">{{ __('invoices.index.empty_desc') }}</p>
                                @if (in_array(auth()->user()->role, ['tenant_owner','accountant','branch_manager','branch_admin']))
                                    <a href="{{ route('tenant.invoices.create') }}"
                                       class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
                                       style="background:var(--app-brand)">
                                        + {{ __('invoices.nav.create') }}
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $invoices->links() }}</div>

{{-- Void modal --}}
<div id="ivVoidModal" class="fixed inset-0 z-[300] hidden flex items-center justify-center" style="background:rgba(0,0,0,0.5)">
    <div class="rounded-xl p-6 w-full max-w-md mx-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <h3 class="font-semibold text-base mb-1" style="color:var(--app-text)">{{ __('invoices.void.title') }}</h3>
        <p id="ivVoidDesc" class="text-sm mb-4" style="color:var(--app-text-muted)"></p>
        <form id="ivVoidForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('invoices.void.reason_label') }}</label>
                <select name="void_reason" required
                        class="w-full px-3 py-2 rounded-lg border text-sm"
                        style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    <option value="">— {{ __('invoices.void.select_reason') }} —</option>
                    @foreach (\App\Models\Invoice::VOID_REASONS as $r)
                        <option value="{{ $r }}">{{ __('invoices.void_reasons.' . $r) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="ivCloseVoid()"
                        class="px-4 py-2 rounded-lg text-sm border"
                        style="border-color:var(--app-border);color:var(--app-text-muted)">
                    {{ __('common.cancel') }}
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600">
                    {{ __('invoices.void.confirm') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const IV_VOID_BASE = '{{ rtrim(route('tenant.invoices.void', ['invoice' => '__ID__']), '') }}';
function ivOpenVoid(id, number) {
    document.getElementById('ivVoidDesc').textContent = '{{ __('invoices.void.desc_prefix') }} ' + number;
    document.getElementById('ivVoidForm').action = IV_VOID_BASE.replace('__ID__', id);
    document.getElementById('ivVoidModal').classList.remove('hidden');
}
function ivCloseVoid() {
    document.getElementById('ivVoidModal').classList.add('hidden');
}
</script>

</x-layouts.admin>
