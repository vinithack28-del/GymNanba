<x-layouts.admin :title="__('settings.nav.data')">

<div class="mb-2">
    <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('settings.title') }}</h1>
    <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('settings.subtitle') }}</p>
</div>

@include('tenant.settings._nav')

@if (session('success'))
    <div class="mb-2 rounded-xl px-4 py-3 text-sm bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
        {{ session('success') }}
    </div>
@endif

<div class="space-y-6 max-w-2xl">

        {{-- Export --}}
        <div class="rounded-2xl p-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <div class="flex items-start gap-4">
                <div class="h-10 w-10 rounded-xl flex items-center justify-center flex-none" style="background:color-mix(in srgb,var(--app-brand) 12%,transparent)">
                    <svg class="h-5 w-5" style="color:var(--app-brand)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-sm font-semibold mb-1" style="color:var(--app-text)">{{ __('settings.data.export.title') }}</h2>
                    <p class="text-xs mb-4" style="color:var(--app-text-muted)">{{ __('settings.data.export.desc') }}</p>
                    <ul class="text-xs mb-4 space-y-1" style="color:var(--app-text-muted)">
                        @foreach (['Members','Payments','Attendance','Expenses','Staff'] as $item)
                            <li class="flex items-center gap-1.5">
                                <span style="color:var(--app-brand)">✓</span> {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                    <form method="POST" action="{{ route('tenant.settings.data.export') }}">
                        @csrf
                        <button type="submit" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-white" style="background:var(--app-brand)">
                            {{ __('settings.data.export.button') }}
                        </button>
                    </form>
                    <p class="text-xs mt-2" style="color:var(--app-text-muted)">{{ __('settings.data.export.email_note') }}</p>
                </div>
            </div>
        </div>

        {{-- Account Deletion --}}
        <div class="rounded-2xl p-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <div class="flex items-start gap-4">
                <div class="h-10 w-10 rounded-xl flex items-center justify-center flex-none bg-red-500/10">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-sm font-semibold mb-1 text-red-400">{{ __('settings.data.delete.title') }}</h2>
                    <p class="text-xs mb-4" style="color:var(--app-text-muted)">{{ __('settings.data.delete.desc') }}</p>

                    <div x-data="{ confirm: false }">
                        <button type="button" @click="confirm = true"
                                x-show="!confirm"
                                class="rounded-xl border border-red-500/40 px-5 py-2.5 text-sm font-semibold text-red-400 hover:bg-red-500/10">
                            {{ __('settings.data.delete.button') }}
                        </button>

                        <div x-show="confirm" x-cloak
                             class="rounded-xl p-4" style="background:var(--app-panel-strong);border:1px solid var(--app-border)">
                            <p class="text-sm font-medium mb-3 text-red-400">{{ __('settings.data.delete.confirm_title') }}</p>
                            <p class="text-xs mb-4" style="color:var(--app-text-muted)">{{ __('settings.data.delete.confirm_desc') }}</p>
                            <form method="POST" action="{{ route('tenant.settings.data.delete-request') }}">
                                @csrf
                                <label class="flex items-start gap-2 mb-4 cursor-pointer">
                                    <input type="checkbox" name="confirm_delete" value="1" class="mt-0.5" required>
                                    <span class="text-xs" style="color:var(--app-text)">{{ __('settings.data.delete.confirm_checkbox') }}</span>
                                </label>
                                @error('confirm_delete')<p class="text-xs text-red-400 mb-2">{{ $message }}</p>@enderror
                                <div class="flex gap-3">
                                    <button type="submit" class="rounded-xl px-4 py-2 text-sm font-semibold text-white bg-red-500">
                                        {{ __('settings.data.delete.submit') }}
                                    </button>
                                    <button type="button" @click="confirm = false"
                                            class="rounded-xl border px-4 py-2 text-sm font-medium"
                                            style="border-color:var(--app-border);color:var(--app-text-muted)">
                                        {{ __('common.cancel') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</div>

</x-layouts.admin>
