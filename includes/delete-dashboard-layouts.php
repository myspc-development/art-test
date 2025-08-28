<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Delete dashboard layout metadata for all users.
 */
function ap_delete_all_dashboard_layouts(): void {
	$user_ids = get_users( array( 'fields' => 'ID' ) );
	$count    = 0;

	foreach ( $user_ids as $uid ) {
		delete_user_meta( (int) $uid, 'ap_dashboard_layout' );
		++$count;
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		\WP_CLI::success( "Deleted dashboard layouts for {$count} users." );
	} else {
		echo "✅ Deleted dashboard layouts for {$count} users.\n";
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'ap delete-dashboard-layouts', 'ap_delete_all_dashboard_layouts' );
}
