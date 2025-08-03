<?php
namespace ArtPulse\Widgets\Member;

if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class WelcomeBoxWidget implements DashboardWidgetInterface {
    public static function id(): string { return 'welcome_box'; }

    public static function label(): string { return __('Welcome', 'artpulse'); }

    public static function roles(): array { return ['member']; }

    public static function description(): string { return __('Personal greeting for the signed-in user.', 'artpulse'); }

    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            'smiley',
            self::description(),
            [self::class, 'render'],
            [ 'roles' => self::roles(), 'category' => 'general' ]
        );
    }

    public static function render(int $user_id = 0): string {
        $user = wp_get_current_user();
        $name = $user->display_name ?: $user->user_login;
        $text = sprintf(esc_html__( 'Welcome back, %s!', 'artpulse' ), $name);
        return '<div data-widget-id="' . esc_attr(self::id()) . '"><p>' . esc_html($text) . '</p></div>';
    }
}

WelcomeBoxWidget::register();
