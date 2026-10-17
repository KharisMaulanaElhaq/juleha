<?php

namespace App\Providers;

use App\Listeners\UpdateUserLastLogin;
use App\Models\Setting;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, UpdateUserLastLogin::class);

        // Bagikan seluruh pengaturan situs ke semua view blade secara reaktif
        View::composer('*', function ($view) {
            $view->with('siteSettings', Setting::allCached());
            // Berikan juga fallback variabel $settings agar view lama tetap berjalan normal
            if (! $view->offsetExists('settings')) {
                $view->with('settings', Setting::allCached());
            }
        });
    }
}
