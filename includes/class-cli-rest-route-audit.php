<?php
if (!defined('ABSPATH')) { exit; }

/**
 * WP-CLI command to audit REST route conflicts.
 */
class AP_CLI_REST_Route_Audit {
    /**
     * Audit REST route registrations and optionally remove conflicts.
     *
     * ## OPTIONS
     *
     * [--json]
     * : Output results as JSON.
     *
     * [--fix]
     * : Unregister duplicate callbacks, keeping only the first handler for each conflicting route and method.
     *
     * ## EXAMPLES
     *
     *     # List conflicting routes
     *     wp ap:audit-rest-routes
     *
     *     # Output conflicts as JSON
     *     wp ap:audit-rest-routes --json
     *
     *     # Attempt to remove duplicate callbacks
     *     wp ap:audit-rest-routes --fix
     *
     * @param array $args       Positional arguments (unused).
     * @param array $assoc_args Associative arguments.
     */
    public function __invoke(array $args, array $assoc_args): void {
        $server = rest_get_server();
        $routes = $server->get_routes();
        $conflicts = [];

        foreach ($routes as $route => $endpoints) {
            if (!is_array($endpoints)) {
                continue;
            }
            $method_map = [];
            foreach ($endpoints as $endpoint) {
                $methods  = $endpoint['methods'] ?? '';
                $callback = $endpoint['callback'] ?? null;
                if (!$callback) {
                    continue;
                }
                $methods = is_array($methods) ? $methods : explode(',', (string) $methods);
                $signature = ap_rest_callback_signature($callback);
                $file      = ap_rest_callback_file($callback);
                $plugin    = ap_rest_callback_plugin($file);
                foreach ($methods as $method) {
                    $method = trim($method);
                    $method_map[$method][] = [
                        'callback' => $signature,
                        'file'     => $file,
                        'plugin'   => $plugin,
                        'endpoint' => $endpoint,
                    ];
                }
            }
            foreach ($method_map as $method => $handlers) {
                if (count($handlers) > 1) {
                    $conflicts[$route][$method] = $handlers;
                }
            }
        }

        if (isset($assoc_args['json'])) {
            $out = [];
            foreach ($conflicts as $route => $methods) {
                foreach ($methods as $method => $handlers) {
                    foreach ($handlers as $h) {
                        $out[$route][$method][] = [
                            'callback' => $h['callback'],
                            'plugin'   => $h['plugin'],
                        ];
                    }
                }
            }
            \WP_CLI::print_value($out);
            return;
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

        if (isset($assoc_args['fix'])) {
            foreach ($conflicts as $route => $methods) {
                $parts = explode('/', ltrim($route, '/'), 2);
                $namespace = $parts[0] ?? '';
                $path = '/' . ($parts[1] ?? '');
                unregister_rest_route($namespace, $path);
                $endpoints = [];
                foreach ($methods as $method => $handlers) {
                    $pref = $handlers[0]['endpoint'];
                    $pref['methods'] = $method;
                    $endpoints[] = $pref;
                }
                foreach ($endpoints as $ep) {
                    register_rest_route($namespace, $path, $ep);
                }
            }
            \WP_CLI::success('Conflicting routes cleaned.');
        }
    }
}

if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('ap:audit-rest-routes', 'AP_CLI_REST_Route_Audit');
}

