<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

class MyUpcomingEventsWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::get_id(),
            self::get_title(),
            'calendar',
            __('List of your upcoming events', 'artpulse'),
            [self::class, 'render'],
            [
                'roles' => ['member', 'artist'],
                'section' => self::get_section(),
            ]
        );
    }

    public static function get_id(): string { return 'my_upcoming_events'; }
    public static function get_title(): string { return __('My Upcoming Events', 'artpulse'); }
    public static function get_section(): string { return 'insights'; }

    public static function can_view(int $user_id): bool {
        return $user_id > 0;
    }

    public static function render(): string {
        $user_id = get_current_user_id();
        if (!self::can_view($user_id)) {
            return '<div class="notice notice-error"><p>' . esc_html__('Please log in.', 'artpulse') . '</p></div>';
        }

        return '<p>' . esc_html__('Upcoming events will appear here.', 'artpulse') . '</p>';
    }
}

MyUpcomingEventsWidget::register();
