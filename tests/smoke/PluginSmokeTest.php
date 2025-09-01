<?php
declare(strict_types=1);

/**

 * @group smoke

 */

class PluginSmokeTest extends WP_UnitTestCase {
	public function test_plugin_loaded_and_shortcode_registered(): void {
		$this->assertTrue( defined( 'ARTPULSE_PLUGIN_FILE' ) );
		$this->assertTrue( shortcode_exists( 'ap_event_calendar' ) );
	}

	public function test_rsvp_route_registered(): void {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( '/ap/v1/rsvps', $routes );
	}
}
