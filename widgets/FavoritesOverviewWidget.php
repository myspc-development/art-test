<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

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

      public static function render(): string
      {
          if (function_exists('ap_widget_favorites')) {
              return ap_widget_favorites([]);
          }
          return self::render_placeholder();
      }

    public static function render_placeholder(): string
    {
        return '<div data-widget="' . esc_attr(self::id()) . '" data-widget-id="' . esc_attr(self::id()) . '" class="dashboard-widget"><div class="inside"><div class="ap-widget-placeholder">' .
            esc_html__('Favorites widget is under construction.', 'artpulse') .
            '</div></div></div>';
    }
}

FavoritesOverviewWidget::register();
