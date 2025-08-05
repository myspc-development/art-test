<?php
if (!defined('ABSPATH')) { exit; }

/**
 * WP-CLI command to audit REST route conflicts.
 */
class AP_CLI_Rest_Route_Audit {
    /**
     * Handle the command.
     *
     * ## OPTIONS
     *
     * [--json]
     * : Output results as JSON.
     *
     * [--fix]
     * : Show suggested fixes for each conflict.
     */
    public function __invoke(array $args, array $assoc_args): void {
        $json = isset($assoc_args['json']);
        $fix  = isset($assoc_args['fix']);

        $server    = rest_get_server();
        $routes    = $server->get_routes();
        $conflicts = [];

        foreach ($routes as $path => $endpoints) {
            if (!is_array($endpoints)) {
                continue;
            }

            $map = [];
            foreach ($endpoints as $endpoint) {
                if (empty($endpoint['callback'])) {
                    continue;
                }

                $methods = $endpoint['methods'] ?? '';
                $methods = is_array($methods) ? $methods : explode(',', (string)$methods);

                $info = $this->inspect_callback($endpoint['callback']);

                foreach ($methods as $method) {
                    $method = strtoupper(trim((string)$method));
                    $map[$method][$info['signature']] = $info;
                }
            }

            foreach ($map as $method => $handlers) {
                if (count($handlers) > 1) {
                    $core_override = false;
                    foreach ($handlers as $handler) {
                        if ($handler['owner'] === 'core') {
                            $core_override = true;
                            break;
                        }
                    }
                    $conflicts[$path][$method] = [
                        'callbacks'     => array_values($handlers),
                        'core_override' => $core_override,
                    ];
                }
            }
        }

        if (!$conflicts) {
            \WP_CLI::success('No REST route conflicts found.');
            return;
        }

        if ($json) {
            $out = [];
            foreach ($conflicts as $path => $methods) {
                foreach ($methods as $method => $data) {
                    $item = [
                        'route'         => $path,
                        'method'        => $method,
                        'core_override' => $data['core_override'],
                        'callbacks'     => $data['callbacks'],
                    ];
                    if ($fix) {
                        $item['suggestions'] = $this->fix_suggestions($path, $method);
                    }
                    $out[] = $item;
                }
            }
            \WP_CLI::print_value($out, ['format' => 'json']);
            return;
        }

        foreach ($conflicts as $path => $methods) {
            \WP_CLI::line("Route: {$path}");
            foreach ($methods as $method => $data) {
                $handlers      = $data['callbacks'];
                $core_override = $data['core_override'] ? ' (overrides core endpoint)' : '';
                \WP_CLI::line("  Method: {$method}{$core_override}");
                foreach ($handlers as $info) {
                    $owner    = $info['owner'] ?: basename((string)$info['file']);
                    $location = $info['file'] ? $info['file'] . ':' . $info['line'] : '';
                    \WP_CLI::line(
                        "    {$info['signature']} [{$info['type']}] - {$owner} {$location}"
                    );
                }
                if ($fix) {
                    foreach ($this->fix_suggestions($path, $method) as $suggest) {
                        \WP_CLI::line("    Suggest: {$suggest}");
                    }
                }
            }
        }
    }

    /**
     * Inspect a callback and return details.
     */
    private function inspect_callback($callback): array {
        $info = [
            'signature' => '',
            'type'      => '',
            'file'      => null,
            'line'      => null,
            'owner'     => null,
        ];

        try {
            if (is_string($callback)) {
                $info['signature'] = $callback;
                $info['type']      = 'function';
                if (function_exists($callback)) {
                    $ref          = new \ReflectionFunction($callback);
                    $info['file'] = $ref->getFileName();
                    $info['line'] = $ref->getStartLine();
                }
            } elseif (is_array($callback)) {
                $class            = is_object($callback[0]) ? get_class($callback[0]) : (string)$callback[0];
                $info['signature'] = $class . '::' . (string)$callback[1];
                $info['type']      = 'method';
                $ref               = new \ReflectionMethod($callback[0], $callback[1]);
                $info['file']      = $ref->getFileName();
                $info['line']      = $ref->getStartLine();
            } elseif ($callback instanceof \Closure) {
                $info['signature'] = 'closure';
                $info['type']      = 'closure';
                $ref               = new \ReflectionFunction($callback);
                $info['file']      = $ref->getFileName();
                $info['line']      = $ref->getStartLine();
            } elseif (is_object($callback) && method_exists($callback, '__invoke')) {
                $class            = get_class($callback);
                $info['signature'] = $class . '::__invoke';
                $info['type']      = 'invokable';
                $ref               = new \ReflectionMethod($callback, '__invoke');
                $info['file']      = $ref->getFileName();
                $info['line']      = $ref->getStartLine();
            } else {
                $info['signature'] = 'callable';
                $info['type']      = 'unknown';
            }
        } catch (\ReflectionException $e) {
            // Ignore reflection errors.
        }

        if ($info['file']) {
            $info['owner'] = $this->detect_owner($info['file']);
        }

        return $info;
    }

    /**
     * Identify the owning plugin, theme, or core for a file path.
     */
    private function detect_owner(string $file): ?string {
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
        if (strpos($file, ABSPATH) === 0) {
            return 'core';
        }
        return null;
    }

    /**
     * Generate fix suggestions.
     */
    private function fix_suggestions(string $path, string $method): array {
        $ns    = '';
        $route = $path;
        if (preg_match('#^/([^/]+)(/.*)$#', $path, $m)) {
            $ns    = $m[1];
            $route = $m[2];
        }

        $suggestions   = [];
        $suggestions[] = "if (!rest_route_exists('{$path}', '{$method}')) { register_rest_route(...); }";
        if ($ns && $route) {
            $suggestions[] = "add_action('rest_api_init', function() { unregister_rest_route('{$ns}', '{$route}'); }, 20);";
        }
        $suggestions[] = 'Consider using a unique namespace.';

        return $suggestions;
    }
}

if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('ap:audit-rest-routes', 'AP_CLI_Rest_Route_Audit');
}

