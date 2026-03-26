<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

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
        $this->configureDefaults();
        $this->configureRateLimiting();
        $this->configureObservers();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    public function configureRateLimiting(): void
    {

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip().'|'.$request->input('login'));
        });


        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip().'|'.$request->input('email'));
        });

        RateLimiter::for('diagnose-upload', function (Request $request) {
            $maxAttempts = max(1, (int) config('services.diagnose_uploads.rate_limit_per_minute', 10));
            $limits = [
                Limit::perMinute($maxAttempts)->by('diagnose:ip:'.$request->ip()),
            ];

            if ($request->user() !== null) {
                $limits[] = Limit::perMinute($maxAttempts)->by('diagnose:user:'.$request->user()->id);
            }

            return $limits;
        });

        RateLimiter::for('password-update', function (Request $request) {
            $key = $request->user() !== null
                ? 'password-update:user:'.$request->user()->id
                : 'password-update:ip:'.$request->ip();

            return Limit::perHour(5)->by($key);
        });
        
    }

    protected function configureObservers(): void
    {
        User::observe(UserObserver::class);
    }

 
}