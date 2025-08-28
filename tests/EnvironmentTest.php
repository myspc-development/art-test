<?php
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase {
	public function test_wp_loaded() {
		$this->assertTrue( defined( 'ABSPATH' ), 'WordPress ABSPATH should be defined' );
		$this->assertTrue( function_exists( 'do_action' ), 'Core functions should be loaded' );
	}
}
