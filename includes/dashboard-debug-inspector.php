<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Output debugging information for administrators when viewing wp-admin dashboard.
 */
add_action(
	'load-index.php',
	function () {
		if ( ! current_user_can( 'manage_options' ) || ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}
		$user_id = get_current_user_id();
		$role    = DashboardController::get_role( $user_id );
		$can     = current_user_can( 'view_artpulse_dashboard' );

		$defs    = DashboardWidgetRegistry::get_definitions();
		$visible = array();
		foreach ( $defs as $id => $def ) {
			if ( DashboardWidgetRegistry::user_can_see( $id, $user_id ) ) {
				$visible[] = $id;
			}
		}
		$list = implode( ', ', $visible );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log(
				sprintf(
					'ap dashboard inspector user=%d role=%s can_view=%s widgets=%s',
					$user_id,
					$role,
					$can ? 'yes' : 'no',
					$list
				)
			);
		}

		add_action(
			'admin_notices',
			function () use ( $role, $can, $list ) {
				echo '<div class="notice notice-info"><p>' . sprintf(
					esc_html__( 'Dashboard inspector – role: %1$s, view_cap: %2$s, widgets: %3$s', 'artpulse' ),
					esc_html( $role ),
					esc_html( $can ? 'yes' : 'no' ),
					esc_html( $list )
				) . '</p></div>';
			}
		);
	}
);
