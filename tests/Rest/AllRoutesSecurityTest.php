<?php
namespace ArtPulse\Rest\Tests;

class AllRoutesSecurityTest extends \WP_UnitTestCase {
	private static array $mustValidate = array(
		'/ap/v1/roles',
		'/artpulse/v1/webhooks',
	);
        /**
         * @dataProvider routesProvider
         * @group REST
         * @group slow
         */
	public function test_route_security( string $route, string $method, array $args ): void {
		$server = rest_get_server();

		// 401: unauthenticated request
		$res = $server->dispatch( new \WP_REST_Request( $method, $route ) );
		$this->assertEquals( 401, $res->get_status(), "Unauthenticated access to $method $route should return 401" );

               // 403: authenticated without required capabilities.
               // Some read-only endpoints are accessible to subscribers.
               wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
               $res       = $server->dispatch( new \WP_REST_Request( $method, $route ) );
               $allowed   = array(
                       '/ap/v1/routes/audit',
                       '/ap/v1/routes/audit.json',
                       '/artpulse/v1/roles',
               );
               if ( in_array( $route, $allowed, true ) ) {
                       $this->assertSame( 200, $res->get_status(), "Subscriber access to $method $route should be allowed" );
               } else {
                       $this->assertSame( 403, $res->get_status(), "Subscriber access to $method $route should be 403" );
               }

		// 2xx: authenticated as administrator
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$req = new \WP_REST_Request( $method, $route );
		foreach ( $args as $k => $v ) {
			if ( $v !== null ) {
				$req->set_param( $k, $v );
			}
		}
		$res    = $server->dispatch( $req );
		$status = $res->get_status();
		$this->assertTrue( $status >= 200 && $status < 300, "Admin access to $method $route should be 2xx, got $status" );
		$schema = self::get_response_schema( $route, $method );

		if ( $schema ) {
			$validation = rest_validate_value_from_schema( $res->get_data(), $schema, 'response' );
			$this->assertNotWPError( $validation );
		} elseif ( in_array( $route, self::$mustValidate, true ) ) {
			$data = $res->get_data();
			$this->assertIsArray( $data );
			$this->assertNotEmpty( $data );
			$first = is_array( $data ) ? reset( $data ) : $data;
			$this->assertIsArray( $first );
			$this->assertArrayHasKey( 'id', $first );
		}
	}

	private static function normalize_methods( $m ): array {
		if ( is_array( $m ) ) {
			return array_values( $m );
		}
		$map = array(
			\WP_REST_Server::READABLE   => 'GET',
			\WP_REST_Server::CREATABLE  => 'POST',
			\WP_REST_Server::EDITABLE   => array( 'PUT', 'PATCH' ),
			\WP_REST_Server::DELETABLE  => 'DELETE',
			\WP_REST_Server::ALLMETHODS => array( 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ),
		);
		$out = array();
		foreach ( $map as $flag => $verb ) {
			if ( is_int( $m ) && ( $m & $flag ) ) {
				$out = array_merge( $out, (array) $verb );
			}
		}
		return $out ?: array( 'GET' );
	}

	private static function get_response_schema( string $route, string $method ): ?array {
		foreach ( rest_get_server()->get_routes()[ $route ] ?? array() as $handler ) {
			$methods = self::normalize_methods( $handler['methods'] ?? array() );
			if ( ! in_array( $method, $methods, true ) ) {
				continue;
			}
			if ( ! isset( $handler['schema'] ) ) {
				break;
			}
			$schema = $handler['schema'];
			if ( is_callable( $schema ) ) {
				$schema = call_user_func( $schema );
			}
			return is_array( $schema ) ? $schema : null;
		}
		return null;
	}

	public function routesProvider(): array {
		$out        = array();
		$namespaces = array( '/ap/v1', '/artpulse/v1' );
		foreach ( rest_get_server()->get_routes() as $route => $handlers ) {
			if ( ! array_filter( $namespaces, fn( $ns )=>strpos( $route, $ns ) === 0 ) ) {
				continue;
			}
			foreach ( $handlers as $h ) {
				foreach ( self::normalize_methods( $h['methods'] ?? array() ) as $method ) {
					$args  = array_map( fn( $a )=>$a['default'] ?? null, $h['args'] ?? array() );
					$out[] = array( $route, $method, $args );
				}
			}
		}
		return $out;
	}
}
