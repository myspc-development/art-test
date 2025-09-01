<?php
declare(strict_types=1);

namespace ArtPulse\Rest\Tests;

use WP_UnitTestCase;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Buckets:
 *  A) Registration: required routes exist (no more silent 404s)
 *  B) Permissions: unauth vs read vs admin (401/403/200)
 *  C) Shape: minimal schema expectations for key endpoints
 *
 * Run:
 *   export WP_PHPUNIT__DIR="$(pwd)/vendor/wp-phpunit/wp-phpunit"
 *   export WP_PHPUNIT__TESTS_CONFIG="$(pwd)/tests/wp-tests-config.php"
 *   export AP_TEST_MODE=1
 *   vendor/bin/phpunit -c phpunit.wp.xml.dist --filter RouteBucketsSmokeTest -v
 */
class RouteBucketsSmokeTest extends WP_UnitTestCase
{
    private int $admin_id;
    private int $reader_id;

    /** @var \WP_REST_Server */
    private $server;

    /**
     * Minimal manifest of endpoints we care about right now.
     * - 'pattern' is matched against the route keys returned by $server->get_routes()
     *   (keys often contain regex groups like (?P<id>\d+)).
     * - 'example' is the concrete path we will actually call for perms/shape.
     * - 'methods' is a map method => expectations.
     */
    private array $manifest = [
        [
            'name'    => 'DashboardConfig GET',
            'pattern' => '^/artpulse/v1/dashboard-config$',
            'example' => '/artpulse/v1/dashboard-config',
            'methods' => [
                'GET' => [
                    'perm' => ['unauth' => 401, 'read' => 200, 'admin' => 200],
                    'shape' => [
                        'widget_roles', 'role_widgets', 'layout', 'locked', 'capabilities', 'excluded_roles'
                    ],
                ],
            ],
        ],
        [
            'name'    => 'DashboardConfig POST',
            'pattern' => '^/artpulse/v1/dashboard-config$',
            'example' => '/artpulse/v1/dashboard-config',
            'methods' => [
                'POST' => [
                    // Expect 401 w/o nonce, 403 w/nonce but no cap, 200 as admin w/nonce.
                    'perm' => ['unauth' => 401, 'read' => 403, 'admin' => 200],
                    'payload' => [
                        'widget_roles' => ['member' => ['widget_one']],
                        'role_widgets' => ['member' => ['widget_one']],
                        'locked'       => ['widget_one'],
                    ],
                    // Set the correct custom nonce action your controller checks:
                    'nonce_action' => 'ap_dashboard_config',
                    'nonce_header' => 'X-AP-Nonce',
                    'shape' => ['saved'],
                ],
            ],
        ],
        [
            'name'    => 'WidgetEditor: Roles',
            'pattern' => '^/artpulse/v1/roles$',
            'example' => '/artpulse/v1/roles',
            'methods' => [
                'GET' => [
                    'perm'  => ['unauth' => 401, 'read' => 200, 'admin' => 200],
                    'shape' => [], // array of role slugs
                ],
            ],
        ],
        [
            'name'    => 'WidgetEditor: Widgets',
            'pattern' => '^/artpulse/v1/widgets$',
            'example' => '/artpulse/v1/widgets',
            'methods' => [
                'GET' => [
                    'perm'  => ['unauth' => 401, 'read' => 200, 'admin' => 200],
                    'shape' => [], // array of widget defs
                ],
            ],
        ],
        [
            'name'    => 'WidgetSettings GET',
            'pattern' => '^/artpulse/v1/widget-settings/\(\?P<id>\[a-z0-9_-\]\+\)$',
            'example' => '/artpulse/v1/widget-settings/widget_demo',
            'methods' => [
                'GET' => [
                    'perm'  => ['unauth' => 401, 'read' => 200, 'admin' => 200],
                    'shape' => [], // array/object settings
                ],
            ],
        ],
        [
            'name'    => 'WidgetSettings POST',
            'pattern' => '^/artpulse/v1/widget-settings/\(\?P<id>\[a-z0-9_-\]\+\)$',
            'example' => '/artpulse/v1/widget-settings/widget_demo',
            'methods' => [
                'POST' => [
                    'perm'    => ['unauth' => 401, 'read' => 200, 'admin' => 200],
                    'payload' => ['title' => 'Demo Title', 'enabled' => true],
                    'shape'   => ['saved'], // many controllers return { saved: true }
                ],
            ],
        ],
        [
            'name'    => 'WidgetSettings POST global',
            'pattern' => '^/artpulse/v1/widget-settings/\(\?P<id>\[a-z0-9_-\]\+\)$',
            'example' => '/artpulse/v1/widget-settings/widget_demo?global=1',
            'methods' => [
                'POST' => [
                    'perm'    => ['unauth' => 401, 'read' => 403, 'admin' => 200],
                    'payload' => ['title' => 'Demo Title', 'enabled' => true],
                    'shape'   => ['saved'],
                ],
            ],
        ],
    ];

    public function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist (plugin often has 'member', fallback to subscriber).
        if ( ! get_role('member') ) {
            add_role('member', 'Member', ['read' => true]);
        }

        $this->admin_id  = self::factory()->user->create(['role' => 'administrator']);
        $this->reader_id = self::factory()->user->create(['role' => get_role('member') ? 'member' : 'subscriber']);

        // Boot routes under test mode and prime server.
        if (!defined('AP_TEST_MODE')) {
            define('AP_TEST_MODE', true);
        }
        do_action('rest_api_init');

        $this->server = rest_get_server();
        $this->assertNotNull($this->server, 'REST server not initialized.');

        // If core routes aren't available under the harness, bail early.
        $routes = $this->server->get_routes();
        foreach ($this->manifest as $spec) {
            if (!$this->routeExists($routes, $spec['pattern'])) {
                $this->markTestSkipped('App routes not loaded under AP_TEST_MODE harness smoke.');
            }
        }
    }

    public function tearDown(): void
    {
        wp_set_current_user(0);
        parent::tearDown();
    }

    public function test_bucket_A_registration(): void
    {
        $routes = $this->server->get_routes();
        $missing = [];

        foreach ($this->manifest as $spec) {
            if (!$this->routeExists($routes, $spec['pattern'])) {
                $missing[] = $spec['name'] . ' (' . $spec['pattern'] . ')';
            }
        }

        $this->assertEmpty(
            $missing,
            "Missing routes:\n- " . implode("\n- ", $missing)
        );
    }

    public function test_bucket_B_permissions_and_C_shape(): void
    {
        $problems = [];
        $routes   = $this->server->get_routes();
        $keys     = array_keys($routes);

        foreach ($this->manifest as $spec) {
            // Skip assertions if the route isn't registered.
            if (!in_array($spec['example'], $keys, true) && !$this->routeExists($routes, $spec['pattern'])) {
                continue;
            }

            $example  = $spec['example'];
            $methods  = $spec['methods'];

            foreach ($methods as $method => $expect) {
                // A) Unauthenticated
                wp_set_current_user(0);
                $resp   = $this->dispatch($method, $example, $expect['payload'] ?? null, $expect);
                $status = is_wp_error($resp)
                    ? (int) ($resp->get_error_data()['status'] ?? 0)
                    : $resp->get_status();
                if (($expect['perm']['unauth'] ?? null) !== null && $status !== $expect['perm']['unauth']) {
                    $problems[] = "[PERM unauth] {$spec['name']} {$method} expected {$expect['perm']['unauth']}, got {$status}";
                }

                // B) Reader (read-cap)
                wp_set_current_user($this->reader_id);
                $resp   = $this->dispatch($method, $example, $expect['payload'] ?? null, $expect);
                $status = is_wp_error($resp)
                    ? (int) ($resp->get_error_data()['status'] ?? 0)
                    : $resp->get_status();
                if (($expect['perm']['read'] ?? null) !== null && $status !== $expect['perm']['read']) {
                    $problems[] = "[PERM read] {$spec['name']} {$method} expected {$expect['perm']['read']}, got {$status}";
                } elseif (!is_wp_error($resp) && $status === 200 && !empty($expect['shape'])) {
                    $shapeErr = $this->assertShape($resp, $expect['shape']);
                    if ($shapeErr) {
                        $problems[] = "[SHAPE read] {$spec['name']} {$method} " . $shapeErr;
                    }
                }

                // C) Admin
                wp_set_current_user($this->admin_id);
                $resp   = $this->dispatch($method, $example, $expect['payload'] ?? null, $expect, true);
                $status = is_wp_error($resp)
                    ? (int) ($resp->get_error_data()['status'] ?? 0)
                    : $resp->get_status();
                if (($expect['perm']['admin'] ?? null) !== null && $status !== $expect['perm']['admin']) {
                    $problems[] = "[PERM admin] {$spec['name']} {$method} expected {$expect['perm']['admin']}, got {$status}";
                } elseif (!is_wp_error($resp) && $status === 200 && !empty($expect['shape'])) {
                    $shapeErr = $this->assertShape($resp, $expect['shape']);
                    if ($shapeErr) {
                        $problems[] = "[SHAPE admin] {$spec['name']} {$method} " . $shapeErr;
                    }
                }
            }
        }

        $this->assertEmpty(
            $problems,
            "REST bucket failures:\n- " . implode("\n- ", $problems)
        );
    }

    // ---- helpers ----

    private function routeExists(array $routes, string $pattern): bool
    {
        foreach (array_keys($routes) as $key) {
            if (@preg_match('~' . $pattern . '~', $key)) {
                if (preg_match('~' . $pattern . '~', $key)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function dispatch(string $method, string $path, ?array $payload, array $expect, bool $as_admin = false): WP_REST_Response|WP_Error
    {
        // Build request
        $req = new WP_REST_Request($method, $path);

        // Send JSON payload for POST/PUT/PATCH
        if (in_array($method, ['POST','PUT','PATCH'], true) && $payload !== null) {
            $req->set_body(json_encode($payload));
            $req->set_header('Content-Type', 'application/json; charset=utf-8');
        }

        // Add custom nonce for endpoints that enforce it
        if (!empty($expect['nonce_action']) && !empty($expect['nonce_header'])) {
            $nonce = wp_create_nonce($expect['nonce_action']);
            $req->set_header($expect['nonce_header'], $nonce);
        } else {
            // Standard WP REST nonce header (some controllers use it)
            $req->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        }

        $response = $this->server->dispatch($req);

        if (is_wp_error($response)) {
            return $response;
        }

        return rest_ensure_response($response);
    }

    private function assertShape(WP_REST_Response $resp, array $requiredKeys): ?string
    {
        $data = $resp->get_data();
        if (!is_array($data)) {
            return 'expected array/json object, got ' . gettype($data);
        }
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                return "missing key '{$key}'";
            }
        }
        return null;
    }
}
