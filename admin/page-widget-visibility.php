<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Support\OptionUtils;
use ArtPulse\Support\WidgetIds;

add_action(
	'admin_menu',
	function () {
		add_options_page(
			__( 'Widget Visibility', 'artpulse' ),
			__( 'Widget Visibility', 'artpulse' ),
			'manage_options',
			'ap-widget-visibility',
			'ap_render_widget_visibility_page'
		);
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'artpulse_widget_roles',
			'artpulse_widget_roles',
			array(
				'sanitize_callback' => 'ap_sanitize_widget_visibility_settings',
			)
		);
	}
);

function ap_sanitize_widget_visibility_settings( $input ) {
	$roles  = array( 'member', 'artist', 'organization' );
	$output = array();
	foreach ( (array) $input as $id => $config ) {
		$id      = WidgetIds::canonicalize( $id );
		$conf    = array();
		$allowed = array_map( 'sanitize_key', $config['roles'] ?? array() );
		$exclude = array_values( array_diff( $roles, $allowed ) );
		if ( $exclude ) {
			$conf['exclude_roles'] = $exclude;
		}
		if ( ! empty( $config['capability'] ) ) {
			$conf['capability'] = sanitize_text_field( $config['capability'] );
		}
		if ( $conf ) {
			$output[ $id ] = $conf;
		}
	}
	return $output;
}

function ap_render_widget_visibility_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Insufficient permissions', 'artpulse' ) );
	}
	$widgets  = DashboardWidgetRegistry::get_all();
	$settings = OptionUtils::get_array_option( 'artpulse_widget_roles' );
	$roles    = array( 'member', 'artist', 'organization' );
	echo '<div class="wrap"><h1>' . esc_html__( 'Widget Visibility', 'artpulse' ) . '</h1>';
	echo '<form method="post" action="options.php">';
	settings_fields( 'artpulse_widget_roles' );
	echo '<table class="widefat"><thead><tr><th>' . esc_html__( 'Widget', 'artpulse' ) . '</th>';
	echo '<th>' . esc_html__( 'Roles', 'artpulse' ) . '</th>';
	echo '<th>' . esc_html__( 'Capability', 'artpulse' ) . '</th></tr></thead><tbody>';
	foreach ( $widgets as $id => $def ) {
		$excluded   = $settings[ $id ]['exclude_roles'] ?? ( $def['exclude_roles'] ?? array() );
		$conf_roles = array_diff( $roles, (array) $excluded );
		$cap        = $settings[ $id ]['capability'] ?? ( $def['capability'] ?? '' );
		echo '<tr><td>' . esc_html( $def['label'] ) . '</td><td>';
		foreach ( $roles as $r ) {
			$checked = in_array( $r, (array) $conf_roles, true ) ? 'checked' : '';
			echo '<label style="margin-right:8px"><input type="checkbox" name="artpulse_widget_roles[' . esc_attr( $id ) . '][roles][]" value="' . esc_attr( $r ) . '" ' . $checked . ' /> ' . esc_html( ucfirst( $r ) ) . '</label>';
		}
		echo '</td><td><input type="text" name="artpulse_widget_roles[' . esc_attr( $id ) . '][capability]" value="' . esc_attr( $cap ) . '" /></td></tr>';
	}
	echo '</tbody></table>';
	submit_button();
	echo '</form></div>';
}
