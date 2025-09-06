<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Dashboard\WidgetGuard;

add_action(
	'plugins_loaded',
	static function (): void {
		$ids = array(
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
			'empty_dashboard',
			'widget_placeholder',
		);

		foreach ( $ids as $id ) {
			$meta = array();
			if ( $id === 'widget_placeholder' ) {
				$meta = array( 'title' => 'Placeholder Widget' );
			} elseif ( $id === 'empty_dashboard' ) {
				$meta = array( 'title' => 'Dashboard Placeholder' );
			}
			WidgetGuard::register_stub_widget( $id, $meta );
		}
	},
	15
);
