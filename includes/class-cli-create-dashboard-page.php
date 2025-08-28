<?php
namespace ArtPulse\CLI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_CLI;

/**
 * WP-CLI command to ensure the user dashboard page exists.
 */
class CreateUserDashboardPageCommand {
	public function __invoke( array $args, array $assoc_args ): void {
		$id = \ap_ensure_user_dashboard_page();
		if ( $id ) {
			WP_CLI::success( "Dashboard page exists with ID {$id}." );
		} else {
			WP_CLI::error( 'Failed to create dashboard page.' );
		}
	}
}
