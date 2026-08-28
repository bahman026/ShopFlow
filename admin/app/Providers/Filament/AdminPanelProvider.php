<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Http\Middleware\SetLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->assets([
                Css::make('persian-font', asset('css/persian-font.css')),
            ])
            ->renderHook(
                'panels::body.start',
                fn (): string => app()->getLocale() === 'fa'
                    ? '<style>*{font-family:"A Iranian Sans",ui-sans-serif,system-ui,sans-serif!important}</style>'
                    : '',
            )
            ->userMenuItems([
                MenuItem::make()
                    ->label('English')
                    ->icon('heroicon-o-language')
                    ->url(fn (): string => route('locale.switch', 'en')),
                MenuItem::make()
                    ->label('فارسی')
                    ->icon('heroicon-o-language')
                    ->url(fn (): string => route('locale.switch', 'fa')),
            ])
            // Pulse and the log viewer live outside Filament, each with its own
            // stylesheet and its own full-page layout. Embedding them in a
            // panel page squeezed them into an unusable frame, so these are
            // plain links that open in a new tab — the panel stays put in the
            // other tab. Visibility follows the same gates the routes use, so
            // a plain admin never sees a link they cannot open.
            ->navigationItems([
                NavigationItem::make('server-health')
                    ->label(fn (): string => trans('system.health_label'))
                    ->icon('heroicon-o-heart')
                    ->url(fn (): string => url((string) config('pulse.path', 'pulse')), shouldOpenInNewTab: true)
                    ->visible(fn (): bool => Auth::user()?->can('viewPulse') === true)
                    ->sort(2),
                NavigationItem::make('application-logs')
                    ->label(fn (): string => trans('system.logs_label'))
                    ->icon('heroicon-o-document-text')
                    ->url(fn (): string => url((string) config('log-viewer.route_path', 'log-viewer')), shouldOpenInNewTab: true)
                    ->visible(fn (): bool => Auth::user()?->can('viewLogViewer') === true)
                    ->sort(3),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
                SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
