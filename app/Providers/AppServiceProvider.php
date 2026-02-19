<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Pagination\Paginator;
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
        // Fix untuk MySQL versi lama
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();
        // Share settings with all views
        try {
            $settings = \App\Models\Setting::all()->pluck('value', 'key');
            \Illuminate\Support\Facades\View::share('app_settings', $settings);
        } catch (\Exception $e) {
            // Fallback if table doesn't exist yet (e.g. during migration)
            \Illuminate\Support\Facades\View::share('app_settings', []);
        }
    }
}