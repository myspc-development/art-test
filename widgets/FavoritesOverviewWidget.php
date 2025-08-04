<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Community\FavoritesManager;

if (!defined('ABSPATH')) { exit; }
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;

/**
 * Wrapper widget for Favorites Overview.
 */

class FavoritesOverviewWidget {
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
        if (!DashboardWidgetRegistry::get('widget_widget_favorites')) {
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

    public static function render(int $user_id = 0): string
    {
        $user_id = $user_id ?: get_current_user_id();
        if (!$user_id) {
            // Should not happen on the dashboard but keeps the widget safe for
            // unauthenticated contexts.
            return self::render_placeholder();
        }

        $favorites = FavoritesManager::get_user_favorites($user_id, 'artpulse_event');
        if (!$favorites) {
            return '<p>' . esc_html__('You have no favorite events yet.', 'artpulse') . '</p>';
        }

        $items = [];
        foreach ($favorites as $fav) {
            $link  = get_permalink((int) $fav->object_id);
            $title = get_the_title((int) $fav->object_id);
            if ($link && $title) {
                $items[] = '<li><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></li>';
            }
        }

        if (!$items) {
            return '<p>' . esc_html__('You have no favorite events yet.', 'artpulse') . '</p>';
        }

        return '<ul class="ap-favorites-overview">' . implode('', $items) . '</ul>';
    }

    public static function render_placeholder(): string
    {
        return '<div data-widget="' . esc_attr(self::id()) . '" data-widget-id="' . esc_attr(self::id()) . '" class="dashboard-widget"><div class="inside"><div class="ap-widget-placeholder">' .
            esc_html__('Favorites widget is under construction.', 'artpulse') .
            '</div></div></div>';
    }
}

FavoritesOverviewWidget::register();
