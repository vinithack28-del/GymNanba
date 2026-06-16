<x-layouts.admin
    title="Audit Log"
    eyebrow="Module 4"
    heading="Audit Log"
    subheading="Immutable event history for admin actions, tenant changes, subscriptions, and settings updates."
>
    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5">
        <table class="w-full divide-y divide-white/10 text-left text-sm">
            <thead class="bg-slate-950/60 text-slate-300">
                <tr>
                    <th class="px-4 py-3 font-medium">Timestamp</th>
                    <th class="px-4 py-3 font-medium">Actor</th>
                    <th class="px-4 py-3 font-medium">Action</th>
                    <th class="px-4 py-3 font-medium">Target</th>
                    <th class="px-4 py-3 font-medium">Difference</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10 bg-white/5">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-4 py-4 text-slate-300">{{ $log->created_at?->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-4">
                            <p>{{ $log->actor_name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $log->actor_ip }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <span class="rounded-full bg-sky-500/15 px-3 py-1 text-xs uppercase tracking-[0.2em] text-sky-300">
                                {{ $log->action_type }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <p>{{ $log->target_name ?: 'Platform event' }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $log->target_type }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <pre class="whitespace-pre-wrap break-words text-xs text-slate-300">{{ json_encode($log->difference, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">No audit entries available yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</x-layouts.admin>
