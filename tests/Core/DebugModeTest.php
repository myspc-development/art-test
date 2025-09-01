<?php
use PHPUnit\Framework\TestCase;

/**

 * @group core

 */

class DebugModeTest extends TestCase {
	public function test_debug_constants() {
		$this->assertTrue( defined( 'WP_DEBUG' ) && WP_DEBUG, 'WP_DEBUG not enabled' );
		$this->assertTrue( defined( 'WP_DEBUG_LOG' ), 'WP_DEBUG_LOG not defined' );
	}
}
