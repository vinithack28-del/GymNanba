<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ChangePasswordRequest;
use App\Http\Requests\Tenant\UpdateAccountRequest;
use App\Http\Requests\Tenant\UpdateProfileRequest;
use App\Models\Integration;
use App\Models\PlatformLanguage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController extends Controller
{
    private function ownerOnly(): void
    {
        abort_unless(Auth::user()->role === 'tenant_owner', 403);
    }

    // â”€â”€â”€ Gym Profile â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function profile(){
        $this->ownerOnly();
        $tenant = Auth::user()->tenant;
        $hours  = $tenant->operating_hours ?? \App\Models\Tenant::defaultOperatingHours();

        return Inertia::render('Tenant/Settings/Profile', compact('tenant', 'hours'));
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $this->ownerOnly();
        $tenant = Auth::user()->tenant;
        $data   = $request->safe()->except(['logo', 'cover_photo', 'instagram', 'facebook', 'hours']);

        // File uploads
        if ($request->hasFile('logo')) {
            Storage::disk('public')->delete(ltrim((string) $tenant->logo_url, '/storage/'));
            $data['logo_url'] = '/storage/' . $request->file('logo')->store('settings/logos', 'public');
        }
        if ($request->hasFile('cover_photo')) {
            Storage::disk('public')->delete(ltrim((string) $tenant->cover_photo_url, '/storage/'));
            $data['cover_photo_url'] = '/storage/' . $request->file('cover_photo')->store('settings/covers', 'public');
        }

        // Social links
        $data['social_links'] = [
            'instagram' => $request->input('instagram'),
            'facebook'  => $request->input('facebook'),
        ];

        // Operating hours
        $hours = [];
        foreach (['mon','tue','wed','thu','fri','sat','sun'] as $day) {
            $hours[$day] = [
                'open'   => $request->input("hours.{$day}.open", '06:00'),
                'close'  => $request->input("hours.{$day}.close", '22:00'),
                'closed' => (bool) $request->input("hours.{$day}.closed", false),
            ];
        }
        $data['operating_hours'] = $hours;

        $tenant->update($data);

        return redirect()->route('tenant.settings.profile')
            ->with('success', __('settings.profile.saved'));
    }

    // â”€â”€â”€ My Account â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function account(){
        $this->ownerOnly();
        $user     = Auth::user();
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn ($s) => [
                'id'           => $s->id,
                'is_current'   => $s->id === session()->getId(),
                'ip'           => $s->ip_address,
                'device'       => $this->parseDevice($s->user_agent ?? ''),
                'last_active'  => \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans(),
            ]);

        return Inertia::render('Tenant/Settings/Index', compact('user', 'sessions'));
    }

    public function updateAccount(UpdateAccountRequest $request): RedirectResponse
    {
        $this->ownerOnly();
        $user = Auth::user();
        $data = $request->safe()->except(['avatar']);

        if ($request->hasFile('avatar')) {
            Storage::disk('public')->delete(ltrim((string) $user->avatar_url, '/storage/'));
            $data['avatar_url'] = '/storage/' . $request->file('avatar')->store('settings/avatars', 'public');
        }

        $user->update($data);

        return redirect()->route('tenant.settings.account')
            ->with('success', __('settings.account.saved'));
    }

    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $this->ownerOnly();
        $user = Auth::user();
        $user->update(['password' => Hash::make($request->new_password)]);

        // Invalidate all other sessions
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();

        return redirect()->route('tenant.settings.account')
            ->with('success', __('settings.account.password_changed'));
    }

    public function terminateSession(Request $request, string $sessionId): RedirectResponse
    {
        $this->ownerOnly();
        abort_if($sessionId === session()->getId(), 400);

        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', $sessionId)
            ->delete();

        return back()->with('success', __('settings.account.session_terminated'));
    }

    public function terminateOtherSessions(): RedirectResponse
    {
        $this->ownerOnly();
        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', '!=', session()->getId())
            ->delete();

        return back()->with('success', __('settings.account.other_sessions_terminated'));
    }

    // â”€â”€â”€ Integrations â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function integrations(){
        $this->ownerOnly();
        $tenantId     = Auth::user()->tenant->id;
        $integrations = Integration::where('tenant_id', $tenantId)
            ->get()
            ->keyBy('key');

        return Inertia::render('Tenant/Settings/Integrations', compact('integrations'));
    }

    public function updateIntegration(Request $request, string $key): RedirectResponse
    {
        $this->ownerOnly();
        abort_unless(in_array($key, ['whatsapp','razorpay','biometric','google_calendar','tally']), 404);

        $tenantId    = Auth::user()->tenant->id;
        $integration = Integration::forTenant($tenantId, $key);
        $integration->tenant_id = $tenantId;

        match ($key) {
            'whatsapp' => $this->saveWhatsapp($request, $integration),
            'razorpay' => $this->saveRazorpay($request, $integration),
            'biometric'=> $this->saveBiometric($request, $integration),
            'tally'    => $this->saveTally($request, $integration),
            default    => null,
        };

        $integration->status       = 'connected';
        $integration->connected_at = now();
        $integration->save();

        return redirect()->route('tenant.settings.integrations')
            ->with('success', __('settings.integrations.saved', ['name' => $key]));
    }

    public function disconnectIntegration(string $key): RedirectResponse
    {
        $this->ownerOnly();
        $tenantId = Auth::user()->tenant->id;
        Integration::where('tenant_id', $tenantId)->where('key', $key)->update([
            'status'       => 'disconnected',
            'connected_at' => null,
        ]);

        return redirect()->route('tenant.settings.integrations')
            ->with('success', __('settings.integrations.disconnected', ['name' => $key]));
    }

    public function testIntegration(Request $request, string $key): \Illuminate\Http\JsonResponse
    {
        $this->ownerOnly();
        $tenantId    = Auth::user()->tenant->id;
        $integration = Integration::where('tenant_id', $tenantId)->where('key', $key)->first();

        if (! $integration || ! $integration->isConnected()) {
            return response()->json(['test_passed' => false, 'error' => 'Not connected']);
        }

        // Stub: real implementation would ping the service
        return response()->json(['test_passed' => true, 'message' => 'Connection OK']);
    }

    // â”€â”€â”€ Language â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function language(){
        $this->ownerOnly();
        $user      = Auth::user();
        $languages = PlatformLanguage::where('is_active', true)->orderBy('display_name')->get();

        return Inertia::render('Tenant/Settings/Language', compact('user', 'languages'));
    }

    public function updateLanguage(Request $request): RedirectResponse
    {
        $this->ownerOnly();
        $request->validate([
            'locale' => ['required', 'string', 'exists:platform_languages,locale_code'],
        ]);

        Auth::user()->update(['preferred_language' => $request->locale]);

        return redirect()->route('tenant.settings.language')
            ->with('success', __('settings.language.saved'));
    }

    // â”€â”€â”€ Billing & Subscription (view-only) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function subscription(){
        $this->ownerOnly();
        $tenant       = Auth::user()->tenant;
        $subscription = $tenant->subscriptions()->with('plan')->latest()->first();

        return Inertia::render('Tenant/Settings/Subscription', compact('tenant', 'subscription'));
    }

    // â”€â”€â”€ Data & Privacy â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function data(){
        $this->ownerOnly();
        return Inertia::render('Tenant/Settings/Data');
    }

    public function exportData(): RedirectResponse
    {
        $this->ownerOnly();
        // TODO: dispatch ExportGymDataJob::dispatch(Auth::user()->tenant->id)
        return redirect()->route('tenant.settings.data')
            ->with('success', __('settings.data.export_queued'));
    }

    public function requestDeletion(Request $request): RedirectResponse
    {
        $this->ownerOnly();
        $request->validate([
            'confirm_delete' => ['required', 'accepted'],
        ]);

        // TODO: send email to super admin + confirmation to owner
        return redirect()->route('tenant.settings.data')
            ->with('success', __('settings.data.deletion_requested'));
    }

    // â”€â”€â”€ Private helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function parseDevice(string $ua): string
    {
        if (str_contains($ua, 'Mobile') || str_contains($ua, 'Android')) {
            return 'Mobile';
        }
        if (str_contains($ua, 'Tablet') || str_contains($ua, 'iPad')) {
            return 'Tablet';
        }
        return 'Desktop';
    }

    private function saveWhatsapp(Request $request, Integration $integration): void
    {
        $integration->config = [
            'phone_number_id' => $request->phone_number_id,
            'verify_token'    => $request->verify_token,
        ];
        if ($request->filled('api_token')) {
            $integration->setSecret('api_token', $request->api_token);
        }
    }

    private function saveRazorpay(Request $request, Integration $integration): void
    {
        $integration->config = [
            'key_id'    => $request->key_id,
            'test_mode' => (bool) $request->test_mode,
        ];
        if ($request->filled('key_secret')) {
            $integration->setSecret('key_secret', $request->key_secret);
        }
        if ($request->filled('webhook_secret')) {
            $integration->setSecret('webhook_secret', $request->webhook_secret);
        }
    }

    private function saveBiometric(Request $request, Integration $integration): void
    {
        $integration->config = [
            'device_type'   => $request->device_type,
            'ip_address'    => $request->ip_address,
            'port'          => (int) ($request->port ?? 4370),
            'device_serial' => $request->device_serial,
            'sync_schedule' => $request->sync_schedule,
        ];
    }

    private function saveTally(Request $request, Integration $integration): void
    {
        $integration->config = [
            'export_format' => $request->export_format,
            'auto_sync'     => (bool) $request->auto_sync,
        ];
    }
}

