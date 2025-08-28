<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * WP-CLI command to diagnose dashboard widget layout issues for a user.
 */
class AP_CLI_Dashboard_Diagnose {
	/**
	 * Handle the command.
	 *
	 * ## OPTIONS
	 *
	 * <user_id>
	 * : The ID of the user to inspect.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$user_id = (int) ( $args[0] ?? 0 );
		if ( ! $user_id ) {
			\WP_CLI::error( 'User ID required.' );
		}

		$user = get_user_by( 'ID', $user_id );
		if ( ! $user ) {
			\WP_CLI::error( "User {$user_id} not found." );
		}

		$roles = (array) $user->roles;
		\WP_CLI::line( 'User Roles: ' . implode( ', ', $roles ) );

		$layout = get_user_meta( $user_id, '_ap_dashboard_layout', true );
		if ( ! is_array( $layout ) ) {
			$layout = array();
		}

		$layout_ids = array();
		foreach ( $layout as $item ) {
			if ( is_array( $item ) && isset( $item['id'] ) ) {
				$layout_ids[] = sanitize_key( $item['id'] );
			} elseif ( is_string( $item ) ) {
				$layout_ids[] = sanitize_key( $item );
			}
		}

		$available_widgets = ap_get_all_widget_definitions();
		$registered        = array();
		foreach ( $available_widgets as $id => $def ) {
			$widget_roles = isset( $def['roles'] ) ? (array) $def['roles'] : array();
			if ( ! $widget_roles || array_intersect( $widget_roles, $roles ) ) {
				$registered[ $id ] = true;
			}
		}

		$registered_ids = array_keys( $registered );

		$orphans = array_diff( $layout_ids, $registered_ids );
		$missing = array_diff( $registered_ids, $layout_ids );

		\WP_CLI::line( 'Orphan widgets (in layout but not registered):' );
		if ( $orphans ) {
			foreach ( $orphans as $id ) {
				\WP_CLI::line( "❌ {$id}" );
			}
		} else {
			\WP_CLI::line( '✅ None' );
		}

		\WP_CLI::line( 'Missing widgets (expected by role but not in layout):' );
		if ( $missing ) {
			foreach ( $missing as $id ) {
				\WP_CLI::line( "❌ {$id}" );
			}
		} else {
			\WP_CLI::line( '✅ None' );
		}

		if ( $orphans || $missing ) {
			\WP_CLI::warning( "Layout mismatch detected. Suggest running: wp ap:reset-dashboard-layout {$user_id}" );
		} else {
			\WP_CLI::success( 'Dashboard layout looks good.' );
		}
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'ap:diagnose-dashboard', 'AP_CLI_Dashboard_Diagnose' );
}
