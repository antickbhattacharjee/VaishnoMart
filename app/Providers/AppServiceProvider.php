<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
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
        // Pagination
        Paginator::useBootstrapFive();
        Paginator::defaultView('pagination.bootstrap-5');
        Paginator::defaultSimpleView('pagination.simple-bootstrap-5');

        // Rate Limiting Guards
        RateLimiter::for('login', function (Request $request) {
            $key = (string) $request->input('login') . '|' . $request->ip();
            return Limit::perMinute(5)->by($key)->response(function () {
                return back()->withErrors([
                    'login' => 'Too many login attempts. Please wait 60 seconds before trying again.',
                ]);
            });
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(6)->by($request->user()?->id ?: $request->ip())->response(function () {
                return back()->withErrors([
                    'error' => 'Too many OTP requests or attempts. For your security, please wait 60 seconds.',
                ]);
            });
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
