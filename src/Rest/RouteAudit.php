<?php
namespace ArtPulse\Rest;

use WP_REST_Response;
use WP_Error;
use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

final class RouteAudit {
        use RestResponder;

        /**
         * Hook route registration into rest_api_init.
         */
        public static function register(): void {
                add_action( 'rest_api_init', array( self::class, 'routes' ) );
        }

        /**
         * Register the REST API routes for auditing.
         */
        public static function routes(): void {
                $controller = new self();

                register_rest_route(
                        'ap/v1',
                        '/routes/audit',
                        array(
                                'methods'             => WP_REST_Server::READABLE,
                               // Expose route list to any logged-in user.
                               'permission_callback' => Auth::require_login_and_cap( 'read' ),
                                'callback'            => array( $controller, 'get' ),
                        )
                );

                register_rest_route(
                        'ap/v1',
                        '/routes/audit.json',
                        array(
                                'methods'             => WP_REST_Server::READABLE,
                               // Expose route list to any logged-in user.
                               'permission_callback' => Auth::require_login_and_cap( 'read' ),
                                'callback'            => array( $controller, 'get_json' ),
                        )
                );
        }

        /**
         * Callback for the standard endpoint.
         */
        public function get(): WP_REST_Response|WP_Error {
                return $this->prepare_response( false );
        }

        /**
         * Callback for the JSON enforced endpoint.
         */
        public function get_json(): WP_REST_Response|WP_Error {
                return $this->prepare_response( true );
        }

        /**
         * Build the response data and optionally enforce JSON header.
         */
        private function prepare_response( bool $force_json ): WP_REST_Response {
                $response = $this->ok(
                        array(
                                'routes'    => self::collect_routes(),
                                'conflicts' => self::find_conflicts(),
                        )
                );
                if ( $force_json ) {
                        $response->set_headers( array( 'Content-Type' => 'application/json' ) );
                }
                return $response;
        }

	/**
	 * Gather route data from the REST server.
	 *
	 * @return array<int,array{path:string,methods:array,callback:string}>
	 */
	private static function collect_routes(): array {
		$server = rest_get_server();
		if ( ! $server ) {
			return array();
		}
                $routes = array();
                foreach ( $server->get_routes() as $path => $handlers ) {
                        foreach ( (array) $handlers as $endpoint ) {
                                $methods = $endpoint['methods'] ?? array();
                                if ( is_int( $methods ) ) {
                                        $methods = self::methods_from_mask( $methods );
                                } elseif ( is_string( $methods ) ) {
                                        $methods = array_map( 'trim', explode( ',', $methods ) );
                                }
                                $routes[] = array(
                                        'path'     => $path,
                                        'methods'  => array_map( 'strtoupper', (array) $methods ),
                                        'callback' => self::callback_to_string( $endpoint['callback'] ?? null ),
                                );
                        }
                }
                return $routes;
        }

	/**
	 * Convert a callback into a readable string.
	 */
	private static function callback_to_string( $callback ): string {
		if ( is_string( $callback ) ) {
			return $callback;
		}
		if ( is_array( $callback ) ) {
			$class = is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0];
			return $class . '::' . $callback[1];
		}
		if ( $callback instanceof \Closure ) {
			return 'closure';
		}
		if ( is_object( $callback ) && method_exists( $callback, '__invoke' ) ) {
			return get_class( $callback ) . '::__invoke';
		}
		return '(unknown)';
	}

	/**
	 * Returns first conflict found or an array of conflicts.
	 * Conflict = same route path AND overlapping HTTP method.
	 */
	public static function find_conflicts(): ?array {
		$server = rest_get_server();
		if ( ! $server ) {
			return null;
		}
		$routes    = $server->get_routes();
		$conflicts = array();

                foreach ( $routes as $path => $handlers ) {
                        // $handlers is array of endpoint arrays
                        // Build method signatures for this path
                        $methodSeen = array();
                        foreach ( (array) $handlers as $endpoint ) {
                                $methods = $endpoint['methods'] ?? array();
                                if ( is_int( $methods ) ) {
                                        $methods = self::methods_from_mask( $methods );
                                } elseif ( is_string( $methods ) ) {
                                        $methods = array_map( 'trim', explode( ',', $methods ) );
                                }
                                foreach ( (array) $methods as $m ) {
                                        $sig = strtoupper( $m );
                                        if ( isset( $methodSeen[ $sig ] ) ) {
                                                $conflicts[] = array(
                                                        'path'   => $path,
                                                        'method' => $sig,
                                                );
                                        } else {
                                                $methodSeen[ $sig ] = true;
                                        }
                                }
                        }
                }
                return $conflicts ? $conflicts : null;
        }

        /**
         * Convert WP_REST_Server method mask to array of HTTP methods.
         */
        private static function methods_from_mask( int $mask ): array {
                $map     = array(
                        WP_REST_Server::READABLE  => array( 'GET', 'HEAD' ),
                        WP_REST_Server::CREATABLE => array( 'POST' ),
                        WP_REST_Server::EDITABLE  => array( 'PUT', 'PATCH' ),
                        WP_REST_Server::DELETABLE => array( 'DELETE' ),
                );
                $methods = array();
                foreach ( $map as $bit => $verbs ) {
                        if ( $mask & $bit ) {
                                $methods = array_merge( $methods, $verbs );
                        }
                }
                return array_unique( $methods );
        }
}
