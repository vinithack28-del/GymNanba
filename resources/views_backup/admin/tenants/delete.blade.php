<x-layouts.admin
    title="Delete Tenant"
    eyebrow="Tenant"
    heading="Delete {{ $tenant->gym_name }}"
    subheading="This action permanently removes the tenant record, subscriptions, and payment entries currently stored in this app."
>
    <div class="app-panel max-w-3xl rounded-[2rem] border p-6">
        <div class="rounded-2xl border border-red-400/20 bg-red-500/10 p-5">
            <p class="text-lg font-semibold text-red-300">Confirm deletion</p>
            <p class="mt-2 text-sm text-red-200">You are about to delete <strong>{{ $tenant->gym_name }}</strong> owned by {{ $tenant->owner_name }}.</p>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="app-panel-strong rounded-2xl border p-4"><p class="app-muted text-xs uppercase tracking-[0.24em]">Owner email</p><p class="mt-2 font-semibold">{{ $tenant->owner_email }}</p></div>
            <div class="app-panel-strong rounded-2xl border p-4"><p class="app-muted text-xs uppercase tracking-[0.24em]">Subdomain</p><p class="mt-2 font-semibold">{{ $tenant->subdomain }}.gymos.in</p></div>
        </div>

        <form method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}" class="mt-6 flex gap-3">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-2xl bg-red-500 px-5 py-3 text-sm font-semibold text-white hover:bg-red-400">Delete tenant</button>
            <a href="{{ route('admin.tenants.show', $tenant) }}" class="app-panel-strong rounded-2xl border px-5 py-3 text-sm font-semibold hover:opacity-90">Cancel</a>
        </form>
    </div>
</x-layouts.admin>
