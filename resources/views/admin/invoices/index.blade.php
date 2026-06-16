<x-layouts.admin
    title="Invoices & Payments"
    eyebrow="Module 3"
    heading="Invoices & Manual Payments"
    subheading="Record offline payments and review recent collection activity per tenant."
>
    <div class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
        <form method="POST" action="{{ route('admin.invoices.payments.store') }}" class="space-y-4 rounded-[2rem] border border-white/10 bg-white/5 p-6">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-200">Tenant</label>
                <select name="tenant_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none" required>
                    <option value="">Select tenant</option>
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}">{{ $tenant->gym_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">Amount (INR)</label>
                    <input type="number" step="0.01" name="amount_inr" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">Date paid</label>
                    <input type="date" name="paid_at" value="{{ now()->toDateString() }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none" required>
                </div>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-200">Payment method</label>
                <select name="payment_method" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none" required>
                    @foreach (['Cash', 'Bank transfer', 'UPI', 'Cheque'] as $method)
                        <option value="{{ $method }}">{{ $method }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-200">Transaction reference</label>
                <input name="transaction_ref" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white outline-none">
            </div>
            <button type="submit" class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm font-semibold text-slate-950 hover:bg-orange-400">
                Record payment
            </button>
        </form>

        <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
            <table class="w-full divide-y divide-white/10 text-left text-sm">
                <thead class="bg-slate-950/60 text-slate-300">
                    <tr>
                        <th class="px-4 py-3 font-medium">Gym</th>
                        <th class="px-4 py-3 font-medium">Amount</th>
                        <th class="px-4 py-3 font-medium">Method</th>
                        <th class="px-4 py-3 font-medium">Reference</th>
                        <th class="px-4 py-3 font-medium">Paid on</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="px-4 py-4">{{ $payment->tenant->gym_name }}</td>
                            <td class="px-4 py-4">Rs. {{ number_format($payment->amount_paise / 100, 2) }}</td>
                            <td class="px-4 py-4">{{ $payment->payment_method }}</td>
                            <td class="px-4 py-4">{{ $payment->transaction_ref ?: 'N/A' }}</td>
                            <td class="px-4 py-4">{{ $payment->paid_at?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">No payments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $payments->links() }}
    </div>
</x-layouts.admin>
