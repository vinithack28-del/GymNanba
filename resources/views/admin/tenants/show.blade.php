<x-layouts.admin
    title="Tenant Details"
    eyebrow="Tenant"
    heading="{{ $tenant->gym_name }}"
    subheading="Detailed view of the tenant profile, active subscription, and recorded payments."
>
    <x-slot:headerAction>
        <div class="flex gap-3">
            <a href="{{ route('admin.tenants.edit', $tenant) }}" class="rounded-full bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-400">Edit</a>
            <a href="{{ route('admin.tenants.delete', $tenant) }}" class="rounded-full bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-400">Delete</a>
        </div>
    </x-slot:headerAction>

    <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <section class="app-panel rounded-[2rem] border p-6">
            <h3 class="text-xl font-semibold">Overview</h3>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="app-panel-strong rounded-2xl border p-4"><p class="app-muted text-xs uppercase tracking-[0.24em]">Owner</p><p class="mt-2 font-semibold">{{ $tenant->owner_name }}</p><p class="app-muted mt-1 text-sm">{{ $tenant->owner_email }}</p></div>
                <div class="app-panel-strong rounded-2xl border p-4"><p class="app-muted text-xs uppercase tracking-[0.24em]">Primary domain</p><p class="mt-2 font-semibold">{{ $tenant->primary_domain }}</p></div>
                <div class="app-panel-strong rounded-2xl border p-4"><p class="app-muted text-xs uppercase tracking-[0.24em]">Status</p><p class="mt-2 font-semibold uppercase">{{ $tenant->status }}</p></div>
                <div class="app-panel-strong rounded-2xl border p-4"><p class="app-muted text-xs uppercase tracking-[0.24em]">Language</p><p class="mt-2 font-semibold">{{ $tenant->default_language }}</p></div>
                <div class="app-panel-strong rounded-2xl border p-4"><p class="app-muted text-xs uppercase tracking-[0.24em]">Business type</p><p class="mt-2 font-semibold">{{ $tenant->business_type }}</p></div>
                <div class="app-panel-strong rounded-2xl border p-4"><p class="app-muted text-xs uppercase tracking-[0.24em]">Members</p><p class="mt-2 font-semibold">{{ $tenant->members_count }}</p></div>
                <div class="app-panel-strong rounded-2xl border p-4"><p class="app-muted text-xs uppercase tracking-[0.24em]">Domain mode</p><p class="mt-2 font-semibold uppercase">{{ $tenant->domain_mode }}</p></div>
                <div class="app-panel-strong rounded-2xl border p-4"><p class="app-muted text-xs uppercase tracking-[0.24em]">Database mode</p><p class="mt-2 font-semibold uppercase">{{ $tenant->database_mode }}</p></div>
                <div class="app-panel-strong rounded-2xl border p-4"><p class="app-muted text-xs uppercase tracking-[0.24em]">Login email</p><p class="mt-2 font-semibold">{{ $tenant->ownerUser?->email ?? $tenant->owner_email }}</p></div>
                <div class="app-panel-strong rounded-2xl border p-4"><p class="app-muted text-xs uppercase tracking-[0.24em]">Database name</p><p class="mt-2 font-semibold">{{ $tenant->database_name ?: 'Main database' }}</p></div>
                <div class="app-panel-strong rounded-2xl border p-4 md:col-span-2"><p class="app-muted text-xs uppercase tracking-[0.24em]">Address</p><p class="mt-2 font-semibold">{{ $tenant->address }}</p><p class="app-muted mt-1 text-sm">{{ $tenant->city }}, {{ $tenant->state }}</p></div>
                <div class="app-panel-strong rounded-2xl border p-4 md:col-span-2"><p class="app-muted text-xs uppercase tracking-[0.24em]">Notes</p><p class="mt-2 font-semibold">{{ $tenant->notes ?: 'No notes added' }}</p></div>
            </div>
        </section>

        <div class="space-y-6">
            <section class="app-panel rounded-[2rem] border p-6">
                <h3 class="text-xl font-semibold">Subscriptions</h3>
                <div class="mt-5 space-y-4">
                    @forelse ($tenant->subscriptions as $subscription)
                        <div class="app-panel-strong rounded-2xl border p-4">
                            <p class="font-semibold">{{ $subscription->plan->name }}</p>
                            <p class="app-muted mt-1 text-sm">{{ $subscription->status }} · Rs. {{ number_format($subscription->price_paise / 100, 2) }}</p>
                            <p class="app-muted mt-1 text-sm">Start: {{ $subscription->start_date?->format('d M Y') }}</p>
                        </div>
                    @empty
                        <p class="app-muted text-sm">No subscriptions found.</p>
                    @endforelse
                </div>
            </section>

            <section class="app-panel rounded-[2rem] border p-6">
                <h3 class="text-xl font-semibold">Payments</h3>
                <div class="mt-5 space-y-4">
                    @forelse ($tenant->payments as $payment)
                        <div class="app-panel-strong rounded-2xl border p-4">
                            <p class="font-semibold">Rs. {{ number_format($payment->amount_paise / 100, 2) }}</p>
                            <p class="app-muted mt-1 text-sm">{{ $payment->payment_method }} · {{ $payment->paid_at?->format('d M Y') }}</p>
                            <p class="app-muted mt-1 text-sm">{{ $payment->transaction_ref ?: 'No reference' }}</p>
                        </div>
                    @empty
                        <p class="app-muted text-sm">No payments recorded.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-layouts.admin>
