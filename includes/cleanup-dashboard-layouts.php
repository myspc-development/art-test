<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardController;

/**
 * Remove unauthorized widgets from stored user dashboard layouts.
 */
function ap_clean_dashboard_layouts_by_role(): void {
	$users    = get_users( array( 'fields' => array( 'ID' ) ) );
	$registry = DashboardWidgetRegistry::get_all();
	$count    = 0;

	foreach ( $users as $user ) {
		$uid    = (int) $user->ID;
		$layout = get_user_meta( $uid, 'ap_dashboard_layout', true );
		if ( ! is_array( $layout ) ) {
			continue;
		}

		$role  = DashboardController::get_role( $uid );
		$valid = array_filter(
			$layout,
			static function ( $widget ) use ( $role, $registry ) {
				$id = sanitize_key( $widget['id'] ?? '' );
				if ( ! $id || ! isset( $registry[ $id ] ) ) {
					return false;
				}
				$roles = isset( $registry[ $id ]['roles'] ) ? (array) $registry[ $id ]['roles'] : array();
				return in_array( $role, $roles, true );
			}
		);
		$valid = array_values( $valid );

		if ( $valid !== $layout ) {
			update_user_meta( $uid, 'ap_dashboard_layout', $valid );
			++$count;
		}
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		\WP_CLI::success( "Cleaned dashboard layouts for {$count} users." );
	} else {
		echo "✅ Cleaned dashboard layouts for {$count} users.\n";
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'ap clean-dashboard-layouts', 'ap_clean_dashboard_layouts_by_role' );
}
