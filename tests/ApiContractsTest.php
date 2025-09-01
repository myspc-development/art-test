<?php
/**
 * PHPUnit tests ensuring REST API routes follow basic contracts.
 */
/**
 * @group REST
 */
class ApiContractsTest extends WP_UnitTestCase {
	/** Ensure every route defines permission_callback. */
	public function test_routes_have_permission_callbacks() {
		$server = rest_get_server();
		foreach ( $server->get_routes() as $path => $handlers ) {
			foreach ( $handlers as $handler ) {
				$this->assertArrayHasKey( 'permission_callback', $handler, $path . ' missing permission_callback' );
			}
		}
	}

	/** Basic heuristic: routes with "list" in path should expose pagination args. */
	public function test_list_routes_have_pagination() {
		$server = rest_get_server();
		foreach ( $server->get_routes() as $path => $handlers ) {
			if ( false === strpos( $path, 'list' ) ) {
				continue;
			}
			foreach ( $handlers as $handler ) {
				$args = isset( $handler['args'] ) ? $handler['args'] : array();
				$this->assertArrayHasKey( 'page', $args, $path . ' missing page arg' );
				$this->assertArrayHasKey( 'per_page', $args, $path . ' missing per_page arg' );
			}
		}
	}
}
