<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Frontend\EventListingShortcode;

if (!defined('ABSPATH')) { exit; }
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;

/**
 * Widget showing events near the current user.
 */
class NearMeEventsWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            'location',
            __('Events near your location.', 'artpulse'),
            [self::class, 'render'],
            [ 'roles' => self::roles() ]
        );
        // Load geolocation scripts/styles used by the event listing shortcode
        // so the "Events Near Me" button works in the dashboard context.
        add_action('admin_enqueue_scripts', [EventListingShortcode::class, 'enqueue']);
    }

    public static function id(): string { return 'widget_near_me_events'; }
    public static function label(): string { return __('Near Me Events', 'artpulse'); }
    public static function roles(): array { return ['member']; }

    public static function render(int $user_id = 0): string {
        // Render the existing event listing shortcode with a small page size.
        return do_shortcode('[ap_event_listing posts_per_page="5"]');
    }
}

NearMeEventsWidget::register();
