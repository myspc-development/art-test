<?php
/**
 * Shortcodes for rendering common user dashboard widgets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Core\ShortcodeRegistry;
use ArtPulse\Admin\RolePresets;
use ArtPulse\Core\DashboardWidgetRegistry;

function ap_user_events_shortcode(): string {
	return do_shortcode( '[ap_widget id="my-events"]' );
}
ShortcodeRegistry::register( 'ap_user_events', 'User Events', 'ap_user_events_shortcode' );

function ap_user_follows_shortcode(): string {
	return do_shortcode( '[ap_widget id="my-follows"]' );
}
ShortcodeRegistry::register( 'ap_user_follows', 'User Follows', 'ap_user_follows_shortcode' );

function ap_user_analytics_shortcode(): string {
	return do_shortcode( '[ap_widget id="artpulse_analytics_widget"]' );
}
ShortcodeRegistry::register( 'ap_user_analytics', 'User Analytics', 'ap_user_analytics_shortcode' );

function user_dashboard_shortcode(): string {
	if ( ! is_user_logged_in() ) {
		return '';
	}
	$role = function_exists( 'get_query_var' ) ? sanitize_key( get_query_var( 'ap_role' ) ) : 'member';
	if ( ! in_array( $role, array( 'member', 'artist', 'organization' ), true ) ) {
		$role = 'member';
	}

	$slugs = RolePresets::get_preset_slugs( $role );

	ob_start();
	echo '<section class="ap-role-layout" role="tabpanel"'
		. ' data-role="' . esc_attr( $role ) . '"'
		. ' id="ap-panel-' . esc_attr( $role ) . '"'
		. ' aria-labelledby="ap-tab-' . esc_attr( $role ) . '">';

	foreach ( $slugs as $slug ) {
		echo DashboardWidgetRegistry::render( $slug, array( 'preview_role' => $role ) );
	}

	echo '</section>';
	return ob_get_clean();
}
ShortcodeRegistry::register( 'user_dashboard', 'User Dashboard', 'user_dashboard_shortcode' );
