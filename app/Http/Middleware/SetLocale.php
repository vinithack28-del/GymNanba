<?php

namespace App\Http\Middleware;

use App\Models\PlatformLanguage;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $fallbackLocale = config('app.fallback_locale', 'en-IN');
        $locale = $request->user()?->preferred_language
            ?? $request->session()->get('locale')
            ?? config('app.locale', $fallbackLocale);

        app()->setLocale($this->resolveLocale($locale, $fallbackLocale));

        return $next($request);
    }

    private function resolveLocale(?string $locale, string $fallbackLocale): string
    {
        if (blank($locale)) {
            return $fallbackLocale;
        }

        try {
            if (! Schema::hasTable('platform_languages')) {
                return $fallbackLocale;
            }

            return PlatformLanguage::query()
                ->where('locale_code', $locale)
                ->where('is_active', true)
                ->value('locale_code') ?? $fallbackLocale;
        } catch (Throwable) {
            return $fallbackLocale;
        }
    }
}

