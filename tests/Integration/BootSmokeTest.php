<?php
namespace ArtPulse\Integration\Tests;

/**

 * @group INTEGRATION

 */

class BootSmokeTest extends \WP_UnitTestCase {
	public function test_bootstrap(): void {
		$this->assertGreaterThan( 0, did_action( 'muplugins_loaded' ) );
		$this->assertTrue( class_exists( '\\ArtPulse\\Core\\DashboardWidgetRegistry' ) );
	}
}
