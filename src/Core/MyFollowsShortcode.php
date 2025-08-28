<?php
namespace ArtPulse\Core;

class MyFollowsShortcode {
	public static function register() {
		ShortcodeRegistry::register( 'ap_my_follows', 'My Follows', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function enqueue() {
		wp_enqueue_script(
			'ap-my-follows-js',
			plugins_url( 'assets/js/ap-my-follows.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);
		wp_localize_script(
			'ap-my-follows-js',
			'ArtPulseFollowsApi',
			array(
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}
	}

	public static function render( $atts ) {
		// Output a simple container for JS to populate
		return '<div class="ap-my-follows"><div class="ap-directory-results"></div></div>';
	}
}
