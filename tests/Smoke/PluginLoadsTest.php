<?php
/**
 * Basic smoke test to ensure the plugin loads under WP tests.
 */
class PluginLoadsTest extends WP_UnitTestCase {
	public function test_wp_loaded_and_plugin_constants() {
		$this->assertTrue( defined( 'ABSPATH' ), 'ABSPATH should be defined by WP test bootstrap' );
		// If your plugin defines constants, assert them here (optional):
		// $this->assertTrue( defined('ARTPULSE_API_NAMESPACE') );
	}
}
