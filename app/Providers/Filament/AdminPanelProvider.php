<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\CourseCategories\CourseCategoryResource;
use App\Filament\Widgets\ArticlesPublishedChart;
use App\Filament\Widgets\ContentStatsOverview;
use App\Filament\Widgets\EditorialQueueWidget;
use App\Filament\Widgets\PendingCommentsWidget;
use App\Filament\Widgets\RecentArticlesWidget;
use App\Filament\Widgets\UnreadContactMessagesWidget;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->brandLogo(asset('al-shaheen.png'))
            ->brandLogoHeight('2.75rem')
            ->defaultThemeMode(ThemeMode::Light)
            ->colors([
                'primary' => Color::hex('#28414e'),
                'gray' => Color::Stone,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<link rel="stylesheet" href="'.asset('css/filament-admin-theme.css').'">',
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->resources([
                CourseCategoryResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                ContentStatsOverview::class,
                ArticlesPublishedChart::class,
                EditorialQueueWidget::class,
                RecentArticlesWidget::class,
                PendingCommentsWidget::class,
                UnreadContactMessagesWidget::class,
            ])
            ->navigationGroups([
                'Users',
                'Content',
                'Catalog',
                'Marketing',
                'Events',
                'Monetization',
                'Subscriptions',
                'Training',
                'Settings',
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
