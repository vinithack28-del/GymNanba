<x-layouts.admin
    title="Dashboard"
    eyebrow="{{ __('dashboard.eyebrow') }}"
    heading="{{ __('dashboard.heading') }}"
    subheading="{{ __('dashboard.subheading') }}"
>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm text-slate-400">{{ __('dashboard.cards.total_tenants') }}</p>
            <p class="mt-4 text-4xl font-semibold">{{ $totalTenants }}</p>
            <p class="mt-2 text-sm text-slate-300">All non-archived gym tenants on the platform.</p>
        </div>
        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm text-slate-400">{{ __('dashboard.cards.active_tenants') }}</p>
            <p class="mt-4 text-4xl font-semibold text-emerald-300">{{ $activeTenants }}</p>
            <p class="mt-2 text-sm text-slate-300">Paying gyms currently active and operational.</p>
        </div>
        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm text-slate-400">{{ __('dashboard.cards.trials_active') }}</p>
            <p class="mt-4 text-4xl font-semibold text-sky-300">{{ $trialTenants }}</p>
            <p class="mt-2 text-sm text-slate-300">Tenants with trial subscriptions still within term.</p>
        </div>
        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm text-slate-400">{{ __('dashboard.cards.mrr') }}</p>
            <p class="mt-4 text-4xl font-semibold text-orange-300">Rs. {{ number_format($mrr / 100, 2) }}</p>
            <p class="mt-2 text-sm text-slate-300">Monthly recurring revenue normalized across billing cycles.</p>
        </div>
        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm text-slate-400">{{ __('dashboard.cards.renewals') }}</p>
            <p class="mt-4 text-4xl font-semibold text-amber-300">{{ $renewalsThisWeek }}</p>
            <p class="mt-2 text-sm text-slate-300">Subscriptions ending within the next 7 days.</p>
        </div>
        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-5">
            <p class="text-sm text-slate-400">{{ __('dashboard.cards.trials_expiring') }}</p>
            <p class="mt-4 text-4xl font-semibold text-fuchsia-300">{{ $trialsExpiring }}</p>
            <p class="mt-2 text-sm text-slate-300">Trial tenants that need follow-up before conversion.</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-400">{{ __('dashboard.sections.mrr_trend') }}</p>
                    <h3 class="mt-2 text-2xl font-semibold">{{ __('dashboard.sections.revenue_snapshot') }}</h3>
                </div>
                <span class="rounded-full bg-slate-950/70 px-3 py-1 text-xs text-slate-300">Last 12 months</span>
            </div>

            <div class="mt-8 flex h-64 items-end gap-3">
                @foreach ($mrrTrend as $point)
                    <div class="flex flex-1 flex-col items-center gap-3">
                        <div class="flex h-52 w-full items-end rounded-full bg-slate-950/70 p-2">
                            <div
                                class="w-full rounded-full bg-[linear-gradient(180deg,#f97316_0%,#22c55e_100%)]"
                                style="height: {{ max(10, (int) (($point['value'] / $maxTrend) * 100)) }}%;"
                            ></div>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-slate-400">{{ $point['label'] }}</p>
                            <p class="mt-1 text-xs font-medium text-slate-200">Rs. {{ number_format($point['value']) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6">
                    <p class="text-sm text-slate-400">{{ __('dashboard.sections.recent_activity') }}</p>
                    <h3 class="mt-2 text-2xl font-semibold">{{ __('dashboard.sections.audit_highlights') }}</h3>

            <div class="mt-6 space-y-4">
                @foreach ($recentActivities as $activity)
                    <article class="rounded-2xl border border-white/10 bg-slate-950/60 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold">{{ $activity->action_type }}</p>
                                <p class="mt-1 text-sm text-slate-300">{{ $activity->target_name ?: 'Platform event' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $activity->actor_name }} · {{ $activity->created_at?->format('d M Y, h:i A') }}</p>
                            </div>
                            <span class="rounded-full bg-white/5 px-3 py-1 text-[11px] uppercase tracking-[0.2em] text-sky-300">
                                {{ $activity->target_type }}
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>

    <section class="mt-6 rounded-[2rem] border border-white/10 bg-white/5 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-400">{{ __('dashboard.sections.renewals_due') }}</p>
                <h3 class="mt-2 text-2xl font-semibold">{{ __('dashboard.sections.upcoming_renewals') }}</h3>
            </div>
            <a href="{{ route('admin.subscriptions.index') }}" class="rounded-full border border-white/10 bg-slate-950/70 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">
                {{ __('dashboard.sections.view_subscriptions') }}
            </a>
        </div>

        <div class="mt-6 overflow-hidden rounded-[1.5rem] border border-white/10">
            <table class="w-full divide-y divide-white/10 text-left text-sm">
                <thead class="bg-slate-950/60 text-slate-300">
                    <tr>
                        <th class="px-4 py-3 font-medium">Gym</th>
                        <th class="px-4 py-3 font-medium">Plan</th>
                        <th class="px-4 py-3 font-medium">Renewal date</th>
                        <th class="px-4 py-3 font-medium">MRR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-white/5">
                    @forelse ($renewalsDue as $renewal)
                        <tr>
                            <td class="px-4 py-3">{{ $renewal->tenant->gym_name }}</td>
                            <td class="px-4 py-3">{{ $renewal->plan->name }}</td>
                            <td class="px-4 py-3">{{ $renewal->end_date?->format('d M Y') ?? 'Ongoing' }}</td>
                            <td class="px-4 py-3">Rs. {{ number_format($renewal->price_paise / 100, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-400">No renewals are currently due.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.admin>
