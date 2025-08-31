<?php
/**
 * REST route de-duplication:
 * - Drop exact duplicates (same route+method+callback).
 * - If multiple handlers register the same route+method with DIFFERENT callbacks,
 *   keep the LAST registered handler (so your real controller wins over stubs).
 * - Only warn on true conflicts outside tests; suppress in AP_TEST_MODE.
 */

if ( ! function_exists( 'ap__is_test_mode' ) ) {
	function ap__is_test_mode(): bool {
		if ( defined( 'AP_TEST_MODE' ) && AP_TEST_MODE ) {
			return true;
		}
		$env = getenv( 'AP_TEST_MODE' );
		return $env !== false && $env !== '' && $env !== '0';
	}
}

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
			$by_method   = [];
			$by_method_id = []; // for conflict detection

			foreach ( $handlers as $h ) {
				if ( ! is_array( $h ) ) { continue; }
				$method = ap__normalize_method( $h['methods'] ?? '' );
				$id     = ap__callback_id( $h['callback'] ?? null );

				// Track first seen id per method to detect conflicts
				if ( isset( $by_method_id[ $method ] ) && $by_method_id[ $method ] !== $id ) {
					$should_warn = apply_filters(
						'ap_rest_dedupe_warn',
						! ap__is_test_mode(), // suppress in tests by default
						$route,
						$method,
						$by_method_id[ $method ],
						$id
					);
					if ( $should_warn ) {
						_doing_it_wrong(
							'ap_rest_dedupe',
							sprintf( 'Conflicting REST route %s (%s)', $route, $method ),
							'1.0.0'
						);
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
