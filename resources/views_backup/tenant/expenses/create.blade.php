<x-layouts.admin :title="__('expenses.nav.add')">

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('expenses.nav.add') }}</h1>
        <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('expenses.create.subtitle') }}</p>
    </div>
    <a href="{{ route('tenant.expenses.index') }}"
       class="px-3 py-1.5 text-sm rounded border"
       style="border-color:var(--app-border);color:var(--app-text-muted)">
        ← {{ __('expenses.nav.expenses') }}
    </a>
</div>

<div class="max-w-3xl">
    <form action="{{ route('tenant.expenses.store') }}" method="POST"
          class="rounded-xl p-6 space-y-0" style="background:var(--app-panel);border:1px solid var(--app-border)">
        @csrf
        @include('tenant.expenses._form')
        <div class="mt-6 flex gap-3">
            <button type="submit"
                    class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white"
                    style="background:var(--app-brand)">
                {{ __('expenses.create.submit') }}
            </button>
            <a href="{{ route('tenant.expenses.index') }}"
               class="px-6 py-2.5 rounded-lg text-sm border"
               style="border-color:var(--app-border);color:var(--app-text-muted)">
                {{ __('common.cancel') }}
            </a>
        </div>
    </form>
</div>

</x-layouts.admin>
