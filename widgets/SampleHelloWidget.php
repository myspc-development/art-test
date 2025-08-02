<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Simple example widget that greets the logged in user.
 */
class SampleHelloWidget implements DashboardWidgetInterface {
    /** Register the widget. */
    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            'admin-users',
            __('Greets the current user.', 'artpulse'),
            [self::class, 'render'],
            [ 'roles' => self::roles() ]
        );
    }

    /** Widget unique ID. */
    public static function id(): string {
        return 'sample_hello';
    }

    /** Widget title. */
    public static function label(): string {
        return __('Hello Widget', 'artpulse');
    }

    /** Roles allowed to view the widget. */
    public static function roles(): array {
        return ['member', 'artist', 'organization'];
    }

    /** Determine if the widget can be viewed. */
    public static function can_view(): bool {
        return is_user_logged_in();
    }

    /** Render the widget output. */
    public static function render(): string {
        if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return '';

        if (!self::can_view()) {
            $msg = '<p class="ap-widget-no-access">' . esc_html__('You do not have access.', 'artpulse') . '</p>';
            echo $msg;
            return $msg;
        }

        $user = wp_get_current_user();
        $name = $user->display_name ?: $user->user_login;
        $output = '<div>Hello, ' . esc_html($name) . '!</div>';
        echo $output;
        return $output;
    }
}

SampleHelloWidget::register();
