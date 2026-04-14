<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Responses\LoginResponse;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            function (): string {
                if (app()->environment('production')) {
                    return Blade::render('
                    <script>
                        document.oncontextmenu = function() { return false; };
                    </script>
                ');
                }
                return '';
            },
        );
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn() => view('components.meta-tags'),
        );
    }
}
