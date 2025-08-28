<?php
namespace ArtPulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load text domain and set script translations.
 */
function ap_register_i18n(): void {
	load_plugin_textdomain( 'artpulse', false, basename( dirname( __DIR__ ) ) . '/languages' );

	if ( function_exists( 'wp_set_script_translations' ) ) {
		$lang_dir = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'languages';
		wp_set_script_translations( 'ap-payment-dashboard', 'artpulse', $lang_dir );
		wp_set_script_translations( 'ap-engagement-dashboard', 'artpulse', $lang_dir );
	}
}
add_action( 'init', __NAMESPACE__ . '\\ap_register_i18n' );
