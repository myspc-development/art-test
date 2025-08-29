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

        $seen                = array();
        static $logged_routes = array();
        $core_prefixes = array( '/', '/wp/v2', '/wp/v2/', '/batch/v1', '/wp-site-health/v1', '/block-editor/v1', '/oembed/1.0' );

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
			$dedupe_key   = $route . ' ' . $method_label;

                        if ( ! isset( $seen[ $dedupe_key ] ) ) {
                                $seen[ $dedupe_key ] = array(
                                        'callback'            => $handler['callback'] ?? null,
                                        'permission_callback' => $handler['permission_callback'] ?? null,
                                );
                                continue;
                        }

			$prev            = $seen[ $dedupe_key ];
			$same_callback   = isset( $handler['callback'] ) && $prev['callback'] === $handler['callback'];
			$same_permission = $prev['permission_callback'] === ( $handler['permission_callback'] ?? null );

			if ( $same_callback && $same_permission ) {
				// Identical handler already registered - remove duplicate.
				unset( $endpoints[ $route ][ $key ] );
				continue;
			}

                        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && empty( $logged_routes[ $route ] ) ) {
                                $prev_loc = ap_callback_location( $prev['callback'] );
                                $curr_loc = ap_callback_location( $handler['callback'] ?? null );
                                error_log( "[REST CONFLICT] Duplicate route $route ($method_label) between $prev_loc and $curr_loc." );
                                $logged_routes[ $route ] = true;
                                if ( ! in_array( $route, $GLOBALS['ap_rest_diagnostics']['conflicts'], true ) ) {
                                        $GLOBALS['ap_rest_diagnostics']['conflicts'][] = $route;
                                }
                        }
                }
        }

        return $endpoints;
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

        $namespace  = trim( $namespace, '/' );
        $route      = '/' . ltrim( $route, '/' );
        $full_route = '/' . $namespace . $route;
        $full_route = rtrim( $full_route, '/' );
        $routes     = $wp_rest_server->get_routes();

        if ( ! isset( $routes[ $full_route ] ) ) {
                if ( ! in_array( $full_route, $GLOBALS['ap_rest_diagnostics']['missing'], true ) ) {
                        $GLOBALS['ap_rest_diagnostics']['missing'][] = $full_route;
                        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                                error_log( "[REST MISSING] Route $full_route is not registered." );
                        }
                }
                return false;
        }

	if ( $method === null ) {
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
			$list = array_map( 'trim', explode( '|', $methods ) );
		} elseif ( is_int( $methods ) && class_exists( '\\WP_REST_Server' ) ) {
			$map  = array(
				\WP_REST_Server::READABLE  => array( 'GET', 'HEAD' ),
				\WP_REST_Server::CREATABLE => array( 'POST' ),
				\WP_REST_Server::EDITABLE  => array( 'PUT', 'PATCH' ),
				\WP_REST_Server::DELETABLE => array( 'DELETE' ),
			);
			$list = array();
			foreach ( $map as $bit => $verbs ) {
				if ( $methods & $bit ) {
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

		foreach ( $list as $verb ) {
			if ( strtoupper( $verb ) === $method ) {
				return true;
			}
		}
	}

	return false;
}

add_filter( 'rest_endpoints', 'ap_deduplicate_rest_routes', 999 );
