<?php
namespace ArtPulse\Admin\Tests;

/**

 * @group ADMIN

 */

final class SettingsRoundTripTest extends \WP_UnitTestCase {
	public function test_round_trip_sanitizes_and_escapes(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$nonce = wp_create_nonce( 'ap_settings_save' );
		$_POST = array(
			'option_page'       => 'artpulse_settings_group',
			'action'            => 'update',
			'ap_nonce'          => $nonce,
			'artpulse_settings' => array(
				'admin_email'      => 'bad<script>@example.com',
				'enable_reporting' => 'on',
				'theme'            => '💣',
				'homepage_url'     => 'javascript:alert(1)',
			),
		);
		do_action( 'admin_post_artpulse_save_settings' ); // use your existing handler
		$opts = get_option( 'artpulse_settings' );
		$this->assertNotFalse( filter_var( $opts['admin_email'], FILTER_VALIDATE_EMAIL ) );
		$this->assertSame( 1, (int) $opts['enable_reporting'] );
		$this->assertContains( $opts['theme'], array( 'default', 'dark' ) );
		$this->assertTrue( empty( $opts['homepage_url'] ) || str_starts_with( (string) $opts['homepage_url'], 'http' ) );
		$html = $this->render_settings_page(); // reuse existing helper used in other tests
		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringNotContainsString( 'javascript:', $html );
	}

	private function render_settings_page(): string {
		\ob_start();
		\ArtPulse\Admin\SettingsPage::render();
		return \ob_get_clean();
	}
}
