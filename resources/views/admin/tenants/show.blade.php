<x-layouts.admin
    title="Tenant Details"
    eyebrow="Tenant"
    heading="{{ $tenant->gym_name }}"
    subheading="Profile overview, active subscriptions, and recorded payments."
>
    <x-slot:headerAction>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.tenants.index') }}"
                class="app-panel-strong inline-flex items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-medium transition hover:opacity-80"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Back
            </a>
            <a
                href="{{ route('admin.tenants.edit', $tenant) }}"
                class="inline-flex items-center gap-2 rounded-full bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg>
                Edit
            </a>
            <a
                href="{{ route('admin.tenants.delete', $tenant) }}"
                class="inline-flex items-center gap-2 rounded-full bg-red-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                Delete
            </a>
        </div>
    </x-slot:headerAction>

    @php
        $latestSub = $tenant->subscriptions->sortByDesc('id')->first();
        $statusColors = [
            'active'    => ['bg' => 'bg-emerald-500/15', 'text' => 'text-emerald-400', 'ring' => 'ring-emerald-500/30', 'dot' => 'bg-emerald-400'],
            'trial'     => ['bg' => 'bg-sky-500/15',     'text' => 'text-sky-400',     'ring' => 'ring-sky-500/30',     'dot' => 'bg-sky-400'],
            'suspended' => ['bg' => 'bg-red-500/15',     'text' => 'text-red-400',     'ring' => 'ring-red-500/30',     'dot' => 'bg-red-400'],
            'archived'  => ['bg' => 'bg-slate-500/15',   'text' => 'text-slate-400',   'ring' => 'ring-slate-500/30',   'dot' => 'bg-slate-400'],
        ];
        $sc = $statusColors[$tenant->status] ?? ['bg' => 'bg-white/10', 'text' => 'text-white', 'ring' => 'ring-white/20', 'dot' => 'bg-white'];
        $totalPaid = $tenant->payments->sum('amount_paise') / 100;
    @endphp

    {{-- Hero banner --}}
    <div class="app-panel mb-6 overflow-hidden rounded-[2rem] border">
        <div class="relative p-6 sm:p-8">

            {{-- Top row: identity + status --}}
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="app-brand-soft app-brand-text flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl text-2xl font-bold">
                        {{ strtoupper(substr($tenant->gym_name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">{{ $tenant->gym_name }}</h2>
                        <p class="app-muted mt-1 text-sm">{{ $tenant->business_type }} &middot; {{ $tenant->city }}, {{ $tenant->state }}</p>
                        <p class="mt-1 font-mono text-xs text-[var(--app-text-muted)]">{{ $tenant->primary_domain }}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold ring-1 {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['ring'] }}">
                    <span class="h-2 w-2 rounded-full {{ $sc['dot'] }}"></span>
                    {{ ucfirst($tenant->status) }}
                </span>
            </div>

            {{-- Quick stats strip --}}
            <div class="mt-6 grid grid-cols-2 gap-3 border-t border-[var(--app-border)] pt-6 sm:grid-cols-4">
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.22em]">Members</p>
                    <p class="mt-1 text-2xl font-bold">{{ number_format($tenant->members_count) }}</p>
                </div>
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.22em]">Plan</p>
                    <p class="mt-1 text-sm font-semibold">{{ $latestSub?->plan?->name ?? '—' }}</p>
                    <p class="app-muted mt-0.5 text-xs">{{ $latestSub ? 'Rs. '.number_format($latestSub->price_paise / 100, 2).' / '.$latestSub->plan->billing_cycle : '' }}</p>
                </div>
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.22em]">Total Paid</p>
                    <p class="mt-1 text-sm font-semibold">Rs. {{ number_format($totalPaid, 2) }}</p>
                    <p class="app-muted mt-0.5 text-xs">{{ $tenant->payments->count() }} payment{{ $tenant->payments->count() === 1 ? '' : 's' }}</p>
                </div>
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.22em]">Joined</p>
                    <p class="mt-1 text-sm font-semibold">{{ $tenant->created_at->format('d M Y') }}</p>
                    <p class="app-muted mt-0.5 text-xs">{{ $tenant->created_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Body: two columns --}}
    <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">

        {{-- Left: Details --}}
        <div class="space-y-6">

            {{-- Contact & Identity --}}
            <section class="app-panel rounded-[2rem] border p-6">
                <h3 class="mb-5 text-sm font-semibold uppercase tracking-[0.2em] text-[var(--app-text-muted)]">Owner & Contact</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0">
                    @php
                        $infoPairs = [
                            [['label'=>'Owner Name',  'value'=>$tenant->owner_name,  'mono'=>false],
                             ['label'=>'Owner Email', 'value'=>$tenant->owner_email, 'mono'=>false]],
                            [['label'=>'Login Email', 'value'=>$tenant->ownerUser?->email ?? $tenant->owner_email, 'mono'=>false],
                             ['label'=>'Phone',       'value'=>$tenant->phone ?: '—', 'mono'=>false]],
                            [['label'=>'GST Number',  'value'=>$tenant->gst_number ?: '—', 'mono'=>true],
                             ['label'=>'Language',    'value'=>strtoupper($tenant->default_language), 'mono'=>false]],
                        ];
                    @endphp
                    @foreach ($infoPairs as $pairIdx => $pair)
                        @foreach ($pair as $colIdx => $field)
                            <div style="padding:1rem 1rem 1rem {{ $colIdx === 0 ? '0' : '1rem' }};{{ $pairIdx < count($infoPairs) - 1 ? 'border-bottom:1px solid var(--app-border)' : '' }};{{ $colIdx === 0 ? 'border-right:1px solid var(--app-border)' : '' }}">
                                <p style="font-size:.72rem;font-weight:600;color:var(--app-text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.4rem">{{ $field['label'] }}</p>
                                <p style="font-size:.9rem;font-weight:600;color:var(--app-text);{{ $field['mono'] ? 'font-family:monospace;' : '' }}word-break:break-all">{{ $field['value'] }}</p>
                            </div>
                        @endforeach
                    @endforeach
                    {{-- Address — full width --}}
                    <div style="grid-column:span 2;padding:1rem 0 0">
                        <p style="font-size:.72rem;font-weight:600;color:var(--app-text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.4rem">Address</p>
                        <p style="font-size:.9rem;font-weight:600;color:var(--app-text)">{{ $tenant->address }}</p>
                        <p style="font-size:.8rem;color:var(--app-text-muted);margin-top:.2rem">{{ $tenant->city }}, {{ $tenant->state }}</p>
                    </div>
                </div>
            </section>

            {{-- Technical --}}
            <section class="app-panel rounded-[2rem] border p-6">
                <h3 class="mb-5 text-sm font-semibold uppercase tracking-[0.2em] text-[var(--app-text-muted)]">Routing & Technical</h3>
                @php
                    $techPairs = [
                        [['label'=>'Subdomain',     'value'=>$tenant->subdomain.'.gymos.in', 'mono'=>true],
                         ['label'=>'Domain Mode',   'value'=>ucfirst($tenant->domain_mode),  'mono'=>false]],
                        [['label'=>'Database Mode', 'value'=>ucfirst($tenant->database_mode),'mono'=>false],
                         ['label'=>'Database Name', 'value'=>$tenant->database_name ?: 'Main database', 'mono'=>true]],
                    ];
                    if ($tenant->domain_mode === 'separate' && $tenant->custom_domain) {
                        $techPairs[] = [['label'=>'Custom Domain','value'=>$tenant->custom_domain,'mono'=>true], null];
                    }
                @endphp
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0">
                    @foreach ($techPairs as $pairIdx => $pair)
                        @foreach ($pair as $colIdx => $field)
                            @if ($field)
                                <div style="padding:1rem 1rem 1rem {{ $colIdx === 0 ? '0' : '1rem' }};{{ $pairIdx < count($techPairs) - 1 ? 'border-bottom:1px solid var(--app-border)' : '' }};{{ $colIdx === 0 ? 'border-right:1px solid var(--app-border)' : '' }}">
                                    <p style="font-size:.72rem;font-weight:600;color:var(--app-text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.4rem">{{ $field['label'] }}</p>
                                    <p style="font-size:.9rem;font-weight:600;color:var(--app-text);{{ $field['mono'] ? 'font-family:monospace;' : '' }}word-break:break-all">{{ $field['value'] }}</p>
                                </div>
                            @else
                                <div></div>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </section>

            {{-- Notes --}}
            @if ($tenant->notes)
                <section class="app-panel rounded-[2rem] border p-6">
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-[var(--app-text-muted)]">Internal Notes</h3>
                    <p class="text-sm leading-relaxed">{{ $tenant->notes }}</p>
                </section>
            @endif
        </div>

        {{-- Right: Subscriptions + Payments --}}
        <div class="space-y-6">

            {{-- Subscriptions --}}
            <section class="app-panel rounded-[2rem] border p-6">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-[var(--app-text-muted)]">Subscriptions</h3>
                    <span class="app-panel-strong rounded-full border px-2.5 py-0.5 text-xs font-semibold">{{ $tenant->subscriptions->count() }}</span>
                </div>
                <div class="space-y-3">
                    @forelse ($tenant->subscriptions->sortByDesc('id') as $subscription)
                        @php
                            $subSc = $statusColors[$subscription->status] ?? ['bg' => 'bg-white/10', 'text' => 'text-white', 'ring' => 'ring-white/20', 'dot' => 'bg-white'];
                        @endphp
                        <div class="app-panel-strong rounded-2xl border p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ $subscription->plan->name }}</p>
                                    <p class="app-muted mt-0.5 text-xs">Rs. {{ number_format($subscription->price_paise / 100, 2) }} / {{ $subscription->plan->billing_cycle }}</p>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $subSc['bg'] }} {{ $subSc['text'] }} {{ $subSc['ring'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $subSc['dot'] }}"></span>
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-[var(--app-text-muted)]">
                                @if ($subscription->start_date)
                                    <span>Start: {{ $subscription->start_date->format('d M Y') }}</span>
                                @endif
                                @if ($subscription->end_date)
                                    <span>End: {{ $subscription->end_date->format('d M Y') }}</span>
                                @endif
                                @if ($subscription->trial_end_date)
                                    <span>Trial ends: {{ $subscription->trial_end_date->format('d M Y') }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center gap-2 py-8 text-center">
                            <svg class="h-8 w-8 text-[var(--app-text-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                            <p class="app-muted text-sm">No subscriptions found.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Payments --}}
            <section class="app-panel rounded-[2rem] border p-6">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-[var(--app-text-muted)]">Payments</h3>
                    <span class="app-panel-strong rounded-full border px-2.5 py-0.5 text-xs font-semibold">{{ $tenant->payments->count() }}</span>
                </div>
                <div class="space-y-3">
                    @forelse ($tenant->payments->sortByDesc('paid_at') as $payment)
                        <div class="app-panel-strong rounded-2xl border p-4">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-base font-bold">Rs. {{ number_format($payment->amount_paise / 100, 2) }}</p>
                                <span class="app-panel rounded-full border px-2.5 py-0.5 text-xs font-medium capitalize">{{ $payment->payment_method }}</span>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-[var(--app-text-muted)]">
                                @if ($payment->paid_at)
                                    <span>{{ $payment->paid_at->format('d M Y, h:i A') }}</span>
                                @endif
                                @if ($payment->transaction_ref)
                                    <span class="font-mono">{{ $payment->transaction_ref }}</span>
                                @endif
                                @if ($payment->admin)
                                    <span>By {{ $payment->admin->name }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center gap-2 py-8 text-center">
                            <svg class="h-8 w-8 text-[var(--app-text-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            <p class="app-muted text-sm">No payments recorded.</p>
                        </div>
                    @endforelse
                </div>
            </section>

        </div>
    </div>
</x-layouts.admin>
