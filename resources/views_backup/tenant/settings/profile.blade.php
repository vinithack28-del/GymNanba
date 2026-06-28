<x-layouts.admin :title="__('settings.nav.profile')">

@php
    $indianStates = ['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Andaman and Nicobar Islands','Chandigarh','Dadra & Nagar Haveli and Daman & Diu','Delhi','Jammu & Kashmir','Ladakh','Lakshadweep','Puducherry'];
    $businessTypes = ['gym','yoga_studio','crossfit','martial_arts','dance','sports_club','other'];
    $days = ['mon','tue','wed','thu','fri','sat','sun'];
@endphp

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

<div class="space-y-6">
    <form method="POST" action="{{ route('tenant.settings.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PUT')

            {{-- Logo & Cover --}}
            <div class="rounded-2xl p-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
                <h2 class="text-sm font-semibold mb-4" style="color:var(--app-text)">{{ __('settings.profile.section.media') }}</h2>
                <div class="flex flex-col sm:flex-row gap-6">
                    <div>
                        <p class="text-xs mb-2" style="color:var(--app-text-muted)">{{ __('settings.profile.field.logo') }}</p>
                        @if ($tenant->logo_url)
                            <img src="{{ $tenant->logo_url }}" alt="Logo" class="h-16 w-16 rounded-xl object-cover mb-2" style="border:1px solid var(--app-border)">
                        @endif
                        <input type="file" name="logo" accept=".jpg,.jpeg,.png,.svg"
                               class="block text-xs" style="color:var(--app-text-muted)">
                        <p class="text-xs mt-1" style="color:var(--app-text-muted)">JPG/PNG/SVG · max 2 MB</p>
                        @error('logo')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex-1">
                        <p class="text-xs mb-2" style="color:var(--app-text-muted)">{{ __('settings.profile.field.cover') }}</p>
                        @if ($tenant->cover_photo_url)
                            <img src="{{ $tenant->cover_photo_url }}" alt="Cover" class="h-20 w-full max-w-xs rounded-xl object-cover mb-2" style="border:1px solid var(--app-border)">
                        @endif
                        <input type="file" name="cover_photo" accept=".jpg,.jpeg,.png"
                               class="block text-xs" style="color:var(--app-text-muted)">
                        <p class="text-xs mt-1" style="color:var(--app-text-muted)">JPG/PNG · max 5 MB</p>
                        @error('cover_photo')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Basic Info --}}
            <div class="rounded-2xl p-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
                <h2 class="text-sm font-semibold mb-4" style="color:var(--app-text)">{{ __('settings.profile.section.basic') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php
                        $field = fn($name,$label,$val,$type='text',$req=true) => [$name,$label,$val,$type,$req];
                    @endphp

                    {{-- Gym Name --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.gym_name') }} *</label>
                        <input type="text" name="gym_name" value="{{ old('gym_name', $tenant->gym_name) }}"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @error('gym_name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Business Type --}}
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.business_type') }} *</label>
                        <select name="business_type" class="w-full rounded-xl border px-3 py-2 text-sm outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                            @foreach ($businessTypes as $bt)
                                <option value="{{ $bt }}" @selected(old('business_type', $tenant->business_type) === $bt)>
                                    {{ __('settings.profile.business_types.' . $bt) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.phone') }} *</label>
                        <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" placeholder="+91XXXXXXXXXX"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @error('phone')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.email') }} *</label>
                        <input type="email" name="email" value="{{ old('email', $tenant->email) }}"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @error('email')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Website --}}
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.website') }}</label>
                        <input type="url" name="website" value="{{ old('website', $tenant->website) }}" placeholder="https://"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @error('website')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- About --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.about') }}</label>
                        <textarea name="about" rows="3" maxlength="1000"
                                  class="w-full rounded-xl border px-3 py-2 text-sm outline-none resize-none"
                                  style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">{{ old('about', $tenant->about) }}</textarea>
                        @error('about')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="rounded-2xl p-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
                <h2 class="text-sm font-semibold mb-4" style="color:var(--app-text)">{{ __('settings.profile.section.address') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.address1') }} *</label>
                        <input type="text" name="address" value="{{ old('address', $tenant->address) }}"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @error('address')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.address2') }}</label>
                        <input type="text" name="address2" value="{{ old('address2', $tenant->address2) }}"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.city') }} *</label>
                        <input type="text" name="city" value="{{ old('city', $tenant->city) }}"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @error('city')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.state') }} *</label>
                        <select name="state" class="w-full rounded-xl border px-3 py-2 text-sm outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                            <option value="">{{ __('common.select') }}</option>
                            @foreach ($indianStates as $st)
                                <option value="{{ $st }}" @selected(old('state', $tenant->state) === $st)>{{ $st }}</option>
                            @endforeach
                        </select>
                        @error('state')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.pin') }} *</label>
                        <input type="text" name="pin" value="{{ old('pin', $tenant->pin) }}" maxlength="6" pattern="[0-9]{6}"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @error('pin')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Legal & Tax --}}
            <div class="rounded-2xl p-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
                <h2 class="text-sm font-semibold mb-4" style="color:var(--app-text)">{{ __('settings.profile.section.legal') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.gstin') }}</label>
                        <input type="text" name="gstin" value="{{ old('gstin', $tenant->gstin) }}" placeholder="22AAAAA0000A1Z5"
                               class="w-full rounded-xl border px-3 py-2 text-sm font-mono outline-none uppercase"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @error('gstin')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.pan') }}</label>
                        <input type="text" name="pan" value="{{ old('pan', $tenant->pan) }}" placeholder="AAAAA9999A"
                               class="w-full rounded-xl border px-3 py-2 text-sm font-mono outline-none uppercase"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                        @error('pan')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.profile.field.reg_number') }}</label>
                        <input type="text" name="reg_number" value="{{ old('reg_number', $tenant->reg_number) }}"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    </div>
                </div>
            </div>

            {{-- Social Links --}}
            <div class="rounded-2xl p-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
                <h2 class="text-sm font-semibold mb-4" style="color:var(--app-text)">{{ __('settings.profile.section.social') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">Instagram URL</label>
                        <input type="url" name="instagram" value="{{ old('instagram', $tenant->social_links['instagram'] ?? '') }}"
                               placeholder="https://instagram.com/yourgym"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">Facebook URL</label>
                        <input type="url" name="facebook" value="{{ old('facebook', $tenant->social_links['facebook'] ?? '') }}"
                               placeholder="https://facebook.com/yourgym"
                               class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                    </div>
                </div>
            </div>

            {{-- Operating Hours --}}
            <div class="rounded-2xl p-6" style="background:var(--app-panel);border:1px solid var(--app-border)">
                <h2 class="text-sm font-semibold mb-4" style="color:var(--app-text)">{{ __('settings.profile.section.hours') }}</h2>
                <div class="space-y-3">
                    @foreach ($days as $day)
                        @php
                            $h = $hours[$day] ?? ['open' => '06:00', 'close' => '22:00', 'closed' => false];
                            $closed = (bool) old("hours.{$day}.closed", $h['closed']);
                        @endphp
                        <div class="flex items-center gap-3">
                            <span class="w-10 text-sm font-medium" style="color:var(--app-text)">{{ strtoupper($day) }}</span>
                            <label class="flex items-center gap-1.5 text-xs cursor-pointer" style="color:var(--app-text-muted)">
                                <input type="checkbox" name="hours[{{ $day }}][closed]" value="1"
                                       class="hours-closed-cb"
                                       data-day="{{ $day }}"
                                       @checked($closed)>
                                {{ __('settings.profile.hours.closed') }}
                            </label>
                            <div id="hours-fields-{{ $day }}" class="{{ $closed ? 'hidden' : 'flex' }} items-center gap-2">
                                <input type="time" name="hours[{{ $day }}][open]"
                                       value="{{ old("hours.{$day}.open", $h['open']) }}"
                                       class="rounded-lg border px-2 py-1.5 text-sm outline-none"
                                       style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                <span class="text-xs" style="color:var(--app-text-muted)">–</span>
                                <input type="time" name="hours[{{ $day }}][close]"
                                       value="{{ old("hours.{$day}.close", $h['close']) }}"
                                       class="rounded-lg border px-2 py-1.5 text-sm outline-none"
                                       style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="rounded-xl px-6 py-2.5 text-sm font-semibold text-white"
                        style="background:var(--app-brand)">
                    {{ __('common.save') }}
                </button>
            </div>
    </form>
</div>

@push('scripts')
<script>
document.querySelectorAll('.hours-closed-cb').forEach(cb => {
    cb.addEventListener('change', function() {
        const fields = document.getElementById('hours-fields-' + this.dataset.day);
        fields?.classList.toggle('hidden', this.checked);
        fields?.classList.toggle('flex', !this.checked);
    });
});
</script>
@endpush

</x-layouts.admin>
