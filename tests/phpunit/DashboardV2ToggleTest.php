<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

if ( ! function_exists( 'ap_dashboard_v2_enabled' ) ) {
	require_once dirname( __DIR__, 2 ) . '/includes/helpers.php';
}

/**
 * @group PHPUNIT
 */
final class DashboardV2ToggleTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'get_option' )->alias( fn( $name, $default = array() ) => $default );
		$_GET     = array();
		$_SESSION = array();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_invalid_input_ignored(): void {
		$_GET['ap_v2'] = 'banana';

		$this->assertTrue( ap_dashboard_v2_enabled() );
		$this->assertArrayNotHasKey( 'ap_v2', $_SESSION );
	}
}
