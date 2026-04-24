<?php

namespace App\Providers\Filament;

use App\Enums\PanelType;
use App\Filament\Pages\Auth\Login;
use App\Filament\Supervisor\Pages\SupervisorDashboard;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SupervisorPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        config(['session.cookie' => 'ms_supervisor_session']);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id(PanelType::SUPERVISOR->value)
            ->domain(str_replace(['http://', 'https://'], '', config('app.supervisor_domain')))
            ->path('')
            ->darkMode(false)
            ->authGuard('web')
            ->defaultThemeMode(ThemeMode::Light)
            ->brandLogo(fn() => view('components.logo'))
            ->brandLogoHeight('2rem')
            ->topNavigation()
            ->login(Login::class)
            ->colors([
                'primary' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Supervisor/Resources'), for: 'App\\Filament\\Supervisor\\Resources')
            ->discoverPages(in: app_path('Filament/Supervisor/Pages'), for: 'App\\Filament\\Supervisor\\Pages')
            ->pages([
                SupervisorDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Supervisor/Widgets'), for: 'App\\Filament\\Supervisor\\Widgets')
            ->widgets([])
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
            ])
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE, // Letakkan di posisi menu user berada
                fn() => view('components.custom-logout-button'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn() => view('components.login-logo'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn() => view('components.login-footer'),
            )
            ->renderHook(
                PanelsRenderHook::CONTENT_END,
                fn() => view('components.footer'),
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn(): string => Blade::render('components.realtime-server-time'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn(): string => Blade::render('filament.components.custom-styles'),
            )
            ->spa();
    }
}
