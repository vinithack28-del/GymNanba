<x-layouts.admin :title="__('settings.nav.account')">

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

<div class="space-y-6">

        {{-- Profile info --}}
        <div class="rounded-2xl p-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <h2 class="text-sm font-semibold mb-4" style="color:var(--app-text)">{{ __('settings.account.section.profile') }}</h2>
            <form method="POST" action="{{ route('tenant.settings.account.update') }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="flex items-start gap-6 mb-4">
                    <div class="flex-none">
                        @if ($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="Avatar" class="h-16 w-16 rounded-full object-cover" style="border:2px solid var(--app-border)">
                        @else
                            <div class="h-16 w-16 rounded-full flex items-center justify-center text-xl font-bold text-white" style="background:var(--app-brand)">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.account.field.avatar') }}</p>
                        <input type="file" name="avatar" accept=".jpg,.jpeg,.png" class="text-xs block" style="color:var(--app-text-muted)">
                        <p class="text-xs mt-1" style="color:var(--app-text-muted)">JPG/PNG · max 2 MB</p>
                        @error('avatar')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.account.field.name') }} *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.account.field.email') }}</label>
                        <input type="email" value="{{ $user->email }}" disabled
                               class="w-full rounded-xl border px-3 py-2 text-sm cursor-not-allowed opacity-60"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        <p class="text-xs mt-1" style="color:var(--app-text-muted)">{{ __('settings.account.email_readonly') }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.account.field.phone') }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+91XXXXXXXXXX"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @error('phone')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button type="submit" class="rounded-xl px-5 py-2 text-sm font-semibold text-white" style="background:var(--app-brand)">
                        {{ __('common.save') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Change password --}}
        <div class="rounded-2xl p-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <h2 class="text-sm font-semibold mb-4" style="color:var(--app-text)">{{ __('settings.account.section.password') }}</h2>
            <form method="POST" action="{{ route('tenant.settings.account.password') }}">
                @csrf @method('PUT')
                <div class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.account.field.current_password') }}</label>
                        <input type="password" name="current_password" autocomplete="current-password"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @error('current_password')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.account.field.new_password') }}</label>
                        <input type="password" name="new_password" autocomplete="new-password"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @error('new_password')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.account.field.confirm_password') }}</label>
                        <input type="password" name="new_password_confirmation" autocomplete="new-password"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    </div>
                    <p class="text-xs" style="color:var(--app-text-muted)">{{ __('settings.account.password_rules') }}</p>
                </div>
                <div class="flex justify-end mt-4">
                    <button type="submit" class="rounded-xl px-5 py-2 text-sm font-semibold text-white bg-amber-500">
                        {{ __('settings.account.change_password') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Active sessions --}}
        <div class="rounded-2xl p-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold" style="color:var(--app-text)">{{ __('settings.account.section.sessions') }}</h2>
                @if ($sessions->where('is_current', false)->count() > 0)
                    <form method="POST" action="{{ route('tenant.settings.account.sessions.terminate-others') }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-300">
                            {{ __('settings.account.sessions.sign_out_others') }}
                        </button>
                    </form>
                @endif
            </div>
            <div class="space-y-3">
                @foreach ($sessions as $session)
                    <div class="flex items-center justify-between py-2.5 border-t" style="border-color:var(--app-border)">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg flex items-center justify-center flex-none" style="background:var(--app-panel-strong)">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--app-text-muted)">
                                    @if ($session['device'] === 'Mobile')
                                        <rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18"/>
                                    @else
                                        <rect x="2" y="3" width="20" height="14" rx="2"/><polyline points="8 21 12 17 16 21"/>
                                    @endif
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium" style="color:var(--app-text)">
                                    {{ $session['device'] }}
                                    @if ($session['is_current'])
                                        <span class="ml-1 text-xs px-1.5 py-0.5 rounded-md text-emerald-400 bg-emerald-500/10">{{ __('settings.account.sessions.current') }}</span>
                                    @endif
                                </p>
                                <p class="text-xs" style="color:var(--app-text-muted)">{{ $session['ip'] }} · {{ $session['last_active'] }}</p>
                            </div>
                        </div>
                        @if (! $session['is_current'])
                            <form method="POST" action="{{ route('tenant.settings.account.sessions.terminate', $session['id']) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-300">{{ __('settings.account.sessions.terminate') }}</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

</x-layouts.admin>
