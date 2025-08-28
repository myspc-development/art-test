<?php
/**
 * Output dashboard widget IDs for a given role.
 *
 * Usage (CLI): wp eval-file tools/dump-role-widgets.php member
 * Usage (HTTP): tools/dump-role-widgets.php?role=member
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;

$role = php_sapi_name() === 'cli'
	? sanitize_key( $argv[1] ?? 'member' )
	: sanitize_key( $_GET['role'] ?? 'member' );

$widgets = DashboardWidgetRegistry::get_widgets_by_role( $role );

if ( php_sapi_name() !== 'cli' ) {
	header( 'Content-Type: application/json' );
}

echo wp_json_encode( array_keys( $widgets ) );
