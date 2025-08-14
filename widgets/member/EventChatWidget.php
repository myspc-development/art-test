<?php
namespace ArtPulse\Widgets\Member;

if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class EventChatWidget implements DashboardWidgetInterface {
    public static function id(): string { return 'event_chat'; }
    public static function label(): string { return 'Event Chat'; }
    public static function roles(): array { return ['member']; }
    public static function description(): string { return 'Chat with other attendees.'; }

    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            'comments',
            self::description(),
            [self::class, 'render'],
            ['roles' => self::roles(), 'category' => 'events']
        );
    }

    public static function render(int $user_id = 0): string {
        ob_start();
        \ap_render_js_widget(self::id());
        return ob_get_clean();
    }
}

EventChatWidget::register();
