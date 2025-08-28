<?php
namespace ArtPulse\Frontend;

class LogoutShortcode {

	public static function register(): void {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_logout', 'Logout', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_styles' ) );
	}

	public static function enqueue_styles(): void {
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}
	}

	public static function render( $atts = array() ): string {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$atts = shortcode_atts(
			array(
				'redirect' => home_url( '/' ),
			),
			$atts,
			'ap_logout'
		);

		$url = wp_logout_url( esc_url_raw( $atts['redirect'] ) );

		return '<a class="ap-logout-link" href="' . esc_url( $url ) . '">' . esc_html__( 'Log Out', 'artpulse' ) . '</a>';
	}
}
