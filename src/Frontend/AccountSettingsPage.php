<?php
namespace ArtPulse\Frontend;

class AccountSettingsPage {

	public static function register(): void {
			\ArtPulse\Core\ShortcodeRegistry::register( 'ap_account_settings', 'Account Settings', array( self::class, 'render' ) );
			add_action( 'wp', array( self::class, 'maybe_enqueue' ) );
	}

	public static function maybe_enqueue(): void {
		if ( function_exists( 'ap_page_has_shortcode' ) && ap_page_has_shortcode( 'ap_account_settings' ) ) {
				add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue' ) );
		}
	}

	public static function enqueue(): void {
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}
		wp_enqueue_script(
			'ap-account-settings-js',
			plugins_url( 'assets/js/ap-account-settings.js', ARTPULSE_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);
		wp_localize_script(
			'ap-account-settings-js',
			'APAccountSettings',
			array(
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'i18n'  => array( 'saved' => __( 'Settings saved.', 'artpulse' ) ),
			)
		);
	}

	public static function render(): string {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to manage your account.', 'artpulse' ) . '</p>';
		}
		$prefs            = get_user_meta( get_current_user_id(), 'ap_notification_prefs', true );
		$email            = ! is_array( $prefs ) || ! array_key_exists( 'email', $prefs ) || $prefs['email'];
		$push             = ! is_array( $prefs ) || ! array_key_exists( 'push', $prefs ) || $prefs['push'];
		$sms              = is_array( $prefs ) && isset( $prefs['sms'] ) ? (bool) $prefs['sms'] : false;
		$digest_frequency = get_user_meta( get_current_user_id(), 'ap_digest_frequency', true ) ?: 'none';
		$digest_topics    = get_user_meta( get_current_user_id(), 'ap_digest_topics', true );

		ob_start();
		$template = plugin_dir_path( __FILE__ ) . '../../templates/account-settings.php';
		if ( file_exists( $template ) ) {
			include $template;
		}
		return ob_get_clean();
	}
}
