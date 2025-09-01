<?php
/**
 * Smoke tests ensuring the plugin loads within the WordPress test environment.
 *
 * @package ArtPulse
 * @subpackage Smoke
 */
class PluginLoadsTest extends WP_UnitTestCase {
	/**
	 * Verify WordPress bootstrap and plugin constants exist.
	 */
	public function test_wp_loaded_and_plugin_constants() {
		$this->assertTrue( defined( 'ABSPATH' ), 'ABSPATH should be defined by WP test bootstrap' );
		$this->assertTrue(
			defined( 'ARTPULSE_PLUGIN_FILE' ),
			'ARTPULSE_PLUGIN_FILE should be defined'
		);
	}
}
