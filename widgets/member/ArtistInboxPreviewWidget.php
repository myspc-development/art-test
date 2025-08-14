<?php
namespace ArtPulse\Widgets\Member;

if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class ArtistInboxPreviewWidget implements DashboardWidgetInterface {
    public static function id(): string { return 'artist_inbox_preview'; }
    public static function label(): string { return 'Artist Inbox Preview'; }
    public static function roles(): array { return ['member', 'artist']; }
    public static function description(): string { return 'Recent unread messages from artists.'; }

    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            'inbox',
            self::description(),
            [self::class, 'render'],
            ['roles' => self::roles(), 'category' => 'engagement', 'capability' => 'can_receive_messages']
        );
    }

    public static function render(int $user_id = 0): string {
        ob_start();
        \ap_render_js_widget(self::id());
        return ob_get_clean();
    }
}

ArtistInboxPreviewWidget::register();
