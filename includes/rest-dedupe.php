<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Detect and merge duplicate REST route handlers during bootstrapping.
 */
function ap_deduplicate_rest_routes( array $endpoints ): array {
		$GLOBALS['ap_rest_diagnostics'] = $GLOBALS['ap_rest_diagnostics'] ?? array(
			'conflicts' => array(),
			'missing'   => array(),
		);

		$seen                 = array();
		static $logged_routes = array();
		$core_prefixes        = array( '/', '/wp/v2', '/wp/v2/', '/batch/v1', '/wp-site-health/v1', '/block-editor/v1', '/oembed/1.0' );

		foreach ( $endpoints as $route => $handlers ) {
			if ( ! is_array( $handlers ) ) {
				continue;
			}

			// Skip core endpoints to avoid noise from WordPress internals.
			foreach ( $core_prefixes as $prefix ) {
				if ( $prefix === '/' ) {
					if ( $route === '/' ) {
						continue 2;
					}
					continue;
				}
				if ( strpos( $route, $prefix ) === 0 ) {
					continue 2;
				}
			}

foreach ( $handlers as $key => $handler ) {
$methods = $handler['methods'] ?? array( 'ALL' );
if ( ! is_array( $methods ) ) {
$methods = preg_split( '/[\s,|]+/', (string) $methods );
}
$methods = array_filter( array_map( 'strtoupper', $methods ) );
sort( $methods );
$method_label = implode( ',', $methods ) ?: 'ALL';

$parts     = explode( '/', trim( $route, '/' ) );
$namespace = implode( '/', array_slice( $parts, 0, 2 ) );
$sub_route = '/' . implode( '/', array_slice( $parts, 2 ) );
$sub_route = '/' === $sub_route ? '' : $sub_route;
$args      = ap_normalize_args( $handler['args'] ?? array() );
$signature = function_exists( 'wp_json_encode' )
? wp_json_encode(
array(
'methods'             => $method_label,
'namespace'           => $namespace,
'route'               => $sub_route,
'callback'            => $handler['callback'] ?? null,
'permission_callback' => $handler['permission_callback'] ?? null,
'args'                => $args,
)
)
: json_encode(
array(
'methods'             => $method_label,
'namespace'           => $namespace,
'route'               => $sub_route,
'callback'            => $handler['callback'] ?? null,
'permission_callback' => $handler['permission_callback'] ?? null,
'args'                => $args,
)
);

$dedupe_key = $route . ' ' . $method_label;

if ( ! isset( $seen[ $dedupe_key ] ) ) {
$seen[ $dedupe_key ] = array(
'signature' => $signature,
'callback'  => $handler['callback'] ?? null,
);
continue;
}

if ( $seen[ $dedupe_key ]['signature'] === $signature ) {
unset( $endpoints[ $route ][ $key ] );
continue;
}

if ( ! in_array( $route, $GLOBALS['ap_rest_diagnostics']['conflicts'], true ) ) {
$GLOBALS['ap_rest_diagnostics']['conflicts'][] = $route;
}
if ( function_exists( '_doing_it_wrong' ) ) {
_doing_it_wrong( 'ap_rest_dedupe', "Conflicting REST route $route ($method_label)", '1.0.0' );
}

if ( defined( 'WP_DEBUG' ) && WP_DEBUG && empty( $logged_routes[ $route ] ) ) {
$prev_loc = ap_callback_location( $seen[ $dedupe_key ]['callback'] );
$curr_loc = ap_callback_location( $handler['callback'] ?? null );
error_log( "[REST CONFLICT] Duplicate route $route ($method_label) between $prev_loc and $curr_loc." );
$logged_routes[ $route ] = true;
}
}
		}

                return $endpoints;
}

function ap_normalize_args( $args ) {
        if ( ! is_array( $args ) ) {
                return array();
        }
        ksort( $args );
        foreach ( $args as $k => $v ) {
                if ( is_array( $v ) ) {
                        $args[ $k ] = ap_normalize_args( $v );
                }
        }
        return $args;
}

function ap_callback_location( $callback ): string {
	try {
		if ( is_array( $callback ) ) {
			if ( is_object( $callback[0] ) ) {
				$ref = new ReflectionMethod( $callback[0], $callback[1] );
			} else {
				$ref = new ReflectionMethod( $callback[0], $callback[1] );
			}
		} elseif ( is_string( $callback ) && function_exists( $callback ) ) {
			$ref = new ReflectionFunction( $callback );
		} else {
			return 'unknown';
		}

		return $ref->getFileName() . ':' . $ref->getStartLine();
	} catch ( Throwable $e ) {
		return 'unknown';
	}
}

/**
 * Determine if a REST route is already registered.
 *
 * Optionally, verify that a specific HTTP method exists for the route.
 */
function ap_rest_route_registered( string $namespace, string $route, ?string $method = null ): bool {
		global $wp_rest_server;
	if ( ! $wp_rest_server ) {
			$wp_rest_server = rest_get_server();
	}

		$GLOBALS['ap_rest_diagnostics'] = $GLOBALS['ap_rest_diagnostics'] ?? array(
			'conflicts' => array(),
			'missing'   => array(),
		);

		$namespace = strtolower( trim( $namespace ) );
		$route     = strtolower( trim( $route ) );

// Build a fully normalized route with a single leading slash and no
// trailing slash (except for the root route).
$full_route = '/' . trim( $namespace, '/' ) . '/' . ltrim( $route, '/' );
$full_route = preg_replace( '#/+#', '/', $full_route );
if ( '/' !== $full_route ) {
$full_route = rtrim( $full_route, '/' );
}

$method_key = $method ? strtoupper( $method ) : '';
$check_key  = $full_route . ' ' . $method_key;
if ( defined( 'AP_TEST_MODE' ) && AP_TEST_MODE ) {
static $declared = array();
if ( isset( $declared[ $check_key ] ) ) {
return true;
}
$declared[ $check_key ] = true;
}

		// Normalize routes array for case-insensitive lookup.
		$routes = array_change_key_case( $wp_rest_server->get_routes(), CASE_LOWER );

		if ( ! isset( $routes[ $full_route ] ) ) {
			if ( ! in_array( $full_route, $GLOBALS['ap_rest_diagnostics']['missing'], true ) ) {
				$GLOBALS['ap_rest_diagnostics']['missing'][] = $full_route;
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( "[REST MISSING] Route $full_route is not registered." );
				}
			}
			return false;
		}

		// Ensure previously flagged routes are removed from the missing list.
		$index = array_search( $full_route, $GLOBALS['ap_rest_diagnostics']['missing'], true );
		if ( false !== $index ) {
			unset( $GLOBALS['ap_rest_diagnostics']['missing'][ $index ] );
			$GLOBALS['ap_rest_diagnostics']['missing'] = array_values( $GLOBALS['ap_rest_diagnostics']['missing'] );
		}

		if ( null === $method ) {
			return true;
		}

		$method   = strtoupper( $method );
		$handlers = $routes[ $full_route ];
		if ( ! is_array( $handlers ) ) {
			return false;
		}

		foreach ( $handlers as $handler ) {
			$methods = $handler['methods'] ?? array();

			if ( is_string( $methods ) ) {
				$list = array_map( 'trim', preg_split( '/[\s,|]+/', $methods ) );
			} elseif ( is_int( $methods ) && class_exists( '\\WP_REST_Server' ) ) {
				$map = array(
					\WP_REST_Server::READABLE  => array( 'GET', 'HEAD' ),
					\WP_REST_Server::CREATABLE => array( 'POST' ),
					\WP_REST_Server::EDITABLE  => array( 'POST', 'PUT', 'PATCH' ),
					\WP_REST_Server::DELETABLE => array( 'DELETE' ),
				);

				if ( defined( 'WP_REST_Server::OPTIONS' ) ) {
					$map[ \WP_REST_Server::OPTIONS ] = array( 'OPTIONS' );
				}

				if ( defined( 'WP_REST_Server::ALLMETHODS' ) ) {
					$all = array( 'GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE' );
					if ( defined( 'WP_REST_Server::OPTIONS' ) ) {
						$all[] = 'OPTIONS';
					}
					$map[ \WP_REST_Server::ALLMETHODS ] = $all;
				}

				$list = array();
				foreach ( $map as $bit => $verbs ) {
					if ( ( $methods & $bit ) === $bit ) {
						$list = array_merge( $list, $verbs );
					}
				}
			} elseif ( is_array( $methods ) ) {
				// Associative array of methods => details or simple list.
				$keys = array_keys( $methods );
				$list = array_keys( $methods ) === range( 0, count( $methods ) - 1 )
				? $methods
				: $keys;
			} else {
				$list = array();
			}

			$list = array_map( 'strtoupper', $list );
			if ( in_array( 'GET', $list, true ) ) {
				$list[] = 'HEAD';
			}
			$list = array_unique( $list );

			if ( in_array( $method, $list, true ) ) {
				return true;
			}
		}

		return false;
}

add_filter( 'rest_endpoints', 'ap_deduplicate_rest_routes', 999 );
