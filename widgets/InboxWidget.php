<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

class InboxWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::get_id(),
            self::get_title(),
            'mail',
            __('Recent messages', 'artpulse'),
            [self::class, 'render'],
            [
                'roles' => ['artist', 'member'],
                'section' => self::get_section(),
            ]
        );
    }

    public static function get_id(): string { return 'inbox_widget'; }
    public static function get_title(): string { return __('Inbox', 'artpulse'); }
    public static function get_section(): string { return 'actions'; }

    public static function can_view(int $user_id): bool {
        return $user_id > 0;
    }

    public static function render(int $user_id): void {
        if (!self::can_view($user_id)) {
            echo '<p class="ap-widget-no-access">' . esc_html__('Please log in.', 'artpulse') . '</p>';
            return;
        }
        echo '<p>' . esc_html__('Message inbox coming soon.', 'artpulse') . '</p>';
    }
}

InboxWidget::register();
