<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePlatformLanguageRequest;
use App\Models\PlatformLanguage;
use App\Services\Admin\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settingsService)
    {
    }

    public function index(): View
    {
        return view('admin.settings.index', $this->settingsService->getIndexData());
    }

    public function updateLanguage(UpdatePlatformLanguageRequest $request, PlatformLanguage $language): RedirectResponse
    {
        $updated = $this->settingsService->updateLanguage(
            $language,
            (bool) $request->validated()['is_active'],
        );

        if (! $updated) {
            return redirect()->route('admin.settings.index')->with('error', 'Language completeness must be at least 90% before enabling.');
        }

        return redirect()->route('admin.settings.index')->with('status', 'Language status updated.');
    }
}
