<?php
namespace ArtPulse\Admin\Tests;

use ArtPulse\Admin\SettingsPage;
use WP_UnitTestCase;

/**

 * @group ADMIN
 */

class SettingsPageSanitizeTest extends WP_UnitTestCase {

	public function test_checkbox_unchecked_saves_zero(): void {
		update_option( 'artpulse_settings', array( 'debug_logging' => 1 ) );
		$input  = array( 'debug_logging' => '0' );
		$result = SettingsPage::sanitizeSettings( $input );
		$this->assertSame( 0, $result['debug_logging'] );
	}

	public function test_checkbox_checked_saves_one(): void {
		update_option( 'artpulse_settings', array( 'debug_logging' => 0 ) );
		$input  = array( 'debug_logging' => '1' );
		$result = SettingsPage::sanitizeSettings( $input );
		$this->assertSame( 1, $result['debug_logging'] );
	}

	public function test_only_one_sanitizer_runs(): void {
		require_once __DIR__ . '/../../includes/settings-register.php';

		remove_all_filters( 'pre_update_option_artpulse_settings' );
		remove_all_filters( 'sanitize_option_artpulse_settings' );

		artpulse_register_settings();
		SettingsPage::registerSettings();

		$calls = 0;
		add_filter(
			'pre_update_option_artpulse_settings',
			function ( $value ) use ( &$calls ) {
				$calls++;
				return $value;
			}
		);

		update_option( 'artpulse_settings', array( 'debug_logging' => '1' ) );

		$this->assertSame( 1, $calls );
	}
}
