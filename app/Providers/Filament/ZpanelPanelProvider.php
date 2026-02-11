<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ZpanelPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('zpanel')
            ->path('zpanel')
            ->login()

            // ->brandLogo(asset('images/maddox-official-logo.png'))
            // ->favicon(asset('images/favicon.ico'))
            
            ->profile(isSimple: false)
            ->sidebarCollapsibleOnDesktop()
            ->assets([
                \Filament\Support\Assets\Css::make('custom-stylesheet', asset('css/app/custom-stylesheet.css')),
                \Filament\Support\Assets\Css::make('custom-stylesheet-fontawesome-all.min', asset('css/app/custom-stylesheet-fontawesome-all.min.css')),
            ])

            ->brandLogo(asset('images/maddox-new-logo-horizontal.png'))
            ->favicon(asset('images/favicon.ico'))

            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                // FilamentInfoWidget::class,
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

            // ->plugins([
            //     \pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin::make()
            //     ->color(fn () => match (app()->environment()) {
            //         'production' => null,
            //         'staging' => Color::Orange,
            //         default => Color::Blue,
            //     })

            // ])
            
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
