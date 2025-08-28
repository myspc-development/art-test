<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ap_output_pwa_meta() {
	$manifest_url = plugins_url( 'manifest.json', ARTPULSE_PLUGIN_FILE );
	echo '<link rel="manifest" href="' . esc_url( $manifest_url ) . '">' . "\n";
	echo '<meta name="theme-color" content="#000000">' . "\n";
}
add_action( 'wp_head', 'ap_output_pwa_meta' );
