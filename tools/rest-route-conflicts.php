#!/usr/bin/env php
<?php
/**
 * Detect duplicate REST route registrations.
 */

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, 'This script must be run from the command line.' . PHP_EOL );
	exit( 1 );
}

$opts        = getopt( '', array( 'json', 'suggest-fix', 'output:' ) );
$output_file = $opts['output'] ?? null;
$json        = array_key_exists( 'json', $opts ) || $output_file !== null;
$suggest     = array_key_exists( 'suggest-fix', $opts );

$wp_load = __DIR__ . '/../wordpress/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
	fwrite( STDERR, 'WordPress bootstrap not found at ' . $wp_load . '. Skipping check.' . PHP_EOL );
	exit( 0 );
}

require $wp_load;

// Register all routes.
do_action( 'rest_api_init' );
$routes = rest_get_server()->get_routes();

$conflicts = array();

foreach ( $routes as $route => $handlers ) {
	$seen = array();
	foreach ( $handlers as $handler ) {
		if ( ! isset( $handler['methods'], $handler['callback'] ) ) {
			continue;
		}
		$methods = is_array( $handler['methods'] ) ? $handler['methods'] : explode( ',', $handler['methods'] );
		foreach ( $methods as $method ) {
			$method = trim( $method );
			$info   = callback_info( $handler['callback'] );
			if ( isset( $seen[ $method ] ) && $seen[ $method ]['callback'] !== $info['callback'] ) {
				$conflicts[ $route . '|' . $method ][] = $seen[ $method ];
				$conflicts[ $route . '|' . $method ][] = $info;
			} else {
				$seen[ $method ] = $info;
			}
		}
	}
}

if ( $json ) {
	$data = array();
	foreach ( $conflicts as $key => $callbacks ) {
		[$route, $method] = explode( '|', $key );
		$data[]           = array(
			'route'     => $route,
			'method'    => $method,
			'callbacks' => $callbacks,
		);
	}
	$json_data = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	if ( $output_file ) {
		file_put_contents( $output_file, $json_data . PHP_EOL );
	} else {
		echo $json_data . PHP_EOL;
	}
} elseif ( empty( $conflicts ) ) {
		echo 'No duplicate REST routes found.' . PHP_EOL;
} else {
	$format = '%-7s %-40s %-30s %-30s %-10s' . PHP_EOL;
	printf( $format, 'Method', 'Route', 'Callback', 'File:Line', 'Source' );
	foreach ( $conflicts as $key => $callbacks ) {
		[$route, $method] = explode( '|', $key );
		foreach ( $callbacks as $cb ) {
			$fileline = $cb['file'] ? $cb['file'] . ':' . $cb['line'] : 'n/a';
			printf( $format, $method, $route, $cb['callback'], $fileline, $cb['source'] );
		}
	}
}

if ( $suggest && ! empty( $conflicts ) ) {
	foreach ( $conflicts as $key => $callbacks ) {
		[$route, $method] = explode( '|', $key );
		foreach ( $callbacks as $cb ) {
			$snippet = <<<'SNIPPET'
add_action('rest_api_init', function () {
    $server = rest_get_server();
    $routes = $server->get_routes();
    $path = '%s';
    foreach ($routes[$path] ?? [] as $endpoint) {
        if (strpos($endpoint['methods'], '%s') !== false) {
            return;
        }
    }
    register_rest_route(ARTPULSE_API_NAMESPACE, $path, [
        'methods' => '%s',
        'callback' => '%s',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ]);
});
SNIPPET;
			printf(
				"# Suggested fix for %s %s (%s)\n%s\n",
				$method,
				$route,
				$cb['callback'],
				sprintf( $snippet, $route, $method, $method, $cb['callback'] )
			);
		}
	}
}

exit( empty( $conflicts ) ? 0 : 1 );

function callback_info( $callback ): array {
	$name   = 'unknown';
	$file   = null;
	$line   = null;
	$source = 'unknown';
	try {
		if ( is_array( $callback ) ) {
			$name = ( is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0] ) . '::' . $callback[1];
			$ref  = new ReflectionMethod( $callback[0], $callback[1] );
		} elseif ( is_string( $callback ) && function_exists( $callback ) ) {
			$name = $callback;
			$ref  = new ReflectionFunction( $callback );
		} elseif ( is_object( $callback ) && method_exists( $callback, '__invoke' ) ) {
			$name = get_class( $callback ) . '::__invoke';
			$ref  = new ReflectionMethod( $callback, '__invoke' );
		} else {
			$ref = null;
		}
		if ( $ref instanceof ReflectionFunctionAbstract ) {
			$file   = $ref->getFileName();
			$line   = $ref->getStartLine();
			$source = source_from_file( $file );
		}
	} catch ( ReflectionException $e ) {
		// ignore
	}
	return array(
		'callback' => $name,
		'file'     => $file,
		'line'     => $line,
		'source'   => $source,
	);
}

function source_from_file( ?string $file ): string {
	if ( ! $file ) {
		return 'unknown';
	}
	$theme_root = function_exists( 'get_theme_root' ) ? get_theme_root() : null;
	if ( defined( 'WP_PLUGIN_DIR' ) && str_starts_with( $file, WP_PLUGIN_DIR ) ) {
		return 'plugin';
	}
	if ( $theme_root && str_starts_with( $file, $theme_root ) ) {
		return 'theme';
	}
	if ( defined( 'ABSPATH' ) && str_starts_with( $file, ABSPATH ) ) {
		return 'core';
	}
	return 'unknown';
}
