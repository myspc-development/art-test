<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Detect and merge duplicate REST route handlers during bootstrapping.
 */
function ap_deduplicate_rest_routes(array $endpoints): array {
    $seen = [];

    foreach ($endpoints as $route => $handlers) {
        if (!is_array($handlers)) {
            continue;
        }
        foreach ($handlers as $key => $handler) {
            $method = $handler['methods'] ?? 'ALL';
            $hash   = md5(serialize([$method, $route]));

            if (isset($seen[$hash])) {
                $prev = $seen[$hash];
                $same_callback  = isset($prev['callback']) && isset($handler['callback']) && $prev['callback'] === $handler['callback'];
                $same_permission = ($prev['permission_callback'] ?? null) === ($handler['permission_callback'] ?? null);

                if ($same_callback && $same_permission) {
                    // Identical handler already registered - remove duplicate.
                    unset($endpoints[$route][$key]);
                } else {
                    // Different handler registered for same route/method.
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("[REST CONFLICT] Duplicate route $route ($method) with different callbacks.");
                    }
                }
            } else {
                $seen[$hash] = $handler;
            }
        }
    }

    return $endpoints;
}

/**
 * Determine if a REST route is already registered.
 */
function ap_rest_route_registered(string $namespace, string $route): bool {
    global $wp_rest_server;
    if (!$wp_rest_server) {
        $wp_rest_server = rest_get_server();
    }
    $full_route = '/' . trim($namespace, '/') . $route;
    return isset($wp_rest_server->get_routes()[$full_route]);
}

add_filter('rest_endpoints', 'ap_deduplicate_rest_routes', 999);
