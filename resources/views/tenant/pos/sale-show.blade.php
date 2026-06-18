<x-layouts.admin
    title="{{ $sale->bill_number }}"
    eyebrow="Gym Workspace"
    heading="Receipt {{ $sale->bill_number }}"
    subheading="Review line items, payment method, tax, and linked member details for this bill."
>
    <div class="mx-auto max-w-5xl space-y-5">
        <section class="app-panel rounded-[2rem] border p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">{{ __('pos.sales.bill_number') }}</p>
                    <h2 class="mt-1 text-2xl font-semibold">{{ $sale->bill_number }}</h2>
                    <p class="app-muted mt-2 text-sm">{{ $sale->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="window.print()" class="sr-btn-primary">{{ __('pos.actions.print_receipt') }}</button>
                    <a href="{{ route('tenant.pos.sales') }}" class="sr-btn-ghost">{{ __('pos.actions.back_to_sales') }}</a>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-panel-strong)] p-4">
                    <p class="app-muted text-xs uppercase tracking-[0.18em]">{{ __('pos.sales.member') }}</p>
                    <p class="mt-2 font-semibold">{{ $sale->member?->name ?? 'Walk-in' }}</p>
                    <p class="app-muted mt-1 text-sm">{{ $sale->member?->member_code ?? 'No member linked' }}</p>
                </div>
                <div class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-panel-strong)] p-4">
                    <p class="app-muted text-xs uppercase tracking-[0.18em]">{{ __('pos.sales.staff') }}</p>
                    <p class="mt-2 font-semibold">{{ $sale->seller?->name ?? 'Owner / system' }}</p>
                    <p class="app-muted mt-1 text-sm">{{ $sale->branch?->name ?? '—' }}</p>
                </div>
                <div class="rounded-2xl border border-[var(--app-border)] bg-[var(--app-panel-strong)] p-4">
                    <p class="app-muted text-xs uppercase tracking-[0.18em]">{{ __('pos.sales.method') }}</p>
                    <p class="mt-2 font-semibold">{{ $sale->method_label }}</p>
                    <p class="app-muted mt-1 text-sm">{{ $sale->reference ?: 'No reference' }}</p>
                </div>
            </div>
        </section>

        <section class="app-panel overflow-hidden rounded-[2rem] border">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="app-table-head">
                        <tr>
                            @foreach ([__('pos.stock.product'), 'Qty', 'Unit', __('pos.sales.subtotal'), __('pos.sales.gst'), __('pos.sales.total')] as $head)
                                <th class="px-5 py-3 text-left font-medium">{{ $head }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->items as $item)
                            <tr class="border-t border-[var(--app-border)]">
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $item->product_name }}</p>
                                    <p class="app-muted text-xs">{{ rtrim(rtrim((string) $item->gst_rate, '0'), '.') }}% GST</p>
                                </td>
                                <td class="px-5 py-4">{{ $item->qty }}</td>
                                <td class="px-5 py-4">₹{{ number_format($item->unit_price_paise / 100, 2) }}</td>
                                <td class="px-5 py-4">₹{{ number_format($item->line_subtotal_paise / 100, 2) }}</td>
                                <td class="px-5 py-4">₹{{ number_format($item->gst_paise / 100, 2) }}</td>
                                <td class="px-5 py-4 font-semibold">₹{{ number_format($item->line_total_paise / 100, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-[1fr_0.8fr]">
            <section class="app-panel rounded-[2rem] border p-6">
                <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">Notes</p>
                <p class="mt-3 text-sm">{{ $sale->notes ?: 'No notes recorded for this bill.' }}</p>

                @if ($sale->refunded_at)
                    <div class="mt-5 rounded-2xl border border-red-400/20 bg-red-500/10 p-4">
                        <p class="text-sm font-semibold text-red-300">{{ __('pos.statuses.refunded') }}</p>
                        <p class="mt-2 text-sm text-red-200">Refunded on {{ $sale->refunded_at->format('d M Y, h:i A') }} by {{ $sale->refundActor?->name ?? 'Unknown' }}.</p>
                        <p class="mt-1 text-sm text-red-200">{{ $sale->refund_reason }}</p>
                    </div>
                @elseif ($canRefund)
                    <form method="POST" action="{{ route('tenant.pos.sales.refund', $sale) }}" class="mt-5 rounded-2xl border border-red-400/20 bg-red-500/10 p-4">
                        @csrf
                        <label class="sr-label">{{ __('pos.sales.refund_reason') }}</label>
                        <input type="text" name="refund_reason" class="sr-input mt-2" placeholder="Reason for refund" required>
                        <button type="submit" class="sr-btn-danger mt-3">{{ __('pos.actions.refund') }}</button>
                    </form>
                @endif
            </section>

            <section class="app-panel rounded-[2rem] border p-6">
                <p class="app-muted text-xs font-medium uppercase tracking-[0.22em]">Summary</p>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between text-sm"><span class="app-muted">{{ __('pos.sales.subtotal') }}</span><span>₹{{ $sale->subtotal_rupees }}</span></div>
                    <div class="flex items-center justify-between text-sm"><span class="app-muted">{{ __('pos.sales.gst') }}</span><span>₹{{ $sale->gst_rupees }}</span></div>
                    <div class="flex items-center justify-between text-sm"><span class="app-muted">{{ __('pos.sales.discount') }}</span><span>₹{{ $sale->discount_rupees }}</span></div>
                    <div class="flex items-center justify-between border-t border-[var(--app-border)] pt-3 text-base font-semibold"><span>{{ __('pos.sales.total') }}</span><span>₹{{ $sale->total_rupees }}</span></div>
                </div>
            </section>
        </div>
    </div>

    @push('styles')
        <style>
            .sr-btn-primary, .sr-btn-ghost, .sr-btn-danger { align-items: center; border-radius: 0.9rem; display: inline-flex; font-size: 0.84rem; font-weight: 600; min-height: 2.9rem; padding: 0 1rem; text-decoration: none; }
            .sr-btn-primary { background: var(--app-brand); color: #0f172a; }
            .sr-btn-ghost { border: 1px solid var(--app-border); color: var(--app-text-muted); }
            .sr-btn-danger { background: rgba(226, 75, 74, 0.15); color: #fca5a5; }
            .sr-label { color: var(--app-text); display: block; font-size: 0.78rem; font-weight: 600; }
            .sr-input { background: var(--app-panel-strong); border: 1px solid var(--app-border); border-radius: 0.9rem; color: var(--app-text); font-size: 0.9rem; min-height: 2.9rem; outline: none; padding: 0 0.9rem; width: 100%; }
        </style>
    @endpush
</x-layouts.admin>
