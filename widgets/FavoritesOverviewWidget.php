<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

/**
 * Wrapper widget for Favorites Overview.
 */
use ArtPulse\Core\DashboardWidgetRegistry;

class FavoritesOverviewWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            'widget_favorites',
            __('Favorites Overview', 'artpulse'),
            'heart',
            __('Your favorite artists and works.', 'artpulse'),
            [self::class, 'render'],
            [ 'roles' => ['member'] ]
        );
    }

    public static function render(): void {
        if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) {
            return;
        }
        if (function_exists('ap_widget_favorites')) {
            echo ap_widget_favorites([]);
        } else {
            echo self::render_placeholder();
        }
    }

    public static function render_placeholder(): string
    {
        return '<div class="ap-widget-placeholder">' .
            esc_html__('Favorites widget is under construction.', 'artpulse') .
            '</div>';
    }
}

FavoritesOverviewWidget::register();
