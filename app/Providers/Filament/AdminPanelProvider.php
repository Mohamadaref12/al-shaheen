<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\CourseCategories\CourseCategoryResource;
use App\Filament\Widgets\ArticlesPublishedChart;
use App\Filament\Widgets\ContentStatsOverview;
use App\Filament\Widgets\DashboardHeaderWidget;
use App\Filament\Widgets\EditorialQueueWidget;
use App\Filament\Widgets\NewsEditorialQueueWidget;
use App\Filament\Widgets\PendingCommentsWidget;
use App\Filament\Widgets\QuickActionsWidget;
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
            ->brandName('Al Shaheen')
            ->brandLogo(asset('al-shaheen.png'))
            ->brandLogoHeight('2.5rem')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('17.5rem')
            ->collapsedSidebarWidth('5rem')
            ->defaultThemeMode(ThemeMode::Light)
            ->colors([
                'primary' => Color::hex('#28414e'),
                'gray'    => Color::Stone,
                'danger'  => Color::Rose,
                'warning' => Color::Amber,
                'success' => Color::Emerald,
                'info'    => Color::Sky,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<link rel="stylesheet" href="'.asset('css/filament-admin-theme.css').'?v=3">',
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
                DashboardHeaderWidget::class,
                QuickActionsWidget::class,
                ContentStatsOverview::class,
                ArticlesPublishedChart::class,
                EditorialQueueWidget::class,
                NewsEditorialQueueWidget::class,
                RecentArticlesWidget::class,
                PendingCommentsWidget::class,
                UnreadContactMessagesWidget::class,
            ])
            ->navigationGroups([
                'Content',
                'Users',
                'Catalog',
                'Training',
                'Marketing',
                'Events',
                'Monetization',
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
