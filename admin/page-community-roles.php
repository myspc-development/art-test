<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Community\CommunityRoles;

add_action(
	'admin_menu',
	function () {
		add_users_page(
			__( 'Community Roles', 'artpulse' ),
			__( 'Community Roles', 'artpulse' ),
			'manage_options',
			'ap-community-roles',
			'ap_render_community_roles_page'
		);
	}
);

function ap_render_community_roles_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Insufficient permissions', 'artpulse' ) );
	}

	if ( ! empty( $_POST['ap_set_role'] ) && check_admin_referer( 'ap_set_role' ) ) {
		$uid  = absint( $_POST['user_id'] );
		$role = sanitize_text_field( $_POST['community_role'] );
		update_user_meta( $uid, 'community_role', $role );
		echo '<div class="updated"><p>' . esc_html__( 'Role updated.', 'artpulse' ) . '</p></div>';
	}

	$users = get_users( array( 'number' => 50 ) );
	echo '<div class="wrap"><h1>' . esc_html__( 'Community Roles', 'artpulse' ) . '</h1>';
	echo '<table class="widefat"><thead><tr><th>' . esc_html__( 'User', 'artpulse' ) . '</th><th>' . esc_html__( 'Role', 'artpulse' ) . '</th></tr></thead><tbody>';
	foreach ( $users as $u ) {
		$current = CommunityRoles::get_role( $u->ID );
		echo '<tr><td>' . esc_html( $u->display_name ) . '</td><td>';
		echo '<form method="post">';
		wp_nonce_field( 'ap_set_role' );
		echo '<input type="hidden" name="user_id" value="' . esc_attr( $u->ID ) . '" />';
		echo '<select name="community_role">';
		foreach ( array(
			CommunityRoles::PUBLIC_USER     => __( 'Public User', 'artpulse' ),
			CommunityRoles::VERIFIED_ARTIST => __( 'Verified Artist', 'artpulse' ),
			CommunityRoles::MODERATOR       => __( 'Moderator', 'artpulse' ),
			CommunityRoles::ADMINISTRATOR   => __( 'Administrator', 'artpulse' ),
		) as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '"' . selected( $current, $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select> <button type="submit" name="ap_set_role" class="button">' . esc_html__( 'Save', 'artpulse' ) . '</button></form>';
		echo '</td></tr>';
	}
	echo '</tbody></table></div>';
}
