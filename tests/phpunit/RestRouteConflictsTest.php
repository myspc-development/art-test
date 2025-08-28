<?php

use PHPUnit\Framework\TestCase;

// Minimal in-memory REST route registry for testing.
if (!function_exists('register_rest_route')) {
    /** @var array<string, array<int, array<string, mixed>>> $GLOBALS['__rest_routes'] */
    $GLOBALS['__rest_routes'] = [];

    function register_rest_route(string $namespace, string $route, array $args): void {
        $path = '/' . trim($namespace, '/') . '/' . ltrim($route, '/');
        $GLOBALS['__rest_routes'][$path][] = [
            'methods'  => $args['methods'] ?? 'GET',
            'callback' => $args['callback'],
        ];
    }

    class _WP_REST_Server {
        /** @return array<string, array<int, array<string, mixed>>> */
        public function get_routes(): array {
            return $GLOBALS['__rest_routes'];
        }
    }

    function rest_get_server(): _WP_REST_Server {
        return new _WP_REST_Server();
    }
}

function foo_callback() {}
function bar_callback() {}

class RestRouteConflictsTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['__rest_routes'] = [];
    }

    public function testDetectsDuplicateRoutesWithDifferentCallbacks(): void
    {
        register_rest_route('ap/v1', '/duplicate', [
            'methods'  => 'GET',
            'callback' => 'foo_callback',
        ]);
        register_rest_route('ap/v1', '/duplicate', [
            'methods'  => 'GET',
            'callback' => 'bar_callback',
        ]);

        $routes    = rest_get_server()->get_routes();
        $conflicts = $this->findConflicts($routes);

        $this->assertCount(1, $conflicts);
        $entry = $conflicts[0];
        $this->assertSame('/ap/v1/duplicate', $entry['route']);
        $this->assertSame('GET', $entry['method']);
        $callbacks = array_column($entry['callbacks'], 'callback');
        sort($callbacks);
        $this->assertSame(['bar_callback', 'foo_callback'], $callbacks);
    }

    public function testJsonOutputStructure(): void
    {
        register_rest_route('ap/v1', '/duplicate', [
            'methods'  => 'GET',
            'callback' => 'foo_callback',
        ]);
        register_rest_route('ap/v1', '/duplicate', [
            'methods'  => 'GET',
            'callback' => 'bar_callback',
        ]);

        $routes    = rest_get_server()->get_routes();
        $conflicts = $this->findConflicts($routes);
        $json      = json_encode($conflicts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $data      = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $entry = $data[0];
        $this->assertSame('/ap/v1/duplicate', $entry['route']);
        $this->assertSame('GET', $entry['method']);
        $this->assertIsArray($entry['callbacks']);
        $this->assertCount(2, $entry['callbacks']);
        $callbacks = array_column($entry['callbacks'], 'callback');
        sort($callbacks);
        $this->assertSame(['bar_callback', 'foo_callback'], $callbacks);
    }

    public function testSuggestFixOptionEmitsSuggestions(): void
    {
        register_rest_route('ap/v1', '/duplicate', [
            'methods'  => 'GET',
            'callback' => 'foo_callback',
        ]);
        register_rest_route('ap/v1', '/duplicate', [
            'methods'  => 'GET',
            'callback' => 'bar_callback',
        ]);

        $routes      = rest_get_server()->get_routes();
        $conflicts   = $this->findConflicts($routes);
        $suggestions = $this->generateSuggestions($conflicts);

        $this->assertStringContainsString('# Suggested fix for GET /ap/v1/duplicate (foo_callback)', $suggestions);
        $this->assertStringContainsString('# Suggested fix for GET /ap/v1/duplicate (bar_callback)', $suggestions);
    }

    /**
     * @param array<string, array<int, array<string, mixed>>> $routes
     * @return array<int, array{route:string, method:string, callbacks:array<int, array{callback:string}>}>
     */
    private function findConflicts(array $routes): array
    {
        $conflicts = [];
        foreach ($routes as $route => $handlers) {
            $seen = [];
            foreach ($handlers as $handler) {
                if (!isset($handler['methods'], $handler['callback'])) {
                    continue;
                }
                $methods = is_array($handler['methods']) ? $handler['methods'] : explode(',', $handler['methods']);
                foreach ($methods as $method) {
                    $method   = trim($method);
                    $callback = $handler['callback'];
                    if (isset($seen[$method]) && $seen[$method]['callback'] !== $callback) {
                        $key = $route . '|' . $method;
                        $conflicts[$key][$seen[$method]['callback']] = $seen[$method];
                        $conflicts[$key][$callback]                  = ['callback' => $callback];
                    } else {
                        $seen[$method] = ['callback' => $callback];
                    }
                }
            }
        }

        $result = [];
        foreach ($conflicts as $key => $callbacks) {
            [$route, $method] = explode('|', $key);
            $result[] = [
                'route'     => $route,
                'method'    => $method,
                'callbacks' => array_values($callbacks),
            ];
        }

        return $result;
    }

    /**
     * @param array<int, array{route:string, method:string, callbacks:array<int, array{callback:string}>}> $conflicts
     */
    private function generateSuggestions(array $conflicts): string
    {
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

        $out = '';
        foreach ($conflicts as $conflict) {
            foreach ($conflict['callbacks'] as $cb) {
                $out .= sprintf(
                    "# Suggested fix for %s %s (%s)\n%s\n",
                    $conflict['method'],
                    $conflict['route'],
                    $cb['callback'],
                    sprintf($snippet, $conflict['route'], $conflict['method'], $conflict['method'], $cb['callback'])
                );
            }
        }

        return $out;
    }
}

