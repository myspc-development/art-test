<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

class EventsWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::get_id(),
            self::get_title(),
            'calendar',
            __('Sample upcoming events list.', 'artpulse'),
            [self::class, 'render'],
            [
                'roles'   => ['member','artist','organization'],
                'section' => self::get_section(),
            ]
        );
    }

    public static function get_id(): string { return 'sample_events'; }
    public static function get_title(): string { return __('Events Widget','artpulse'); }
    public static function get_section(): string { return 'insights'; }
    public static function metadata(): array { return ['sample' => true]; }
    public static function can_view(int $user_id): bool { return $user_id > 0; }

      public static function render(int $user_id = 0): string {
          $user_id = $user_id ?: get_current_user_id();
          if (!self::can_view($user_id)) {
              $content = '<div class="notice notice-error"><p>' . esc_html__('Please log in.', 'artpulse') . '</p></div>';
          } elseif (function_exists('ap_widget_events')) {
              $content = wp_kses_post(ap_widget_events([]));
          } else {
              $content = '<p>' . esc_html__('Events content.', 'artpulse') . '</p>';
          }
          return '<div data-widget-id="' . esc_attr(self::get_id()) . '">' . $content . '</div>';
      }
}

EventsWidget::register();
