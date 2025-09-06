<?php
declare(strict_types=1);

namespace ArtPulse\Rest\Tests;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Smoke test that scans all /artpulse/v1 routes and exercises each method.
 * - Ensures the route is registered (not 404)
 * - Exercises with admin + nonces to avoid capability noise
 * - Fills basic required args when possible
 * - Verifies return type is WP_REST_Response (not raw array)
 * - Aggregates all issues so we get a full inventory in one run
 *
 * @group REST
 */
class RouteAuditSmokeTest extends \WP_UnitTestCase {

	private int $admin_id;

	public function set_up(): void {
		parent::set_up();

		if ( ! defined( 'AP_TEST_MODE' ) ) {
			// Let controllers relax guards under test, if they support it
			define( 'AP_TEST_MODE', true );
		}

		// Admin context to bypass most caps
		$this->admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->admin_id );

		// Add any custom caps your routes might check
		if ( $role = get_role( 'administrator' ) ) {
			foreach ( array( 'view_analytics', 'read', 'edit_posts', 'manage_options' ) as $cap ) {
				$role->add_cap( $cap );
			}
		}

		// Ensure routes are (re)registered
		do_action( 'rest_api_init' );
	}

	public function test_all_artpulse_routes_smoke(): void {
		$server = rest_get_server();
		$this->assertNotNull( $server, 'REST server not initialized.' );
		$routes = $server->get_routes();

		$namespacePrefix = '/artpulse/v1/';
		$problems        = array();

		// Collect all artpulse routes
		$targets = array();
		foreach ( $routes as $route => $handlers ) {
			if ( strpos( $route, $namespacePrefix ) === 0 ) {
				$targets[ $route ] = $handlers;
			}
		}

		$this->assertNotEmpty(
			$targets,
			'No /artpulse/v1 routes found. Verify controllers call register() and rest_api_init ran.'
		);

		$wpNonce = function_exists( 'wp_create_nonce' ) ? wp_create_nonce( 'wp_rest' ) : 'test';
		$apNonce = function_exists( 'wp_create_nonce' ) ? wp_create_nonce( 'ap_dashboard_config' ) : 'test';
		$skipped = array();

		foreach ( $targets as $route => $handlers ) {
			// Each "handler" is an endpoint variant (methods, args, permission_callback, etc.)
			foreach ( $handlers as $idx => $handler ) {
				$methods = $this->expand_methods( $handler['methods'] ?? 'GET' );

				foreach ( $methods as $method ) {
					$concreteRoute = $this->concretize_route( $route );
					if ( $concreteRoute === null ) {
						$skipped[] = sprintf( '%s [%s] (var #%d)', $route, $method, $idx );
						continue;
					}

					$req = new WP_REST_Request( $method, $concreteRoute );
					$req->set_header( 'X-WP-Nonce', $wpNonce );
					$req->set_header( 'X-AP-Nonce', $apNonce );
					$req->set_param( 'context', 'view' );

					// Try to satisfy required args with innocuous defaults
					if ( ! empty( $handler['args'] ) && is_array( $handler['args'] ) ) {
						foreach ( $handler['args'] as $argName => $schema ) {
							if ( ! empty( $schema['required'] ) && ! $req->get_param( $argName ) ) {
								$req->set_param( $argName, $this->default_for_schema( $schema ) );
							}
						}
					}

					// Some POST/PUT/PATCH endpoints only read JSON body
					if ( in_array( $method, array( 'POST', 'PUT', 'PATCH' ), true ) ) {
						$json = array();
						if ( ! empty( $handler['args'] ) && is_array( $handler['args'] ) ) {
							foreach ( $handler['args'] as $argName => $schema ) {
								$json[ $argName ] = $this->default_for_schema( $schema );
							}
						}
						if ( $json ) {
							$req->set_body( wp_json_encode( $json ) );
						}
					}

					$res = $server->dispatch( $req );

					// Normalize status/type for comparison + diagnostics
					$status = null;
					$type   = null;

					if ( $res instanceof WP_REST_Response ) {
						$status = $res->get_status();
						$type   = 'WP_REST_Response';
					} elseif ( $res instanceof WP_Error ) {
						$data   = $res->get_error_data();
						$status = is_array( $data ) && isset( $data['status'] ) ? (int) $data['status'] : 500;
						$type   = 'WP_Error';
					} elseif ( is_array( $res ) || is_object( $res ) ) {
						$status = 200; // may be treated as success but type is wrong
						$type   = gettype( $res );
					} else {
						$status = 500;
						$type   = gettype( $res );
					}

					// Flag anything not ideal
					$prefix = sprintf( '%s [%s] (var #%d)', $route, $method, $idx );

					// 1) Route missing
					if ( $status === 404 ) {
						$problems[] = "$prefix → 404 Not Found";
						continue;
					}

					// 2) Permission/nonce problems (we’re admin + nonces; should pass unless intentionally locked)
					if ( in_array( $status, array( 401, 403 ), true ) ) {
						$problems[] = "$prefix → $status (auth/permission block)";
					}

					// 3) Wrong return type (your controllers should return WP_REST_Response|WP_Error)
					if ( ! $res instanceof WP_REST_Response && ! $res instanceof WP_Error ) {
						$problems[] = "$prefix → bad return type: $type (expect WP_REST_Response or WP_Error)";
					}

					// 4) 5xx
					if ( $status >= 500 ) {
						$problems[] = "$prefix → $status server error";
					}
				}
			}
		}

		if ( $skipped ) {
			fwrite( STDOUT, "Skipped dynamic routes (unresolved placeholders):\n- " . implode( "\n- ", $skipped ) . "\n" );
		}

		// Make the test fail with a readable inventory of issues
		$this->assertEmpty(
			$problems,
			"REST route audit discovered problems:\n- " . implode( "\n- ", $problems )
		);
	}

	/** Expand WP REST method spec to a simple list: 'GET|POST' -> ['GET','POST'] */
	private function expand_methods( $spec ): array {
		if ( is_string( $spec ) ) {
			// WordPress accepts 'GET', 'POST', or bitmask constants; in routes it’s usually a string pipe list
			if ( strpos( $spec, '|' ) !== false ) {
				return array_map( 'trim', explode( '|', $spec ) );
			}
			if ( strpos( $spec, ',' ) !== false ) {
				return array_map( 'trim', explode( ',', $spec ) );
			}
			return array( $spec );
		}
		if ( is_array( $spec ) ) {
			return array_values( array_unique( array_map( 'strval', $spec ) ) );
		}
		if ( is_int( $spec ) ) {
			$map   = array(
				WP_REST_Server::READABLE  => array( 'GET', 'HEAD' ),
				WP_REST_Server::CREATABLE => array( 'POST' ),
				WP_REST_Server::EDITABLE  => array( 'PUT', 'PATCH' ),
				WP_REST_Server::DELETABLE => array( 'DELETE' ),
			);
			$verbs = array();
			foreach ( $map as $mask => $methods ) {
				if ( ( $spec & $mask ) === $mask ) {
					$verbs = array_merge( $verbs, $methods );
				}
			}
			if ( $verbs ) {
				return array_values( array_unique( $verbs ) );
			}
		}
		return array( 'GET' );
	}

	/**
	 * Replace named regex groups in a route with safe sample values.
	 * Returns the concrete route or null if placeholders remain.
	 */
	private function concretize_route( string $route ): ?string {
		$concrete = preg_replace_callback(
			'/\(\?P<[^>]+>[^)]+\)/',
			function ( array $matches ) {
				$segment = $matches[0];
				if ( preg_match( '/\(\?P<[^>]+>([^)]+)\)/', $segment, $m ) ) {
					$pattern = $m[1];
					if ( preg_match( '/\\d|\[0-9\]/', $pattern ) ) {
						return '1';
					}
				}
				return 'test';
			},
			$route
		);

		if ( preg_match( '/[\[\]\(\)\\?*+|^$]/', $concrete ) ) {
			return null;
		}

		return $concrete;
	}

	/** Provide a safe default for required arg schema to let routes execute */
	private function default_for_schema( array $schema ) {
		$type = $schema['type'] ?? null;
		switch ( $type ) {
			case 'integer':
				return 1;
			case 'number':
				return 1;
			case 'boolean':
				return true;
			case 'array':
				return array();
			case 'object':
				return (object) array();
			case 'string':
			default:
				return 'test';
		}
	}
}
