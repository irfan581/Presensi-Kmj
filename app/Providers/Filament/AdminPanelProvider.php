<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Models\User;
use App\Livewire\GantiPassword;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
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
            ->login(CustomLogin::class) 
            ->darkMode(true)
            
            // ✅ Menggunakan view kustom untuk area Logo & Nama
            ->brandLogo(fn () => view('filament.components.brand'))
            
            ->favicon(asset('images/login.png'))
            
            ->colors([
                'primary' => Color::Amber,
                'gray'    => Color::Zinc,
            ])
            
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')

            // ✅ Pastikan discover tetap ada agar menu & dashboard muncul
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

            ->pages([
                Pages\Dashboard::class,
            ])
            
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class, // Sudah dibuang
            ])

            ->plugins([
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: false,
                        hasAvatars: true,
                        slug: 'my-profile'
                    )
                    ->avatarUploadComponent(fn($fileUpload) => $fileUpload->directory('avatars'))
                    ->enableTwoFactorAuthentication(force: false)
                    ->passwordUpdateRules([
                        User::passwordRules()
                    ])
                    ->myProfileComponents([
                        'update_password' => GantiPassword::class,
                    ])
            ])
            
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

    public function boot(): void
    {
        // Bersihkan CSS ::after yang tadi agar tidak menumpuk
        FilamentView::registerRenderHook(
            'panels::head.end',
            fn (): string => Blade::render('<style>body { transition: background-color 0.3s ease; }</style>'),
        );
    }
}