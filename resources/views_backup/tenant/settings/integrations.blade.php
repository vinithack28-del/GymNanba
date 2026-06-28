<x-layouts.admin :title="__('settings.nav.integrations')">

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

<div class="space-y-4">

        @php
            $webhookBase = url('/webhooks');
            $integrationDefs = [
                'whatsapp' => [
                    'name'  => 'WhatsApp Business API',
                    'desc'  => __('settings.integrations.whatsapp.desc'),
                    'icon'  => '💬',
                ],
                'razorpay' => [
                    'name'  => 'Razorpay',
                    'desc'  => __('settings.integrations.razorpay.desc'),
                    'icon'  => '₹',
                ],
                'biometric' => [
                    'name'  => __('settings.integrations.biometric.name'),
                    'desc'  => __('settings.integrations.biometric.desc'),
                    'icon'  => '🔍',
                ],
                'tally' => [
                    'name'  => __('settings.integrations.tally.name'),
                    'desc'  => __('settings.integrations.tally.desc'),
                    'icon'  => '📊',
                ],
            ];
        @endphp

        @foreach ($integrationDefs as $key => $def)
            @php $int = $integrations->get($key); $connected = $int?->isConnected(); @endphp
            <div class="rounded-2xl" style="background:var(--app-panel);border:1px solid var(--app-border)" x-data="{ open: {{ $connected ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between p-5 cursor-pointer" @click="open = !open">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl leading-none">{{ $def['icon'] }}</span>
                        <div>
                            <p class="text-sm font-semibold" style="color:var(--app-text)">{{ $def['name'] }}</p>
                            <p class="text-xs" style="color:var(--app-text-muted)">{{ $def['desc'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $connected ? 'text-emerald-400 bg-emerald-500/10' : 'text-red-400 bg-red-500/10' }}">
                            {{ $connected ? __('settings.integrations.connected') : __('settings.integrations.not_connected') }}
                        </span>
                        <svg class="h-4 w-4 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--app-text-muted)"><path d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <div x-show="open" x-cloak class="border-t px-5 pb-5 pt-4" style="border-color:var(--app-border)">
                    <form method="POST" action="{{ route('tenant.settings.integrations.update', $key) }}">
                        @csrf @method('PUT')

                        @if ($key === 'whatsapp')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.whatsapp.phone_number_id') }}</label>
                                    <input type="text" name="phone_number_id" value="{{ $int?->config['phone_number_id'] ?? '' }}"
                                           class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.whatsapp.api_token') }}</label>
                                    <input type="password" name="api_token" value="" placeholder="{{ $connected ? '••••••••' : '' }}"
                                           class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.whatsapp.verify_token') }}</label>
                                    <input type="text" name="verify_token" value="{{ $int?->config['verify_token'] ?? '' }}"
                                           class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.whatsapp.webhook_url') }}</label>
                                    <div class="flex gap-2">
                                        <input type="text" readonly value="{{ $webhookBase }}/whatsapp"
                                               class="flex-1 rounded-xl border px-3 py-2 text-sm font-mono opacity-60 cursor-default"
                                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $webhookBase }}/whatsapp')"
                                                class="rounded-xl border px-3 py-2 text-xs" style="border-color:var(--app-border);color:var(--app-text-muted)">{{ __('common.copy') }}</button>
                                    </div>
                                </div>
                            </div>

                        @elseif ($key === 'razorpay')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.razorpay.key_id') }}</label>
                                    <input type="text" name="key_id" value="{{ $int?->config['key_id'] ?? '' }}"
                                           class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.razorpay.key_secret') }}</label>
                                    <input type="password" name="key_secret" value="" placeholder="{{ $connected ? '••••••••' : '' }}"
                                           class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.razorpay.webhook_secret') }}</label>
                                    <input type="password" name="webhook_secret" value="" placeholder="{{ $connected ? '••••••••' : '' }}"
                                           class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.razorpay.webhook_url') }}</label>
                                    <div class="flex gap-2">
                                        <input type="text" readonly value="{{ $webhookBase }}/razorpay"
                                               class="flex-1 rounded-xl border px-3 py-2 text-sm font-mono opacity-60 cursor-default"
                                               style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $webhookBase }}/razorpay')"
                                                class="rounded-xl border px-3 py-2 text-xs" style="border-color:var(--app-border);color:var(--app-text-muted)">{{ __('common.copy') }}</button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="flex items-center gap-2 cursor-pointer text-sm" style="color:var(--app-text)">
                                        <input type="checkbox" name="test_mode" value="1" @checked($int?->config['test_mode'] ?? true)>
                                        {{ __('settings.integrations.razorpay.test_mode') }}
                                    </label>
                                </div>
                            </div>

                        @elseif ($key === 'biometric')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.biometric.device_type') }}</label>
                                    <select name="device_type" class="w-full rounded-xl border px-3 py-2 text-sm outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                        @foreach (['ZKTeco','eSSL','Mantra','Other'] as $dt)
                                            <option value="{{ $dt }}" @selected(($int?->config['device_type'] ?? '') === $dt)>{{ $dt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.biometric.ip') }}</label>
                                    <input type="text" name="ip_address" value="{{ $int?->config['ip_address'] ?? '' }}" placeholder="192.168.1.100"
                                           class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.biometric.port') }}</label>
                                    <input type="number" name="port" value="{{ $int?->config['port'] ?? 4370 }}"
                                           class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.biometric.serial') }}</label>
                                    <input type="text" name="device_serial" value="{{ $int?->config['device_serial'] ?? '' }}"
                                           class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                                           style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.biometric.sync') }}</label>
                                    <select name="sync_schedule" class="w-full rounded-xl border px-3 py-2 text-sm outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                        @foreach (['realtime' => __('settings.integrations.biometric.sync_realtime'), 'every_5' => __('settings.integrations.biometric.sync_5'), 'every_15' => __('settings.integrations.biometric.sync_15')] as $v => $l)
                                            <option value="{{ $v }}" @selected(($int?->config['sync_schedule'] ?? 'realtime') === $v)>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        @elseif ($key === 'tally')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-medium mb-1" style="color:var(--app-text-muted)">{{ __('settings.integrations.tally.format') }}</label>
                                    <select name="export_format" class="w-full rounded-xl border px-3 py-2 text-sm outline-none" style="background:var(--app-panel-strong);border-color:var(--app-border);color:var(--app-text)">
                                        <option value="tally_xml" @selected(($int?->config['export_format'] ?? 'tally_xml') === 'tally_xml')>Tally XML</option>
                                        <option value="csv" @selected(($int?->config['export_format'] ?? '') === 'csv')>CSV</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-2 pt-5">
                                    <label class="flex items-center gap-2 cursor-pointer text-sm" style="color:var(--app-text)">
                                        <input type="checkbox" name="auto_sync" value="1" @checked($int?->config['auto_sync'] ?? false)>
                                        {{ __('settings.integrations.tally.auto_sync') }}
                                    </label>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-3">
                            <button type="submit"
                                    class="rounded-xl px-4 py-2 text-sm font-medium text-white"
                                    style="background:var(--app-brand)">
                                {{ $connected ? __('settings.integrations.update') : __('settings.integrations.connect') }}
                            </button>

                            @if ($connected)
                                <button type="button"
                                        class="rounded-xl border px-4 py-2 text-sm font-medium text-amber-400"
                                        style="border-color:var(--app-border)"
                                        onclick="testIntegration('{{ $key }}', this)">
                                    {{ __('settings.integrations.test') }}
                                </button>
                            @endif
                        </div>
                    </form>

                    @if ($connected)
                        <form method="POST" action="{{ route('tenant.settings.integrations.disconnect', $key) }}" class="mt-3">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-300">
                                {{ __('settings.integrations.disconnect') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
    @endforeach

</div>

@push('scripts')
<script>
async function testIntegration(key, btn) {
    const orig = btn.textContent;
    btn.textContent = '...';
    btn.disabled = true;
    try {
        const res = await fetch(`/settings/integrations/${key}/test`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }
        });
        const data = await res.json();
        btn.textContent = data.test_passed ? '✓ OK' : '✗ Failed';
        btn.style.color = data.test_passed ? '#34d399' : '#f87171';
        setTimeout(() => { btn.textContent = orig; btn.disabled = false; btn.style.color = ''; }, 3000);
    } catch {
        btn.textContent = '✗ Error';
        btn.style.color = '#f87171';
        setTimeout(() => { btn.textContent = orig; btn.disabled = false; btn.style.color = ''; }, 3000);
    }
}
</script>
@endpush

</x-layouts.admin>
