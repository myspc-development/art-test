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

    public static function render(): string {
        $user_id = get_current_user_id();
        if (!self::can_view($user_id)) {
            return '<p class="ap-widget-no-access">' . esc_html__('Please log in.', 'artpulse') . '</p>';
        }

        if (function_exists('ap_widget_events')) {
            return wp_kses_post(ap_widget_events([]));
        }

        return '<p>' . esc_html__('Events content.', 'artpulse') . '</p>';
    }
}

EventsWidget::register();
