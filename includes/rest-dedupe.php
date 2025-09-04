<?php
/**
 * REST route de-duplication:
 * - Drop exact duplicates (same route+method+callback).
 * - If multiple handlers register the same route+method with DIFFERENT callbacks,
 *   keep the LAST registered handler (so your real controller wins over stubs).
 * - When conflicts are detected, collect notices during tests; otherwise call
 *   _doing_it_wrong().
*/

if ( ! function_exists( 'ap__callback_id' ) ) {
	function ap__callback_id( $cb ): string {
		if ( is_string( $cb ) ) {
			return $cb;
		}
		if ( is_array( $cb ) ) {
			[$objOrClass, $method] = $cb + [null, null];
			if ( is_object( $objOrClass ) ) {
				return get_class( $objOrClass ) . '::' . (string) $method;
			}
			return (string) $objOrClass . '::' . (string) $method;
		}
		if ( $cb instanceof \Closure ) {
			$ref = new \ReflectionFunction( $cb );
			return 'closure@' . ($ref->getFileName() ?: 'unknown') . ':' . (string) $ref->getStartLine();
		}
		return 'callable:' . gettype( $cb );
	}
}

if ( ! function_exists( 'ap__normalize_method' ) ) {
        function ap__normalize_method( $methods ): string {
                if ( is_array( $methods ) ) {
                        $methods = array_map( 'ap__normalize_method', $methods );
                        $methods = array_unique( $methods );
                        sort( $methods );
                        return implode( ',', $methods );
                }
                $m = strtoupper( (string) $methods );
                return match ( $m ) {
                        'READABLE'   => 'GET',
                        'CREATABLE'  => 'POST',
                        'EDITABLE'   => 'PUT',
                        'DELETABLE'  => 'DELETE',
                        'ALLMETHODS' => '*',
                        default      => $m,
                };
        }
}

if ( ! function_exists( 'ap_rest_dedupe_endpoints' ) ) {
        function ap_rest_dedupe_endpoints( array $endpoints ): array {
		foreach ( $endpoints as $route => $handlers ) {
			if ( ! is_array( $handlers ) ) {
				continue;
			}

			// Build per-method map; iterate in registration order, **last wins**.
                        $by_method   = array();
                        $by_method_id = array(); // for conflict detection

			foreach ( $handlers as $h ) {
				if ( ! is_array( $h ) ) { continue; }
				$method = ap__normalize_method( $h['methods'] ?? '' );
				$id     = ap__callback_id( $h['callback'] ?? null );

                                // Track first seen id per method to detect conflicts.
                                if ( isset( $by_method_id[ $method ] ) && $by_method_id[ $method ] !== $id ) {
                                        $msg = sprintf( 'Conflicting REST route %1$s (%2$s)', $route, $method );
                                        if ( defined( 'WP_RUNNING_TESTS' ) && WP_RUNNING_TESTS ) {
                                                $GLOBALS['ap_rest_dedupe_notices'][] = $msg;
                                        } else {
                                                _doing_it_wrong( 'ap_rest_dedupe', $msg, '1.0.0' );
                                        }
                                } else {
                                        $by_method_id[ $method ] = $id;
                                }

				// Last-wins: overwrite handler for this method.
				$h['methods']        = $method; // normalize on the stored copy
				$by_method[ $method ] = $h;
			}

			// Remove exact duplicates (same method+callback) across handlers: already
			// achieved by the map; now rebuild the list in a stable order.
			$endpoints[ $route ] = array_values( $by_method );
		}

		return $endpoints;
	}
        add_filter( 'rest_endpoints', 'ap_rest_dedupe_endpoints', 99 );
}

if ( ! function_exists( 'ap_deduplicate_rest_routes' ) ) {
        function ap_deduplicate_rest_routes( array $routes ): array {
                return ap_rest_dedupe_endpoints( $routes );
        }
}
