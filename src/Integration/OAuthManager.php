<?php
namespace ArtPulse\Integration;

/**
 * Integrate third party OAuth providers for login.
 *
 * This implementation assumes the Nextend Social Login plugin is installed
 * which provides the [nextend_social_login] shortcode and several action hooks
 * exposing provider data.
 */
class OAuthManager {

	public static function register(): void {
		// Only register hooks if the Nextend Social Login plugin is present.
		if ( ! function_exists( 'nsl_init' ) ) {
			return;
		}
		// Hook into login/registration events to store tokens.
		add_action( 'nsl_register_user', array( self::class, 'store_token' ), 10, 2 );
		add_action( 'nsl_login_successful', array( self::class, 'store_token' ), 10, 2 );
	}

	/**
	 * Persist the OAuth access token on the user meta table.
	 *
	 * @param int   $user_id       The logged in user ID.
	 * @param array $provider_data Provider details from Nextend Social Login.
	 */
	public static function store_token( $user_id, $provider_data ): void {
		if ( ! is_array( $provider_data ) ) {
			return;
		}
		$provider = $provider_data['provider'] ?? '';
		$token    = $provider_data['access_token'] ?? '';
		if ( $provider && $token ) {
			update_user_meta( $user_id, 'oauth_' . sanitize_key( $provider ) . '_token', sanitize_text_field( $token ) );
			do_action( 'ap_oauth_login', $user_id, $provider );
		}
	}

	/**
	 * Output OAuth buttons for enabled providers.
	 */
	public static function render_buttons(): string {
		if ( ! function_exists( 'nsl_init' ) ) {
			return '';
		}
		$opts = get_option( 'artpulse_settings', array() );
		ob_start();
		if ( ! empty( $opts['oauth_google_enabled'] ) ) {
			echo do_shortcode( '[nextend_social_login provider="google"]' );
		}
		if ( ! empty( $opts['oauth_facebook_enabled'] ) ) {
			echo do_shortcode( '[nextend_social_login provider="facebook"]' );
		}
		if ( ! empty( $opts['oauth_apple_enabled'] ) ) {
			echo do_shortcode( '[nextend_social_login provider="apple"]' );
		}
		return trim( ob_get_clean() );
	}
}
