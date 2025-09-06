<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

add_action(
	'admin_menu',
	function () {
		add_menu_page(
			__( 'Roles Editor', 'artpulse' ),
			__( 'Roles', 'artpulse' ),
			'manage_options',
			'artpulse_roles',
			'ap_render_roles_editor_page'
		);
	}
);

function ap_render_roles_editor_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Insufficient permissions', 'artpulse' ) );
	}
	echo '<div class="wrap"><h1>' . esc_html__( 'Roles Editor', 'artpulse' ) . '</h1>';
	echo '<div id="artpulse-roles-root"></div></div>';
}
