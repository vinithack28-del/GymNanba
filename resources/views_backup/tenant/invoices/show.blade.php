<x-layouts.admin :title="__('invoices.show.title')">

<div class="max-w-2xl mx-auto">

    {{-- Actions --}}
    <div class="flex items-center justify-between mb-4 no-print">
        <a href="{{ route('tenant.invoices.index') }}" class="text-sm" style="color:var(--app-text-muted)">
            ← {{ __('invoices.nav.invoices') }}
        </a>
        <div class="flex gap-2">
            @if ($invoice->status !== 'void' && in_array(auth()->user()->role, ['tenant_owner','accountant']))
                <button type="button" onclick="ivShowVoid()"
                        class="text-sm px-3 py-1.5 rounded-lg border border-red-300 text-red-600">
                    {{ __('invoices.table.void_btn') }}
                </button>
            @endif
            <button onclick="window.print()"
                    class="text-sm px-3 py-1.5 rounded-lg border"
                    style="border-color:var(--app-border);color:var(--app-text-muted)">
                {{ __('invoices.show.print') }}
            </button>
        </div>
    </div>

    {{-- Invoice card --}}
    <div id="printArea" class="rounded-xl p-8" style="background:var(--app-panel);border:1px solid var(--app-border)">

        {{-- Gym header --}}
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-lg font-bold" style="color:var(--app-text)">{{ $tenant->gym_name }}</h2>
                <p class="text-xs mt-0.5" style="color:var(--app-text-muted)">{{ $tenant->address }}</p>
                <p class="text-xs" style="color:var(--app-text-muted)">{{ $tenant->city }}, {{ $tenant->state }}</p>
                @if ($tenant->gst_number)
                    <p class="text-xs mt-1 font-mono" style="color:var(--app-text-muted)">GSTIN: {{ $tenant->gst_number }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold uppercase tracking-wider" style="color:var(--app-text-muted)">{{ __('invoices.show.title') }}</p>
                <p class="font-mono text-lg font-bold mt-0.5" style="color:var(--app-brand)">{{ $invoice->invoice_number }}</p>
                <p class="text-xs mt-1" style="color:var(--app-text-muted)">{{ __('invoices.show.date') }}: {{ $invoice->invoice_date->format('d M Y') }}</p>
                @if ($invoice->due_date)
                    <p class="text-xs" style="color:var(--app-text-muted)">{{ __('invoices.show.due_date') }}: {{ $invoice->due_date->format('d M Y') }}</p>
                @endif
            </div>
        </div>

        @if ($invoice->status === 'void')
            <div class="text-center mb-4 py-2 rounded-lg bg-red-100 text-red-700 text-sm font-semibold">
                {{ __('invoices.status.void') }} — {{ $invoice->voided_at?->format('d M Y') }}
            </div>
        @endif

        <hr style="border-color:var(--app-border)" class="mb-5">

        {{-- Bill to --}}
        <div class="mb-5">
            <p class="text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--app-text-muted)">{{ __('invoices.show.bill_to') }}</p>
            <p class="font-semibold" style="color:var(--app-text)">{{ $invoice->member->name }}</p>
            <p class="text-sm" style="color:var(--app-text-muted)">{{ $invoice->member->phone }}</p>
            <p class="text-xs" style="color:var(--app-text-muted)">{{ $invoice->member->member_code }}</p>
        </div>

        {{-- Line items table --}}
        <div class="rounded-lg overflow-hidden mb-5" style="border:1px solid var(--app-border)">
            <table class="w-full text-sm">
                <thead style="background:var(--app-panel-strong)">
                    <tr>
                        <th class="text-left px-4 py-2 text-xs font-medium" style="color:var(--app-text-muted)">#</th>
                        <th class="text-left px-4 py-2 text-xs font-medium" style="color:var(--app-text-muted)">{{ __('invoices.show.col_desc') }}</th>
                        <th class="text-center px-3 py-2 text-xs font-medium" style="color:var(--app-text-muted)">{{ __('invoices.show.col_qty') }}</th>
                        <th class="text-right px-3 py-2 text-xs font-medium" style="color:var(--app-text-muted)">{{ __('invoices.show.col_rate') }}</th>
                        <th class="text-center px-3 py-2 text-xs font-medium" style="color:var(--app-text-muted)">GST</th>
                        <th class="text-right px-4 py-2 text-xs font-medium" style="color:var(--app-text-muted)">{{ __('invoices.show.col_amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->line_items as $i => $item)
                        @php
                            $rate   = $item['rate_paise'] ?? 0;
                            $qty    = $item['qty'] ?? 1;
                            $amount = $rate * $qty;
                            $gstR   = $item['gst_rate'] ?? 0;
                        @endphp
                        <tr class="border-t" style="border-color:var(--app-border)">
                            <td class="px-4 py-2.5 text-xs" style="color:var(--app-text-muted)">{{ $i + 1 }}</td>
                            <td class="px-4 py-2.5" style="color:var(--app-text)">{{ $item['description'] }}</td>
                            <td class="px-3 py-2.5 text-center text-xs" style="color:var(--app-text)">{{ $qty }}</td>
                            <td class="px-3 py-2.5 text-right text-xs" style="color:var(--app-text)">₹{{ number_format($rate / 100, 2) }}</td>
                            <td class="px-3 py-2.5 text-center text-xs" style="color:var(--app-text-muted)">{{ $gstR }}%</td>
                            <td class="px-4 py-2.5 text-right text-xs font-medium" style="color:var(--app-text)">₹{{ number_format($amount / 100, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="flex justify-end mb-5">
            <div class="w-64 space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span style="color:var(--app-text-muted)">{{ __('invoices.show.subtotal') }}</span>
                    <span style="color:var(--app-text)">₹{{ number_format($invoice->subtotal_paise / 100, 2) }}</span>
                </div>
                @php
                    $halfGst = $invoice->gst_paise / 2;
                    $gstRate = $invoice->subtotal_paise > 0
                        ? round(($invoice->gst_paise / $invoice->subtotal_paise) * 100)
                        : 0;
                    $halfRate = $gstRate / 2;
                @endphp
                @if ($invoice->gst_paise > 0)
                    <div class="flex justify-between">
                        <span style="color:var(--app-text-muted)">CGST ({{ $halfRate }}%)</span>
                        <span style="color:var(--app-text)">₹{{ number_format($halfGst / 100, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color:var(--app-text-muted)">SGST ({{ $halfRate }}%)</span>
                        <span style="color:var(--app-text)">₹{{ number_format($halfGst / 100, 2) }}</span>
                    </div>
                @endif
                <div class="border-t pt-2 flex justify-between font-bold text-base" style="border-color:var(--app-border)">
                    <span style="color:var(--app-text)">{{ __('invoices.show.total') }}</span>
                    <span style="color:var(--app-brand)">₹{{ number_format($invoice->total_paise / 100, 2) }}</span>
                </div>
            </div>
        </div>

        <hr style="border-color:var(--app-border)" class="mb-4">

        {{-- Footer info --}}
        <div class="text-xs space-y-1" style="color:var(--app-text-muted)">
            @php
                $svc = new \App\Services\Tenant\InvoiceService();
                $words = $svc->amountInWords($invoice->total_paise);
            @endphp
            <p><span class="font-medium">{{ __('invoices.show.amount_words') }}:</span> {{ $words }}</p>
            <p><span class="font-medium">SAC:</span> 998311 — Fitness centre services</p>
            @if ($invoice->payment)
                <p><span class="font-medium">{{ __('invoices.show.payment_method') }}:</span> {{ __('payments.methods.' . $invoice->payment->method) }}</p>
            @endif
            <p><span class="font-medium">{{ __('invoices.show.place_of_supply') }}:</span> {{ $tenant->state }}</p>
            @if ($invoice->notes)
                <p class="mt-2 italic">{{ $invoice->notes }}</p>
            @endif
        </div>

        <hr style="border-color:var(--app-border)" class="my-4">
        <p class="text-center text-xs" style="color:var(--app-text-muted)">{{ __('invoices.show.footer') }}</p>
    </div>
</div>

{{-- Void modal --}}
<div id="ivVoidModal" class="fixed inset-0 z-[300] hidden flex items-center justify-center no-print" style="background:rgba(0,0,0,0.5)">
    <div class="rounded-xl p-6 w-full max-w-md mx-4" style="background:var(--app-panel);border:1px solid var(--app-border)">
        <h3 class="font-semibold text-base mb-1" style="color:var(--app-text)">{{ __('invoices.void.title') }}</h3>
        <p class="text-sm mb-4" style="color:var(--app-text-muted)">{{ __('invoices.void.desc_prefix') }} {{ $invoice->invoice_number }}</p>
        <form method="POST" action="{{ route('tenant.invoices.void', $invoice) }}">
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

<style>
@media print {
    .no-print { display: none !important; }
    body * { visibility: hidden; }
    #printArea, #printArea * { visibility: visible; }
    #printArea { position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; }
}
</style>

<script>
function ivShowVoid() { document.getElementById('ivVoidModal').classList.remove('hidden'); }
function ivCloseVoid() { document.getElementById('ivVoidModal').classList.add('hidden'); }
</script>

</x-layouts.admin>
