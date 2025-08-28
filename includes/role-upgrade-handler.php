<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Admin\LayoutSnapshotManager;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Merge member widgets when a user upgrades roles.
 *
 * @param int    $user_id    User ID being updated.
 * @param string $new_role   The role being assigned.
 * @param array  $old_roles  Roles prior to the change.
 */
function ap_merge_dashboard_on_role_upgrade( int $user_id, string $new_role, array $old_roles = array() ): void {
	$supported_roles = array( 'member', 'artist', 'organization' );
	if ( ! in_array( $new_role, $supported_roles, true ) ) {
		return; // Ignore unrelated roles.
	}

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return;
	}
	$roles = array_values( array_intersect( $supported_roles, (array) $user->roles ) );
	if ( count( $roles ) <= 1 ) {
		return; // Nothing to merge if user has a single role.
	}

	$current = get_user_meta( $user_id, 'ap_dashboard_layout', true );
	if ( ! is_array( $current ) || empty( $current ) ) {
		$current = UserLayoutManager::get_role_layout( $roles[0] )['layout'];
	}

	$prior = $old_roles[0] ?? $roles[0] ?? '';
	LayoutSnapshotManager::snapshot( $user_id, $prior );

	$merged      = array();
	$seen_ids    = array();
	$all_widgets = DashboardWidgetRegistry::get_definitions();
	$valid_ids   = array_column( $all_widgets, 'id' );

	foreach ( $roles as $role ) {
		$layout = $role === $roles[0] ? $current : UserLayoutManager::get_role_layout( $role )['layout'];
		foreach ( $layout as $item ) {
			$id  = '';
			$vis = true;
			if ( is_array( $item ) && isset( $item['id'] ) ) {
				$id  = sanitize_key( $item['id'] );
				$vis = isset( $item['visible'] ) ? (bool) $item['visible'] : true;
			} elseif ( is_string( $item ) ) {
				$id = sanitize_key( $item );
			}
			if ( ! $id || isset( $seen_ids[ $id ] ) || ! in_array( $id, $valid_ids, true ) ) {
				continue;
			}
			$seen_ids[ $id ] = true;
			$merged[]        = array(
				'id'      => $id,
				'visible' => $vis,
			);
		}
	}

	update_user_meta( $user_id, 'ap_dashboard_layout', $merged );
	update_user_meta( $user_id, 'ap_role_upgrade_notice', 1 );
}
add_action( 'set_user_role', 'ap_merge_dashboard_on_role_upgrade', 10, 3 );
add_action( 'add_user_role', 'ap_merge_dashboard_on_role_upgrade', 10, 2 );

add_action(
	'admin_notices',
	function () {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$uid  = get_current_user_id();
		$show = get_user_meta( $uid, 'ap_role_upgrade_notice', true );
		if ( ! $show ) {
			return;
		}
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'New widgets added for your upgraded role', 'artpulse' ) . '</p></div>';
		delete_user_meta( $uid, 'ap_role_upgrade_notice' );
	}
);
