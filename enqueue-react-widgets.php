<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function artpulse_enqueue_widget_scripts(): void {
	if ( ! is_singular() ) {
		return;
	}

	global $post;
	$post = get_post();
	if ( ! $post ) {
		return;
	}

	$shortcodes     = array( 'ap_widget', 'ap_user_dashboard', 'ap_react_dashboard', 'user_dashboard' );
	$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );

	$has_widget = false;
	foreach ( array( $post->post_content, is_string( $elementor_data ) ? $elementor_data : '' ) as $content ) {
		foreach ( $shortcodes as $shortcode ) {
			if ( has_shortcode( $content, $shortcode ) || strpos( $content, '[' . $shortcode ) !== false ) {
				$has_widget = true;
				break 2;
			}
		}
	}
	if ( ! $has_widget ) {
		return;
	}

		$handle      = 'art-widgets';
		$script_rel  = 'assets/dist/index.js';
		$script_path = plugin_dir_path( __FILE__ ) . $script_rel;
	if ( file_exists( $script_path ) ) {
			wp_enqueue_script(
				$handle,
				plugins_url( $script_rel, __FILE__ ),
				array( 'wp-element', 'wp-api-fetch' ),
				(string) filemtime( $script_path ),
				true
			);
			wp_script_add_data( $handle, 'type', 'module' );

			wp_localize_script(
				$handle,
				'APChat',
				array(
					'apiRoot'  => esc_url_raw( rest_url() ),
					'nonce'    => wp_create_nonce( 'wp_rest' ),
					'loggedIn' => is_user_logged_in(),
				)
			);
	}
}

add_action( 'wp_enqueue_scripts', 'artpulse_enqueue_widget_scripts' );
