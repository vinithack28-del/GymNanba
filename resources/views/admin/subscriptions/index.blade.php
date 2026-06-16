<x-layouts.admin
    title="Subscriptions"
    eyebrow="Module 3"
    heading="Subscriptions"
    subheading="Track plan assignments, trial status, renewal dates, and admin ownership of subscription creation."
>
    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
        <table class="w-full divide-y divide-white/10 text-left text-sm">
            <thead class="bg-slate-950/60 text-slate-300">
                <tr>
                    <th class="px-4 py-3 font-medium">Gym</th>
                    <th class="px-4 py-3 font-medium">Plan</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Start</th>
                    <th class="px-4 py-3 font-medium">End / Trial</th>
                    <th class="px-4 py-3 font-medium">Price</th>
                    <th class="px-4 py-3 font-medium">Created by</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10 bg-white/5">
                @forelse ($subscriptions as $subscription)
                    <tr>
                        <td class="px-4 py-4">{{ $subscription->tenant->gym_name }}</td>
                        <td class="px-4 py-4">{{ $subscription->plan->name }}</td>
                        <td class="px-4 py-4">
                            <span class="rounded-full {{ $subscription->status === 'trial' ? 'bg-sky-500/15 text-sky-300' : ($subscription->status === 'cancelled' ? 'bg-slate-500/15 text-slate-300' : 'bg-emerald-500/15 text-emerald-300') }} px-3 py-1 text-xs uppercase tracking-[0.2em]">
                                {{ $subscription->status }}
                            </span>
                        </td>
                        <td class="px-4 py-4">{{ $subscription->start_date?->format('d M Y') }}</td>
                        <td class="px-4 py-4">
                            @if ($subscription->trial_end_date)
                                Trial until {{ $subscription->trial_end_date->format('d M Y') }}
                            @else
                                {{ $subscription->end_date?->format('d M Y') ?? 'Ongoing' }}
                            @endif
                        </td>
                        <td class="px-4 py-4">Rs. {{ number_format($subscription->price_paise / 100, 2) }}</td>
                        <td class="px-4 py-4">{{ $subscription->creator?->name ?? 'System' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">No subscriptions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $subscriptions->links() }}
    </div>
</x-layouts.admin>
