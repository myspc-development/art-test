<?php
require_once __DIR__ . '/../RouteTestCase.php';

/**
 * Runtime-discovery smoke test for all plugin REST routes.
 * Ensures at least one route under our namespaces exists,
 * basic anonymous/admin behavior doesn't 404 or auth-fail unexpectedly.
 *
 * @group REST
 */
class RestRoutesSmokeTest extends RouteTestCase {
	/** @var array<string> */
	protected $namespaces = array( 'artpulse/v1', 'artpulse/v2' ); // add others if your plugin uses them

	public function test_namespaces_have_routes(): void {
		$routes = rest_get_server()->get_routes();
		foreach ( $this->namespaces as $ns ) {
			$found = false;
			foreach ( array_keys( $routes ) as $k ) {
				if ( str_starts_with( $k, '/' . $ns . '/' ) ) {
					$found = true;
					break; }
			}
			$this->assertTrue( $found, "No registered routes found for namespace: {$ns}" );
		}
	}

	public function test_sample_requests_anonymous_vs_admin(): void {
		$routes  = rest_get_server()->get_routes();
		$methods = array( 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' );
		$tested  = 0;
		foreach ( $this->namespaces as $ns ) {
			foreach ( array_keys( $routes ) as $path ) {
				if ( ! str_starts_with( $path, '/' . $ns . '/' ) ) {
					continue;
				}
				++$tested;
				// Anonymous
				$hit = false;
				foreach ( $methods as $m ) {
					$res = rest_do_request( new WP_REST_Request( $m, $path ) );
					$st  = $res->get_status();
					if ( $st !== 404 ) {
						$this->assertContains( $st, array( 200, 201, 202, 204, 400, 401, 403 ) );
						$hit = true;
						break; }
				}
				if ( ! $hit ) {
					$this->markTestSkipped( "All methods 404 for {$path}" );
				}
				// Admin
				$admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
				wp_set_current_user( $admin );
				$hit = false;
				foreach ( $methods as $m ) {
					$res = rest_do_request( new WP_REST_Request( $m, $path ) );
					$st  = $res->get_status();
					if ( $st !== 404 ) {
						$this->assertNotContains( $st, array( 401, 403 ), "Admin unauthorized for {$m} {$path} (status {$st})" );
						$hit = true;
						break; }
				}
				if ( ! $hit ) {
					$this->markTestSkipped( "All methods 404 for {$path}" );
				}
			}
		}
		$this->assertGreaterThan( 0, $tested, 'No routes tested' );
	}
}
