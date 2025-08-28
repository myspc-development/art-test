<?php
/**
 * Simple utility to render all dashboard widgets for a role.
 *
 * Usage: include this file within a WordPress request and pass
 * ?role=member (or artist/organization) to view all widgets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;

$role    = sanitize_key( $_GET['role'] ?? 'member' );
$widgets = DashboardWidgetRegistry::get_widgets( $role );

foreach ( $widgets as $widget_id => $widget ) {
	echo do_shortcode( '[ap_widget id="' . esc_attr( $widget_id ) . '"]' );
}
