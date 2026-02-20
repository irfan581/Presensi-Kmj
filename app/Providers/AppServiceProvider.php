<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Pagination\Paginator;
use Livewire\Livewire;

// Models
use App\Models\Presensi;
use App\Models\KunjunganToko;
use App\Models\Izin;

// Observers
use App\Observers\PresensiObserver;
use App\Observers\KunjunganTokoObserver;
use App\Observers\IzinObserver;

// Filament Breezy Components
use Jeffgreco13\FilamentBreezy\Livewire\TwoFactorAuthentication;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;
use Jeffgreco13\FilamentBreezy\Livewire\UpdatePassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ✅ Register Telescope hanya jika di environment lokal agar hemat resource di server
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ─── UI & Pagination ──────────────────────────────────
        Paginator::useTailwind();

        // ─── Livewire Components ──────────────────────────────
        Livewire::component('personal_info', PersonalInfo::class);
        Livewire::component('update_password', UpdatePassword::class);
        Livewire::component('two-factor-authentication', TwoFactorAuthentication::class);
        Livewire::component('two_factor_authentication', TwoFactorAuthentication::class);

        // ─── Observers ────────────────────────────────────────
        // Pastikan file observer ini ada di folder app/Observers/
        Presensi::observe(PresensiObserver::class);
        KunjunganToko::observe(KunjunganTokoObserver::class);
        Izin::observe(IzinObserver::class);

        // ─── Rate Limiters ────────────────────────────────────
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // ✅ LOGIN: Mencegah brute force (10 percobaan/menit per IP)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip())
                ->response(fn() => response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.',
                ], 429));
        });

        // ✅ UPLOAD: Membatasi upload foto (20 request/menit per user)
        RateLimiter::for('upload', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->user()?->id ?? $request->ip())
                ->response(fn() => response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak upload. Mohon tunggu sebentar.',
                ], 429));
        });

        // ✅ SENSITIVE: Ganti password & reset (5 request/menit per user)
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