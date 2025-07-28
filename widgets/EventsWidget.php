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
            ['roles' => ['member','artist','organization'], 'group' => 'insights']
        );
    }

    public static function get_id(): string { return 'sample_events'; }
    public static function get_title(): string { return __('Events Widget','artpulse'); }
    public static function metadata(): array { return ['sample' => true]; }
    public static function can_view(): bool { return is_user_logged_in(); }

    public static function render(): void {
        if (!self::can_view()) {
            echo '<p class="ap-widget-no-access">' . esc_html__('Please log in.', 'artpulse') . '</p>';
            return;
        }
        if (function_exists('ap_widget_events')) {
            echo ap_widget_events([]);
        } else {
            echo '<p>' . esc_html__('Events content.', 'artpulse') . '</p>';
        }
    }
}

EventsWidget::register();
