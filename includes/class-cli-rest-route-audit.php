<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * WP-CLI command to audit registered REST API routes for conflicts.
 */
class AP_CLI_Rest_Route_Audit {

	/**
	 * Execute the command.
	 *
	 * ## OPTIONS
	 *
	 * [--json]
	 * : Output results as JSON.
	 *
	 * [--fix]
	 * : Show suggested fixes for detected conflicts.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ap:audit-rest-routes
	 *     wp ap:audit-rest-routes --json
	 *     wp ap:audit-rest-routes --fix
	 *
	 * @param array $args       Positional arguments (unused).
	 * @param array $assoc_args Associative options.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$conflicts = $this->find_conflicts();

		if ( isset( $assoc_args['json'] ) ) {
			\WP_CLI::print_value( array_values( $conflicts ), array( 'json' => true ) );
			return;
		}

		if ( ! $conflicts ) {
			\WP_CLI::success( 'No REST route conflicts found.' );
			return;
		}

		foreach ( $conflicts as $conflict ) {
                        \WP_CLI::log( sprintf( '%1$s %2$s', $conflict['method'], $conflict['path'] ) );
			foreach ( $conflict['callbacks'] as $cb ) {
                                $line = sprintf( '  - %1$s', $cb['description'] );
                                if ( $cb['location'] ) {
                                        $line .= sprintf( ' (%1$s)', $cb['location'] );
                                }
                                if ( $cb['plugin'] ) {
                                        $line .= sprintf( ' [%1$s]', $cb['plugin'] );
                                }
				\WP_CLI::log( $line );
			}
			if ( $conflict['overrides_core'] ) {
				\WP_CLI::warning( '  Overrides core route.' );
			}
			if ( isset( $assoc_args['fix'] ) ) {
				foreach ( $conflict['suggestions'] as $suggestion ) {
					\WP_CLI::log( '  Suggestion: ' . $suggestion );
				}
			}
			\WP_CLI::log( '' );
		}
	}

	/**
	 * Find REST route conflicts.
	 *
	 * @return array
	 */
	public function find_conflicts(): array {
		$server = rest_get_server();
		$routes = $server->get_routes();
		$map    = array();

		foreach ( $routes as $path => $endpoints ) {
			foreach ( $endpoints as $endpoint ) {
				$methods = $this->normalize_methods( $endpoint['methods'] ?? array() );
				foreach ( $methods as $method ) {
					$key = $method . ' ' . $path;
					if ( ! isset( $map[ $key ] ) ) {
						$map[ $key ] = array(
							'path'      => $path,
							'method'    => $method,
							'callbacks' => array(),
						);
					}
					$map[ $key ]['callbacks'][] = $this->describe_callback( $endpoint['callback'] );
				}
			}
		}

		$conflicts = array();
		foreach ( $map as $key => $data ) {
			$unique = array_unique(
				array_map(
					static function ( $cb ) {
						return $cb['callable_id'];
					},
					$data['callbacks']
				)
			);
			if ( count( $unique ) < 2 ) {
				continue;
			}
			$data['overrides_core'] = $this->check_overrides_core( $data['callbacks'] );
			$data['suggestions']    = $this->suggest_fixes( $data['path'] );
			$conflicts[ $key ]      = $data;
		}

		return $conflicts;
	}

	/**
	 * Normalize HTTP methods to an array of method strings.
	 *
	 * @param mixed $methods Methods value from route definition.
	 * @return array
	 */
	private function normalize_methods( $methods ): array {
		if ( is_string( $methods ) ) {
			$methods = array_map( 'trim', explode( ',', $methods ) );
		} elseif ( is_int( $methods ) ) {
			$methods = $this->methods_from_mask( $methods );
		} elseif ( is_array( $methods ) ) {
			if ( $this->is_assoc( $methods ) ) {
				$methods = array_keys( $methods );
			}
		} else {
			$methods = array();
		}

		return array_map( 'strtoupper', $methods );
	}

	/**
	 * Convert WP_REST_Server method mask to array of HTTP methods.
	 */
	private function methods_from_mask( int $mask ): array {
		$map     = array(
			\WP_REST_Server::READABLE  => array( 'GET', 'HEAD' ),
			\WP_REST_Server::CREATABLE => array( 'POST' ),
			\WP_REST_Server::EDITABLE  => array( 'PUT', 'PATCH' ),
			\WP_REST_Server::DELETABLE => array( 'DELETE' ),
		);
		$methods = array();
		foreach ( $map as $bit => $verbs ) {
			if ( $mask & $bit ) {
				$methods = array_merge( $methods, $verbs );
			}
		}
		return array_unique( $methods );
	}

	/**
	 * Describe a callback.
	 */
	private function describe_callback( $callback ): array {
		$type        = '';
		$description = '';
		$location    = '';
		$plugin      = null;
		$is_core     = false;

		try {
			if ( is_string( $callback ) ) {
				$type        = 'function';
				$description = $callback;
				$ref         = new \ReflectionFunction( $callback );
			} elseif ( is_array( $callback ) ) {
				$class       = is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0];
				$type        = 'class_method';
				$description = $class . '::' . $callback[1];
				$ref         = new \ReflectionMethod( $class, $callback[1] );
			} elseif ( $callback instanceof \Closure ) {
				$type        = 'closure';
				$description = 'closure';
				$ref         = new \ReflectionFunction( $callback );
			} elseif ( is_object( $callback ) && method_exists( $callback, '__invoke' ) ) {
				$class       = get_class( $callback );
				$type        = 'invokable';
				$description = $class . '::__invoke';
				$ref         = new \ReflectionMethod( $class, '__invoke' );
			} else {
				$type        = gettype( $callback );
				$description = '(unknown)';
				$ref         = null;
			}

			if ( isset( $ref ) ) {
				$file     = $ref->getFileName();
				$line     = $ref->getStartLine();
				$location = $file && $line ? $file . ':' . $line : '';
				$plugin   = $this->identify_plugin( $file );
				$is_core  = $this->is_core_file( $file );
			}
		} catch ( \ReflectionException $e ) {
			// Ignore reflection errors.
		}

		return array(
			'type'        => $type,
			'description' => $description,
			'location'    => $location,
			'plugin'      => $plugin,
			'is_core'     => $is_core,
			'callable_id' => $description,
		);
	}

	/**
	 * Identify plugin or theme responsible for a file path.
	 */
	private function identify_plugin( ?string $file ): ?string {
		if ( ! $file ) {
			return null;
		}
		if ( defined( 'WP_PLUGIN_DIR' ) && str_starts_with( $file, WP_PLUGIN_DIR ) ) {
			$relative = substr( $file, strlen( WP_PLUGIN_DIR ) + 1 );
			return strtok( $relative, '/' );
		}
		if ( defined( 'WP_CONTENT_DIR' ) ) {
			$themes = WP_CONTENT_DIR . '/themes/';
			if ( str_starts_with( $file, $themes ) ) {
				$relative = substr( $file, strlen( $themes ) );
				return strtok( $relative, '/' );
			}
		}
		return null;
	}

	/**
	 * Determine if file belongs to WordPress core.
	 */
	private function is_core_file( ?string $file ): bool {
		if ( ! $file || ! defined( 'ABSPATH' ) ) {
			return false;
		}
		return str_starts_with( $file, ABSPATH . 'wp-includes' ) || str_starts_with( $file, ABSPATH . 'wp-admin' );
	}

	/**
	 * Check if a route overrides a core endpoint.
	 */
	private function check_overrides_core( array $callbacks ): bool {
		$has_core   = false;
		$has_custom = false;
		foreach ( $callbacks as $cb ) {
			if ( $cb['is_core'] ) {
				$has_core = true;
			} else {
				$has_custom = true;
			}
		}
		return $has_core && $has_custom;
	}

	/**
	 * Provide fix suggestions for a route path.
	 */
	private function suggest_fixes( string $path ): array {
		return array(
                    sprintf( "Wrap custom register_rest_route() in: if ( ! rest_route_exists( '%1\$s' ) ) { register_rest_route( ... ); }", $path ),
			'Consider namespacing, conditional registration, or unregistering the conflicting route.',
		);
	}

	/**
	 * Determine if array is associative.
	 */
	private function is_assoc( array $arr ): bool {
		return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'ap:audit-rest-routes', 'AP_CLI_Rest_Route_Audit' );
}
