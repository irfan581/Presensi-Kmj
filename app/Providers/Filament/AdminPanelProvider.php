<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login() 
            
            // --- IDENTITAS & BRANDING ---
            ->brandName('Kembar Jaya') 
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Zinc, 
            ])
            ->renderHook('panels::footer.before', fn () => null)

            // --- PENGATURAN NOTIFIKASI (WAJIB) ---
            // Baris ini sekarang aman karena tabel 'notifications' (tgl 16) sudah dibuat
            ->databaseNotifications() 
            ->databaseNotificationsPolling('30s') 
            
            // --- UI & UX OPTIMIZATION ---
            ->sidebarCollapsibleOnDesktop() 
            ->globalSearchKeyBindings(['command+k', 'ctrl+k']) 
            ->maxContentWidth('full') 
            
            // --- PLUGINS ---
            ->plugins([
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true, 
                        shouldRegisterNavigation: false, 
                        hasAvatars: false, 
                    )
                    ->enableTwoFactorAuthentication(
                        force: false 
                    )
            ])

            // --- RESOURCE & PAGE DISCOVERY ---
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])

            // --- WIDGETS ---
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Kosong: agar Widget Statistik & Tabel Kustom Anda berada di posisi paling atas
            ])

            // --- MIDDLEWARE ---
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}