<?php
namespace ArtPulse\Integration\Tests {

	use ArtPulse\Integration\OAuthManager;

	/**

	 * @group INTEGRATION

	 */

	class OAuthManagerTest extends \WP_UnitTestCase {

		private array $hook_args = array();

		public function capture_hook( $user_id, $provider ): void {
			$this->hook_args = array( $user_id, $provider );
		}

		public function test_register_hooks_when_nextend_active(): void {
			OAuthManager::register();

			$this->assertSame(
				10,
				has_action( 'nsl_register_user', array( OAuthManager::class, 'store_token' ) )
			);
			$this->assertSame(
				10,
				has_action( 'nsl_login_successful', array( OAuthManager::class, 'store_token' ) )
			);
		}

		public function test_store_token_sanitizes_and_triggers_action(): void {
			$user_id = self::factory()->user->create();
			add_action( 'ap_oauth_login', array( $this, 'capture_hook' ), 10, 2 );

			$provider_data = array(
				'provider'     => ' Google ',
				'access_token' => ' <tok> ',
			);
			OAuthManager::store_token( $user_id, $provider_data );

			$meta_key = 'oauth_' . sanitize_key( $provider_data['provider'] ) . '_token';
			$expected = sanitize_text_field( $provider_data['access_token'] );

			$this->assertSame( $expected, get_user_meta( $user_id, $meta_key, true ) );
			$this->assertSame( array( $user_id, $provider_data['provider'] ), $this->hook_args );
		}

		public function test_render_buttons_outputs_enabled_shortcodes(): void {
			update_option(
				'artpulse_settings',
				array(
					'oauth_google_enabled'   => 1,
					'oauth_facebook_enabled' => 0,
					'oauth_apple_enabled'    => 1,
				)
			);

			$output   = OAuthManager::render_buttons();
			$expected = '[nextend_social_login provider="google"][nextend_social_login provider="apple"]';
			$this->assertSame( $expected, $output );
		}
	}
}
