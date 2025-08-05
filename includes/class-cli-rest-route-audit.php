<?php
if (!defined('ABSPATH')) { exit; }

/**

            foreach ($endpoints as $endpoint) {
                $methods  = $endpoint['methods'] ?? '';
                $callback = $endpoint['callback'] ?? null;
                if (!$callback) {
                    continue;
                }

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
}

if (defined('WP_CLI') && WP_CLI) {
