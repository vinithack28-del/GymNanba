<x-layouts.admin :title="__('settings.nav.language')">

<div class="mb-2">
    <h1 class="text-xl font-semibold" style="color:var(--app-text)">{{ __('settings.title') }}</h1>
    <p class="text-sm mt-0.5" style="color:var(--app-text-muted)">{{ __('settings.subtitle') }}</p>
</div>

@include('tenant.settings._nav')

@if (session('success'))
    <div class="mb-4 rounded-xl px-4 py-3 text-sm bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
        {{ session('success') }}
    </div>
@endif

<div class="rounded-2xl p-6 max-w-lg" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <h2 class="text-sm font-semibold mb-1" style="color:var(--app-text)">{{ __('settings.language.section.title') }}</h2>
            <p class="text-xs mb-5" style="color:var(--app-text-muted)">{{ __('settings.language.section.desc') }}</p>

            <form method="POST" action="{{ route('tenant.settings.language.update') }}">
                @csrf @method('PUT')

                <div class="space-y-3 mb-6">
                    @foreach ($languages as $lang)
                        <label class="flex items-center gap-4 p-3 rounded-xl cursor-pointer transition-colors {{ $user->preferred_language === $lang->locale_code ? 'border' : 'border' }}"
                               style="{{ $user->preferred_language === $lang->locale_code ? 'background:color-mix(in srgb,var(--app-brand) 8%,transparent);border-color:var(--app-brand)' : 'background:var(--app-panel-strong);border-color:var(--app-border)' }}">
                            <input type="radio" name="locale" value="{{ $lang->locale_code }}"
                                   @checked($user->preferred_language === $lang->locale_code)
                                   class="accent-[var(--app-brand)]">
                            <div class="flex-1">
                                <p class="text-sm font-medium" style="color:var(--app-text)">{{ $lang->display_name }}</p>
                                <p class="text-xs" style="color:var(--app-text-muted)">{{ $lang->locale_code }}</p>
                            </div>
                            @if ($lang->completeness_pct < 100)
                                <span class="text-xs px-2 py-0.5 rounded-full" style="background:var(--app-panel);color:var(--app-text-muted)">
                                    {{ $lang->completeness_pct }}%
                                </span>
                            @endif
                        </label>
                    @endforeach
                </div>

                @error('locale')<p class="text-xs text-red-400 mb-3">{{ $message }}</p>@enderror

                <button type="submit" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-white" style="background:var(--app-brand)">
                    {{ __('settings.language.apply') }}
                </button>
            </form>

            <p class="text-xs mt-4" style="color:var(--app-text-muted)">{{ __('settings.language.note') }}</p>
</div>

</x-layouts.admin>
