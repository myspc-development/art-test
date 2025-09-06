<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\DashboardWidgetRegistry;

add_action(
	'admin_menu',
	function () {
		add_management_page(
			__( 'Widget Audit', 'artpulse' ),
			__( 'Widget Audit', 'artpulse' ),
			'manage_options',
			'ap-widget-audit',
			'ap_render_widget_audit_page'
		);
	}
);

function ap_render_widget_audit_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Insufficient permissions', 'artpulse' ) );
	}
	$widgets = DashboardWidgetRegistry::get_all();
	echo '<div class="wrap"><h1>' . esc_html__( 'Widget Audit', 'artpulse' ) . '</h1>';
	echo '<table class="widefat"><thead><tr><th>' . esc_html__( 'ID', 'artpulse' ) . '</th><th>' . esc_html__( 'Label', 'artpulse' ) . '</th><th>' . esc_html__( 'Roles', 'artpulse' ) . '</th><th>' . esc_html__( 'Visibility', 'artpulse' ) . '</th></tr></thead><tbody>';
	foreach ( $widgets as $id => $def ) {
		$roles = isset( $def['roles'] ) ? implode( ', ', (array) $def['roles'] ) : '';
		$vis   = $def['visibility'] ?? '';
		echo '<tr>';
		echo '<td>' . esc_html( $id ) . '</td>';
		echo '<td>' . esc_html( $def['label'] ?? '' ) . '</td>';
		echo '<td>' . esc_html( $roles ) . '</td>';
		echo '<td>' . esc_html( $vis ) . '</td>';
		echo '</tr>';
	}
	echo '</tbody></table></div>';
}
