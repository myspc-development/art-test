<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Simple example widget that greets the logged in user.
 */
class SampleHelloWidget {
    /** Register the widget. */
    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::get_id(),
            self::get_title(),
            'admin-users',
            __('Greets the current user.', 'artpulse'),
            [self::class, 'render'],
            [ 'roles' => ['member', 'artist', 'organization'] ]
        );
    }

    /** Widget unique ID. */
    public static function get_id(): string {
        return 'sample_hello';
    }

    /** Widget title. */
    public static function get_title(): string {
        return __('Hello Widget', 'artpulse');
    }

    /** Determine if the widget can be viewed. */
    public static function can_view(): bool {
        return is_user_logged_in();
    }

    /** Render the widget output. */
    public static function render(): void {
        if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;

        if (!self::can_view()) {
            echo '<p class="ap-widget-no-access">' . esc_html__('You do not have access.', 'artpulse') . '</p>';
            return;
        }

        $user = wp_get_current_user();
        $name = $user->display_name ?: $user->user_login;
        echo '<div>Hello, ' . esc_html($name) . '!</div>';
    }
}

SampleHelloWidget::register();
