<?php
/**
 * REST route de-duplication:
 * - Silently drop exact duplicates (same route+method+callback).
 * - Warn only when the same route+method registers different callbacks.
 * - Suppress warnings in test mode (env/AP_TEST_MODE) unless explicitly re-enabled via filter.
 */

if ( ! function_exists( 'ap__is_test_mode' ) ) {
	function ap__is_test_mode(): bool {
		// Accept either a defined constant or an env var.
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

if ( ! function_exists( 'ap_rest_dedupe_endpoints' ) ) {
	function ap_rest_dedupe_endpoints( array $endpoints ): array {
		foreach ( $endpoints as $route => &$handlers ) {
			if ( ! is_array( $handlers ) ) {
				continue;
			}

			// Normalize methods (map READABLE -> GET, CREATABLE -> POST, etc.)
			$normalize_method = static function( $methods ): string {
				$m = strtoupper( (string) $methods );
				if ( $m === 'READABLE' ) return 'GET';
				if ( $m === 'CREATABLE' ) return 'POST';
				if ( $m === 'EDITABLE' ) return 'PUT';
				if ( $m === 'DELETABLE' ) return 'DELETE';
				if ( $m === 'ALLMETHODS' ) return '*';
				return $m;
			};

			// 1) Drop exact duplicates (same method + same callback)
			$seen = [];
			foreach ( $handlers as $idx => $h ) {
				if ( ! is_array( $h ) ) continue;
				$m  = $normalize_method( $h['methods'] ?? '' );
				$id = ap__callback_id( $h['callback'] ?? null );
				$key = $m . '|' . $id;

				if ( isset( $seen[ $key ] ) ) {
					unset( $handlers[ $idx ] );
				} else {
					// also normalize method on the handler to make later checks consistent
					$handlers[ $idx ]['methods'] = $m;
					$seen[ $key ] = true;
				}
			}
			$handlers = array_values( $handlers );

			// 2) Warn on true conflicts (same method, different callbacks)
			$by_method = [];
			foreach ( $handlers as $h ) {
				$m  = $normalize_method( $h['methods'] ?? '' );
				$id = ap__callback_id( $h['callback'] ?? null );

				if ( isset( $by_method[ $m ] ) && $by_method[ $m ] !== $id ) {
					$should_warn = apply_filters(
						'ap_rest_dedupe_warn',
						! ap__is_test_mode(), // default: suppress in tests
						$route,
						$m,
						$by_method[ $m ],
						$id
					);
					if ( $should_warn ) {
						_doing_it_wrong(
							'ap_rest_dedupe',
							sprintf( 'Conflicting REST route %s (%s)', $route, $m ),
							'1.0.0'
						);
					}
				} else {
					$by_method[ $m ] = $id;
				}
			}
		}
		return $endpoints;
	}
	add_filter( 'rest_endpoints', 'ap_rest_dedupe_endpoints', 99 );
}
