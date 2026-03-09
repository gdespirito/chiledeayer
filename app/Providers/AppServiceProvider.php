<?php

namespace App\Providers;

use App\Listeners\AwardPoints;
use App\Listeners\EvaluateBadges;
use App\Listeners\NotifyPhotoOwner;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureGates();
        $this->configureRateLimiting();

        Event::subscribe(AwardPoints::class);
        Event::subscribe(EvaluateBadges::class);
        Event::subscribe(NotifyPhotoOwner::class);
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

    /**
     * Configure authorization gates.
     */
    protected function configureGates(): void
    {
        Gate::define('admin', fn (User $user): bool => $user->is_admin);
    }

    /**
     * Configure rate limiting.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perHour(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
