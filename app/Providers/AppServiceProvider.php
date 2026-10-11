<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        // Toutes les routes publiques : 60 requêtes/min/IP
        RateLimiter::for('api', fn (Request $r) => Limit::perMinute(60)->by($r->ip()));

        // POST /contact : anti-spam 5 messages/min/IP
        RateLimiter::for('contact', fn (Request $r) => Limit::perMinute(5)->by($r->ip()));

        // POST /auth/login : 5 tentatives/min par IP+email
        RateLimiter::for('auth', fn (Request $r) => Limit::perMinute(5)->by($r->ip().'|'.$r->input('email')));

        // Routes /admin : 120 requêtes/min par token (ou IP si anonyme)
        RateLimiter::for('admin', fn (Request $r) => Limit::perMinute(120)->by(optional($r->user())->id ?: $r->ip()));

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
