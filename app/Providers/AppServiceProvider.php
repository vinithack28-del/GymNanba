<?php

namespace App\Providers;

use App\Services\Admin\LocalizationService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['components.layouts.admin', 'auth.login'], function ($view): void {
            $view->with('activePortalLanguages', app(LocalizationService::class)->getActiveLanguages());
        });
    }
}

