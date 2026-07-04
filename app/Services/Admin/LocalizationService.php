<?php

namespace App\Services\Admin;

use App\Models\PlatformLanguage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class LocalizationService
{
    public function updatePreferredLocale(?User $user, string $localeCode, object $session): bool
    {
        $locale = $this->findActiveLocale($localeCode);

        if (! $locale) {
            return false;
        }

        $session->put('locale', $locale->locale_code);

        if ($user) {
            $user->forceFill([
                'preferred_language' => $locale->locale_code,
            ])->save();
        }

        return true;
    }

    public function getActiveLanguages(): Collection
    {
        try {
            if (! Schema::hasTable('platform_languages')) {
                return collect();
            }

            return PlatformLanguage::query()
                ->where('is_active', true)
                ->orderBy('display_name')
                ->get();
        } catch (Throwable) {
            return collect();
        }
    }

    private function findActiveLocale(string $localeCode): ?PlatformLanguage
    {
        try {
            if (! Schema::hasTable('platform_languages')) {
                return null;
            }

            return PlatformLanguage::query()
                ->where('locale_code', $localeCode)
                ->where('is_active', true)
                ->first();
        } catch (Throwable) {
            return null;
        }
    }
}

