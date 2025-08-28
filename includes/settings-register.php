<?php
/**
 * Register global plugin settings.
 */
function artpulse_register_settings() {
	if ( ! did_action( 'artpulse_register_settings_done' ) ) {
		register_setting(
			'artpulse_settings_group',
			'artpulse_settings',
			array( 'sanitize_callback' => array( '\\ArtPulse\\Admin\\SettingsPage', 'sanitizeSettings' ) )
		);
		do_action( 'artpulse_register_settings_done' );
	}
}
add_action( 'admin_init', 'artpulse_register_settings' );

function artpulse_get_default_settings(): array {
	return array(
		'theme'                  => 'default',
		'enable_reporting'       => true,
		'admin_email'            => get_option( 'admin_email' ),
		'enable_wp_admin_access' => 0,
	);
}
