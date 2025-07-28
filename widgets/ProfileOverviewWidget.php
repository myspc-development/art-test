<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

class ProfileOverviewWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::get_id(),
            self::get_title(),
            'user',
            __('Quick stats about your profile', 'artpulse'),
            [self::class, 'render'],
            [
                'roles' => ['artist'],
                'section' => self::get_section(),
            ]
        );
    }

    public static function get_id(): string { return 'profile_overview'; }
    public static function get_title(): string { return __('Profile Overview', 'artpulse'); }
    public static function get_section(): string { return 'insights'; }

    public static function can_view(int $user_id): bool {
        return user_can($user_id, 'artist');
    }

    public static function render(int $user_id): void {
        if (!self::can_view($user_id)) {
            echo '<p class="ap-widget-no-access">' . esc_html__('You do not have access.', 'artpulse') . '</p>';
            return;
        }
        echo '<p>' . esc_html__('Profile statistics coming soon.', 'artpulse') . '</p>';
    }
}

ProfileOverviewWidget::register();
