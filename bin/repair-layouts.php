#!/usr/bin/env php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use ArtPulse\Core\DashboardController;

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command(
		'ap:repair_layouts',
		function () {
			$users    = get_users( array( 'fields' => array( 'ID' ) ) );
			$repaired = 0;
			foreach ( $users as $user ) {
				if ( DashboardController::reset_user_dashboard_layout( (int) $user->ID ) ) {
					$repaired++;
				}
			}
			\WP_CLI::success( "Repaired dashboard layouts for {$repaired} users." );
		}
	);
} else {
	fwrite( STDERR, "This script must be run within WP-CLI.\n" );
	exit( 1 );
}
