<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

/**
 * Wrapper widget for Favorites Overview.
 */
use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class FavoritesOverviewWidget implements DashboardWidgetInterface {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            self::icon(),
            self::description(),
            [self::class, 'render'],
            [ 'roles' => self::roles() ]
        );

        // Legacy alias used in older configs.
        if (!DashboardWidgetRegistry::get_widget('widget_widget_favorites')) {
            DashboardWidgetRegistry::register(
                'widget_widget_favorites',
                self::label() . ' (Legacy)',
                self::icon(),
                self::description(),
                [self::class, 'render'],
                [ 'roles' => self::roles() ]
            );
        }
    }

    public static function id(): string
    {
        return 'widget_favorites';
    }

    public static function label(): string
    {
        return __('Favorites Overview', 'artpulse');
    }

    public static function roles(): array
    {
        return ['member'];
    }

    public static function description(): string
    {
        return __('Your favorite artists and works.', 'artpulse');
    }

    public static function icon(): string
    {
        return 'heart';
    }

    public static function render(): string
    {
        if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) {
            return '';
        }
        if (function_exists('ap_widget_favorites')) {
            ob_start();
            ap_widget_favorites([]);
            return ob_get_clean();
        }
        return self::render_placeholder();
    }

    public static function render_placeholder(): string
    {
        return '<div class="ap-widget-placeholder">' .
            esc_html__('Favorites widget is under construction.', 'artpulse') .
            '</div>';
    }
}

FavoritesOverviewWidget::register();
