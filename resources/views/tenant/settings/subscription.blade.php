<x-layouts.admin :title="__('settings.nav.subscription')">

<div class="mb-2">
    <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('settings.title') }}</h1>
    <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('settings.subtitle') }}</p>
</div>

@include('tenant.settings._nav')

<div class="space-y-6">

        {{-- Current plan --}}
        <div class="rounded-2xl p-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <h2 class="text-sm font-semibold mb-4" style="color:var(--app-text)">{{ __('settings.subscription.section.current') }}</h2>

            @if ($subscription)
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
                    <div>
                        <p class="text-xs mb-0.5" style="color:var(--app-text-muted)">{{ __('settings.subscription.field.plan') }}</p>
                        <p class="text-base font-bold" style="color:var(--app-text)">{{ $subscription->plan_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs mb-0.5" style="color:var(--app-text-muted)">{{ __('settings.subscription.field.renewal') }}</p>
                        <p class="text-base font-semibold" style="color:var(--app-text)">
                            {{ $subscription->ends_at ? \Carbon\Carbon::parse($subscription->ends_at)->format('d M Y') : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs mb-0.5" style="color:var(--app-text-muted)">{{ __('settings.subscription.field.amount') }}</p>
                        <p class="text-base font-semibold" style="color:var(--app-text)">
                            {{ $subscription->amount ? '₹' . number_format($subscription->amount / 100, 2) : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs mb-0.5" style="color:var(--app-text-muted)">{{ __('settings.subscription.field.branches') }}</p>
                        <p class="text-sm" style="color:var(--app-text)">{{ $tenant->branches()->count() }} {{ __('settings.subscription.field.branches_used') }}</p>
                    </div>
                    <div>
                        <p class="text-xs mb-0.5" style="color:var(--app-text-muted)">{{ __('settings.subscription.field.members') }}</p>
                        <p class="text-sm" style="color:var(--app-text)">{{ number_format($tenant->members_count) }} {{ __('settings.subscription.field.members_active') }}</p>
                    </div>
                </div>
            @else
                <p class="text-sm" style="color:var(--app-text-muted)">{{ __('settings.subscription.no_subscription') }}</p>
            @endif

            <div class="rounded-xl p-4" style="background:var(--app-panel-strong);border:1px solid var(--app-border)">
                <p class="text-xs" style="color:var(--app-text-muted)">
                    {{ __('settings.subscription.contact_note') }}
                    <a href="mailto:support@gymos.in" class="underline" style="color:var(--app-brand)">support@gymos.in</a>
                </p>
            </div>
        </div>

        {{-- Invoice history --}}
        @if ($subscription && $tenant->payments()->exists())
            <div class="rounded-2xl" style="background:var(--app-panel);border:1px solid var(--app-border)">
                <div class="px-5 py-3.5 border-b" style="border-color:var(--app-border)">
                    <h2 class="text-sm font-semibold" style="color:var(--app-text)">{{ __('settings.subscription.section.invoices') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="color:var(--app-text-muted)">
                                <th class="text-left px-5 py-2 font-medium">{{ __('settings.subscription.col.invoice') }}</th>
                                <th class="text-left px-5 py-2 font-medium">{{ __('settings.subscription.col.date') }}</th>
                                <th class="text-right px-5 py-2 font-medium">{{ __('settings.subscription.col.amount') }}</th>
                                <th class="text-left px-5 py-2 font-medium">{{ __('settings.subscription.col.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tenant->payments()->latest()->limit(12)->get() as $payment)
                                <tr class="border-t" style="border-color:var(--app-border);color:var(--app-text)">
                                    <td class="px-5 py-2.5 font-mono text-xs">{{ $payment->invoice_number ?? '—' }}</td>
                                    <td class="px-5 py-2.5">{{ $payment->created_at->format('d M Y') }}</td>
                                    <td class="px-5 py-2.5 text-right">₹{{ number_format($payment->amount / 100, 2) }}</td>
                                    <td class="px-5 py-2.5">
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium text-emerald-400 bg-emerald-500/10">
                                            {{ $payment->status ?? 'paid' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>

</x-layouts.admin>
