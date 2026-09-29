<?php

namespace App\Providers;

use App\Services\DeviceService;
use Illuminate\Support\Facades\Blade;
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
        // Bagikan status perangkat ke seluruh Blade view
        View::composer('*', function ($view) {
            $isMobile = DeviceService::isMobile();
            $view->with('isMobile', $isMobile);
            $view->with('isDesktop', !$isMobile);
        });

        // Directive Blade untuk render kondisional PC vs Mobile
        Blade::if('desktop', function () {
            return DeviceService::isDesktop();
        });

        Blade::if('mobile', function () {
            return DeviceService::isMobile();
        });
    }
}

