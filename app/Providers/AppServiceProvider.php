<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

use Laravel\Sanctum\Sanctum;
use App\Models\PersonalAccessToken;

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
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // Global API limiter keyed by user id (when authenticated) or client IP.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute((int) env('RATE_LIMIT_API_PER_MINUTE', 120))
                ->by($request->user()?->id ?: $request->ip());
        });

        // Stricter limiter for authentication endpoints.
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute((int) env('RATE_LIMIT_AUTH_PER_MINUTE', 20))
                ->by(strtolower((string) $request->input('email')) . '|' . $request->ip());
        });
    }
}

?>
