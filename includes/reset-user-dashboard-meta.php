<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\DashboardController;

/**
 * Delete a user's saved dashboard layout and visibility so defaults load.
 */
function ap_reset_user_dashboard_meta( int $user_id ): bool {
	delete_user_meta( $user_id, 'ap_dashboard_layout' );
	delete_user_meta( $user_id, 'ap_widget_visibility' );
	// Optionally rebuild using default layout for role.
	DashboardController::reset_user_dashboard_layout( $user_id );
	return true;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	$handler = function ( $args ): void {
		$uid = (int) ( $args[0] ?? 0 );
		if ( ! $uid ) {
			\WP_CLI::error( 'User ID required.' );
		}
		ap_reset_user_dashboard_meta( $uid );
		\WP_CLI::success( "Reset dashboard layout for user {$uid}." );
	};

	// Original command name.
	\WP_CLI::add_command( 'ap reset-user-dashboard', $handler );
	// Short alias for convenience.
	\WP_CLI::add_command( 'ap reset-layout', $handler );
}
