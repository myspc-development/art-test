<?php
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Dashboard\WidgetGuard;

add_action('plugins_loaded', static function (): void {
    $ids = [
        'artist_artwork_manager',
        'artist_audience_insights',
        'artist_earnings_summary',
        'artist_feed_publisher',
        'artist_spotlight',
        'audience_crm',
        'branding_settings_panel',
        'collab_requests',
        'embed_tool',
        'my_favorites',
        'nearby_events_map',
        'onboarding_tracker',
        'org_approval_center',
        'org_broadcast_box',
        'org_event_overview',
        'org_insights',
        'org_ticket_insights',
        'portfolio_preview',
        'revenue_summary',
        'rsvp_button',
        'share_this_event',
        'sponsored_event_config',
    ];

    foreach ($ids as $id) {
        WidgetGuard::register_stub_widget($id, []);
    }
}, 15);
