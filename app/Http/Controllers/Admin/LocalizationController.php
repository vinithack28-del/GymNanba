<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminLocaleRequest;
use App\Services\Admin\LocalizationService;
use Illuminate\Http\RedirectResponse;

class LocalizationController extends Controller
{
    public function __construct(private readonly LocalizationService $localizationService)
    {
    }

    public function update(UpdateAdminLocaleRequest $request): RedirectResponse
    {
        $updated = $this->localizationService->updatePreferredLocale(
            $request->user(),
            $request->validated()['locale_code'],
            $request->session(),
        );

        if (! $updated) {
            return back()->with('error', __('common.language_update_failed'));
        }

        return back()->with('status', __('common.language_updated'));
    }
}
