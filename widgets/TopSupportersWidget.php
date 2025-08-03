<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

class TopSupportersWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::get_id(),
            self::get_title(),
            'users',
            __('Your top supporters', 'artpulse'),
            [self::class, 'render'],
            [
                'roles' => ['organization'],
                'section' => self::get_section(),
            ]
        );
    }

    public static function get_id(): string { return 'top_supporters'; }
    public static function get_title(): string { return __('Top Supporters', 'artpulse'); }
    public static function get_section(): string { return 'insights'; }

    public static function can_view(int $user_id): bool {
        return user_can($user_id, 'organization');
    }

    public static function render(int $user_id): string {
        if (!self::can_view($user_id)) {
            return '<div class="notice notice-error"><p>' . esc_html__('You do not have access.', 'artpulse') . '</p></div>';
        }
        return '<p>' . esc_html__('Supporter leaderboard coming soon.', 'artpulse') . '</p>';
    }
}

TopSupportersWidget::register();
