<?php
declare(strict_types=1);

/**
 * Check whether a REST API route has already been registered.
 *
 * Uses the global REST server to determine if the combination of namespace
 * and route exists in the server's route map.
 *
 * @param string $namespace REST API namespace, e.g. 'artpulse/v1'.
 * @param string $route     Route path beginning with '/', e.g. '/widget'.
 *
 * @return bool True when the route is registered, false when it is not or the
 *              REST server is unavailable.
 */
function ap_rest_route_registered( string $namespace, string $route ): bool {
		$server = rest_get_server();
	if ( ! $server ) {
			return false;
	}

		$routes = $server->get_routes();
		$key    = '/' . ltrim( $namespace, '/' ) . $route;

		return isset( $routes[ $key ] );
}
