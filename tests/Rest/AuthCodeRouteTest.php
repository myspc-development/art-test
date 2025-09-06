<?php
require_once __DIR__ . '/RouteTestCase.php';

class AuthCodeRouteTest extends RouteTestCase {
	/** @var array<string> */
	private array $routes = array();

	public function set_up(): void {
		parent::set_up();
		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init' );

		$source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/rest-auth-code.php' );
		if ( preg_match_all( '/register_rest_route\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]/', $source, $m, PREG_SET_ORDER ) ) {
			foreach ( $m as $match ) {
				$this->routes[] = '/' . $match[1] . $match[2];
			}
		}
	}

	public function tear_down(): void {
		global $wp_rest_server;
		$wp_rest_server = null;
		parent::tear_down();
	}

	public function test_routes_exist(): void {
		$registered = rest_get_server()->get_routes();
		foreach ( $this->routes as $path ) {
			$this->assertArrayHasKey( $path, $registered, "Route $path not registered" );
		}
		$this->assertTrue( true );
	}

	public function test_auth_code_filter(): void {
		$route = '/wp/v2/users';

		$response = $this->req( 'GET', $route );
		$this->assertSame( 401, $response->get_status() );

		$subscriber = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$response   = $this->req( 'GET', $route, array(), $subscriber );
		$this->assertSame( 403, $response->get_status() );
	}
}
