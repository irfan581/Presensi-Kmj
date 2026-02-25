<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Gate; 
use Illuminate\Pagination\Paginator;
use Livewire\Livewire;

use App\Models\Presensi;
use App\Models\KunjunganToko;
use App\Models\Izin;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

use App\Policies\KunjunganTokoPolicy;
use App\Policies\ActivityPolicy;
use App\Policies\PresensiPolicy; 
use App\Policies\IzinPolicy;

use App\Observers\PresensiObserver;
use App\Observers\KunjunganTokoObserver;
use App\Observers\IzinObserver;

use Jeffgreco13\FilamentBreezy\Livewire\TwoFactorAuthentication;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;
use Jeffgreco13\FilamentBreezy\Livewire\UpdatePassword;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        Gate::policy(KunjunganToko::class, KunjunganTokoPolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
        Gate::policy(Presensi::class, PresensiPolicy::class);
        Gate::policy(Izin::class, IzinPolicy::class);

        Paginator::useTailwind();

        Livewire::component('personal_info', PersonalInfo::class);
        Livewire::component('update_password', UpdatePassword::class);
        Livewire::component('two-factor-authentication', TwoFactorAuthentication::class);
        Livewire::component('two_factor_authentication', TwoFactorAuthentication::class);

        Presensi::observe(PresensiObserver::class);
        KunjunganToko::observe(KunjunganTokoObserver::class);
        Izin::observe(IzinObserver::class);

        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip())
                ->response(fn() => response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.',
                ], 429));
        });

        RateLimiter::for('upload', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->user()?->id ?? $request->ip())
                ->response(fn() => response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak upload. Mohon tunggu sebentar.',
                ], 429));
        });

        RateLimiter::for('sensitive', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->user()?->id ?? $request->ip())
                ->response(fn() => response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.',
                ], 429));
        });
    }
}