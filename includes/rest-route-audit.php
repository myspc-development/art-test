<?php
if (!defined('ABSPATH')) { exit; }


/**
 * WP-CLI command: wp rest:audit
 * Lists REST route conflicts where the same route and method have different callbacks.
 */
function ap_rest_route_audit(): void {
    $server = rest_get_server();
    $routes = $server->get_routes();
    $conflicts = [];

    foreach ($routes as $route => $endpoints) {
        $method_map = [];
        if (!is_array($endpoints)) {
            continue;
        }
        foreach ($endpoints as $endpoint) {
            $methods = $endpoint['methods'] ?? '';
            $callback = $endpoint['callback'] ?? null;
            if (!$callback) {
                continue;
            }
            $methods = is_array($methods) ? $methods : explode(',', (string) $methods);
            $signature = ap_rest_callback_signature($callback);
            $file = ap_rest_callback_file($callback);
            $plugin = ap_rest_callback_plugin($file);
            foreach ($methods as $method) {
                $method = trim($method);
                $method_map[$method][] = [
                    'callback' => $signature,
                    'file'     => $file,
                    'plugin'   => $plugin,
                ];
            }
        }
        foreach ($method_map as $method => $handlers) {
            if (count($handlers) > 1) {
                $conflicts[$route][$method] = $handlers;
            }
        }
    }

    if (!$conflicts) {
        \WP_CLI::success('No REST route conflicts found.');
        return;
    }

    foreach ($conflicts as $route => $methods) {
        \WP_CLI::line("Route: {$route}");
        foreach ($methods as $method => $handlers) {
            \WP_CLI::line("  Method: {$method}");
            foreach ($handlers as $index => $handler) {
                $owner = $handler['plugin'] ?: basename((string) $handler['file']);
                $preferred = $index === 0 ? ' (preferred)' : '';
                \WP_CLI::line("    {$handler['callback']} - {$owner}{$preferred}");
                if ($index > 0) {
                    $suggest = $handler['plugin']
                        ? "is_plugin_active('{$handler['plugin']}')"
                        : "function_exists('{$handler['callback']}')";
                    \WP_CLI::line("      Suggest: conditionally register using {$suggest}");
                }
            }
        }
    }
}

/**
 * Return a readable callback signature.
 */
function ap_rest_callback_signature($callback): string {
    if (is_string($callback)) {
        return $callback;
    }
    if (is_array($callback)) {
        $class = is_object($callback[0]) ? get_class($callback[0]) : (string) $callback[0];
        return $class . '::' . (string) $callback[1];
    }
    if ($callback instanceof \Closure) {
        return 'closure';
    }
    return 'callable';
}

/**
 * Get the file where the callback is defined.
 */
function ap_rest_callback_file($callback): ?string {
    try {
        if (is_string($callback) && function_exists($callback)) {
            $ref = new \ReflectionFunction($callback);
            return $ref->getFileName();
        }
        if (is_array($callback)) {
            $ref = new \ReflectionMethod($callback[0], $callback[1]);
            return $ref->getFileName();
        }
        if ($callback instanceof \Closure) {
            $ref = new \ReflectionFunction($callback);
            return $ref->getFileName();
        }
    } catch (\ReflectionException $e) {
        return null;
    }
    return null;
}

/**
 * Detect owning plugin or theme for a given file path.
 */
function ap_rest_callback_plugin(?string $file): ?string {
    if (!$file) {
        return null;
    }
    $wp_plugin_dir = rtrim(WP_PLUGIN_DIR, '/');
    $theme_root    = rtrim(get_theme_root(), '/');
    if (strpos($file, $wp_plugin_dir) === 0) {
        $rel = substr($file, strlen($wp_plugin_dir) + 1);
        return strtok($rel, '/');
    }
    if (strpos($file, $theme_root) === 0) {
        $rel = substr($file, strlen($theme_root) + 1);
        return 'theme:' . strtok($rel, '/');
    }
    return null;
}

if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('rest audit', 'ap_rest_route_audit');
}
