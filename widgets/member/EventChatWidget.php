<?php
namespace ArtPulse\Widgets\Member;

if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class EventChatWidget implements DashboardWidgetInterface {
    public static function id(): string { return 'event_chat'; }
    public static function label(): string { return __('Event Chat', 'artpulse'); }
    public static function roles(): array { return ['member']; }
    public static function description(): string { return __('Chat with other attendees.', 'artpulse'); }

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
        return '<div data-widget-id="' . esc_attr(self::id()) . '">This will show event chat.</div>';
    }
}

EventChatWidget::register();
