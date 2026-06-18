<x-layouts.admin :title="__('payments.receipt.title')">

<div class="max-w-lg mx-auto">

    {{-- Actions --}}
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('tenant.payments.history') }}"
           class="text-sm" style="color:var(--app-text-muted)">
            ← {{ __('payments.nav.history') }}
        </a>
        <button onclick="window.print()" class="text-sm px-3 py-1.5 rounded-lg border"
                style="border-color:var(--app-border);color:var(--app-text-muted)">
            {{ __('payments.receipt.print') }}
        </button>
    </div>

    {{-- Receipt card --}}
    <div id="printArea" class="rounded-xl p-8" style="background:var(--app-panel);border:1px solid var(--app-border)">

        {{-- Header --}}
        <div class="text-center mb-6">
            <h2 class="text-lg font-bold" style="color:var(--app-text)">{{ $tenant->name }}</h2>
            @if ($payment->branch)
                <p class="text-sm" style="color:var(--app-text-muted)">{{ $payment->branch->name }}</p>
            @endif
            <p class="mt-3 text-xs font-semibold uppercase tracking-wider" style="color:var(--app-text-muted)">{{ __('payments.receipt.title') }}</p>
            <p class="font-mono text-xl font-bold mt-1" style="color:var(--app-brand)">{{ $payment->receipt_number }}</p>
        </div>

        @if ($payment->status === 'voided')
            <div class="text-center mb-4 py-2 rounded-lg bg-red-100 text-red-700 text-sm font-semibold">
                {{ __('payments.status.voided') }} — {{ $payment->voided_at?->format('d M Y') }}
            </div>
        @endif

        <hr style="border-color:var(--app-border)" class="mb-6">

        {{-- Member --}}
        <div class="mb-5">
            <p class="text-xs font-medium uppercase tracking-wide mb-2" style="color:var(--app-text-muted)">{{ __('payments.receipt.member') }}</p>
            <p class="font-semibold" style="color:var(--app-text)">{{ $payment->member->name }}</p>
            <p class="text-sm" style="color:var(--app-text-muted)">{{ $payment->member->phone }}</p>
            @if ($payment->plan)
                <p class="text-sm mt-1" style="color:var(--app-text-muted)">{{ __('payments.receipt.plan') }}: {{ $payment->plan->name }}</p>
            @endif
        </div>

        {{-- Amounts --}}
        <div class="rounded-lg p-4 mb-5" style="background:var(--app-panel-strong)">
            <div class="flex justify-between text-sm mb-2">
                <span style="color:var(--app-text-muted)">{{ __('payments.receipt.amount') }}</span>
                <span style="color:var(--app-text)">₹{{ number_format($payment->amount_paise / 100, 2) }}</span>
            </div>
            @if ($payment->gst_paise > 0)
                <div class="flex justify-between text-sm mb-2">
                    <span style="color:var(--app-text-muted)">{{ __('payments.receipt.gst') }}</span>
                    <span style="color:var(--app-text)">₹{{ number_format($payment->gst_paise / 100, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between font-bold border-t pt-2" style="border-color:var(--app-border)">
                <span style="color:var(--app-text)">{{ __('payments.receipt.total') }}</span>
                <span style="color:var(--app-brand)">₹{{ number_format($payment->total_paise / 100, 2) }}</span>
            </div>
        </div>

        {{-- Meta --}}
        <div class="space-y-1.5 text-sm">
            <div class="flex justify-between">
                <span style="color:var(--app-text-muted)">{{ __('payments.receipt.method') }}</span>
                <span style="color:var(--app-text)">{{ __('payments.methods.' . $payment->method) }}</span>
            </div>
            @if ($payment->reference)
                <div class="flex justify-between">
                    <span style="color:var(--app-text-muted)">{{ __('payments.receipt.reference') }}</span>
                    <span class="font-mono text-xs" style="color:var(--app-text)">{{ $payment->reference }}</span>
                </div>
            @endif
            <div class="flex justify-between">
                <span style="color:var(--app-text-muted)">{{ __('payments.receipt.date') }}</span>
                <span style="color:var(--app-text)">{{ $payment->payment_date->format('d M Y') }}</span>
            </div>
            @if ($payment->collectedBy)
                <div class="flex justify-between">
                    <span style="color:var(--app-text-muted)">{{ __('payments.receipt.collected_by') }}</span>
                    <span style="color:var(--app-text)">{{ $payment->collectedBy->name }}</span>
                </div>
            @endif
            @if ($payment->notes)
                <div class="flex justify-between">
                    <span style="color:var(--app-text-muted)">{{ __('payments.receipt.notes') }}</span>
                    <span style="color:var(--app-text)">{{ $payment->notes }}</span>
                </div>
            @endif
        </div>

        <hr style="border-color:var(--app-border)" class="my-6">
        <p class="text-center text-xs" style="color:var(--app-text-muted)">{{ __('payments.receipt.footer') }}</p>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    #printArea, #printArea * { visibility: visible; }
    #printArea { position: absolute; left: 0; top: 0; width: 100%; border: none !important; }
}
</style>

</x-layouts.admin>
