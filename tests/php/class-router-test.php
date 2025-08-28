<?php
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class Router_Test extends TestCase {
	public function test_route_pattern() {
		$pattern = '#^/documents/([0-9]+)$#';

		$this->assertMatchesRegularExpression( $pattern, '/documents/123' );
		$this->assertDoesNotMatchRegularExpression( $pattern, '/documents/abc' );
	}
}
