<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Detect and merge duplicate REST route handlers during bootstrapping.
 */
function ap_deduplicate_rest_routes(array $endpoints): array {
    $seen = [];
    $core_prefixes = ['/wp/v2', '/wp/v2/'];

    foreach ($endpoints as $route => $handlers) {
        if (!is_array($handlers)) {
            continue;
        }

        // Skip core endpoints to avoid noise from WordPress internals.
        foreach ($core_prefixes as $prefix) {
            if (strpos($route, $prefix) === 0) {
                continue 2;
            }
        }

        foreach ($handlers as $key => $handler) {
            $methods = $handler['methods'] ?? ['ALL'];
            if (!is_array($methods)) {
                $methods = preg_split('/[\s,|]+/', (string) $methods);
            }
            $methods = array_filter(array_map('strtoupper', $methods));
            sort($methods);
            $hash    = md5(serialize([$methods, $route]));
            $method_label = implode(',', $methods) ?: 'ALL';

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
                        $prev_loc = ap_callback_location($prev['callback'] ?? null);
                        $curr_loc = ap_callback_location($handler['callback'] ?? null);
                        error_log("[REST CONFLICT] Duplicate route $route ($method_label) between $prev_loc and $curr_loc.");
                    }
                }
            } else {
                $seen[$hash] = $handler;
            }
        }
    }

    return $endpoints;
}

function ap_callback_location($callback): string {
    try {
        if (is_array($callback)) {
            if (is_object($callback[0])) {
                $ref = new ReflectionMethod($callback[0], $callback[1]);
            } else {
                $ref = new ReflectionMethod($callback[0], $callback[1]);
            }
        } elseif (is_string($callback) && function_exists($callback)) {
            $ref = new ReflectionFunction($callback);
        } else {
            return 'unknown';
        }

        return $ref->getFileName() . ':' . $ref->getStartLine();
    } catch (Throwable $e) {
        return 'unknown';
    }
}

/**
 * Determine if a REST route is already registered.
 *
 * Optionally, verify that a specific HTTP method exists for the route.
 */
function ap_rest_route_registered(string $namespace, string $route, ?string $method = null): bool {
    global $wp_rest_server;
    if (!$wp_rest_server) {
        $wp_rest_server = rest_get_server();
    }

    $full_route = '/' . trim($namespace, '/') . $route;
    $routes     = $wp_rest_server->get_routes();

    if (!isset($routes[$full_route])) {
        return false;
    }

    if ($method === null) {
        return true;
    }

    $method   = strtoupper($method);
    $handlers = $routes[$full_route];
    if (!is_array($handlers)) {
        return false;
    }

    foreach ($handlers as $handler) {
        $methods = $handler['methods'] ?? [];

        if (is_string($methods)) {
            $list = array_map('trim', explode('|', $methods));
        } elseif (is_int($methods) && class_exists('\\WP_REST_Server')) {
            $map  = [
                \WP_REST_Server::READABLE   => ['GET', 'HEAD'],
                \WP_REST_Server::CREATABLE  => ['POST'],
                \WP_REST_Server::EDITABLE   => ['PUT', 'PATCH'],
                \WP_REST_Server::DELETABLE  => ['DELETE'],
            ];
            $list = [];
            foreach ($map as $bit => $verbs) {
                if ($methods & $bit) {
                    $list = array_merge($list, $verbs);
                }
            }
        } elseif (is_array($methods)) {
            // Associative array of methods => details or simple list.
            $keys = array_keys($methods);
            $list = array_keys($methods) === range(0, count($methods) - 1)
                ? $methods
                : $keys;
        } else {
            $list = [];
        }

        foreach ($list as $verb) {
            if (strtoupper($verb) === $method) {
                return true;
            }
        }
    }

    return false;
}

add_filter('rest_endpoints', 'ap_deduplicate_rest_routes', 999);
