<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages front-end asset loading for the plugin.
 */
class ArtPulse_Assets {

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
	}

	/**
	 * Conditionally enqueue scripts and styles for public pages.
	 */
	public static function enqueue_assets(): void {
               if ( is_page( 'dashboard' ) || is_page_template( 'page-dashboard.php' ) ) {
                        \ArtPulse\Admin\EnqueueAssets::enqueue_style_if_exists( 'ap-dashboard', 'assets/css/dashboard.css' );
                        \ArtPulse\Admin\EnqueueAssets::enqueue_style_if_exists( 'ap-calendar', 'assets/css/calendar.css' );

                        \ArtPulse\Admin\EnqueueAssets::enqueue_script_if_exists( 'ap-user-dashboard', 'assets/js/ap-user-dashboard.js', array(), true, array( 'type' => 'module' ) );

			$user = wp_get_current_user();
			$boot = array(
				'restRoot'     => esc_url_raw( rest_url() ),
				'restNonce'    => wp_create_nonce( 'wp_rest' ),
				'currentUser'  => array(
					'id'          => $user->ID,
					'displayName' => $user->display_name,
					'roles'       => $user->roles,
				),
				'i18n'         => array(
					'Confirm' => __( 'Confirm', 'artpulse' ),
					'Cancel'  => __( 'Cancel', 'artpulse' ),
					'OK'      => __( 'OK', 'artpulse' ),
				),
				'routes'       => array(
					'overview'  => '#overview',
					'calendar'  => '#calendar',
					'favorites' => '#favorites',
					'my-rsvps'  => '#my-rsvps',
					'rsvps'     => '#rsvps',
					'events'    => '#events',
					'analytics' => '#analytics',
					'portfolio' => '#portfolio',
					'artworks'  => '#artworks',
					'settings'  => '#settings',
				),
				'featureFlags' => array(),
			);
			wp_localize_script( 'ap-user-dashboard', 'ARTPULSE_BOOT', $boot );

                        if ( function_exists( 'wp_set_script_translations' ) ) {
                                wp_set_script_translations( 'ap-user-dashboard', 'artpulse', plugin_dir_path( __DIR__ ) . 'languages' );
                        }
                }
        }
}
