<?php
namespace ArtPulse\Rest\Tests;

class AllRoutesSecurityTest extends \WP_UnitTestCase {
    /**
     * @dataProvider routesProvider
     * @group restapi
     */
    public function test_route_security(string $route, string $method, array $args, $schema): void {
        $server = rest_get_server();

        // 401: unauthenticated request
        $res = $server->dispatch(new \WP_REST_Request($method, $route));
        $this->assertEquals(401, $res->get_status(), "Unauthenticated access to $method $route should return 401");

        // 403 or 401: authenticated without required capabilities
        wp_set_current_user(self::factory()->user->create(['role' => 'subscriber']));
        $res = $server->dispatch(new \WP_REST_Request($method, $route));
        $this->assertContains($res->get_status(), [401, 403], "Subscriber access to $method $route should be 401/403");

        // 2xx: authenticated as administrator
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        $req = new \WP_REST_Request($method, $route);
        foreach ($args as $k => $v) {
            if ($v !== null) {
                $req->set_param($k, $v);
            }
        }
        $res = $server->dispatch($req);
        $status = $res->get_status();
        $this->assertTrue($status >= 200 && $status < 300, "Admin access to $method $route should be 2xx, got $status");

        if ($schema) {
            $valid = rest_validate_value_from_schema($res->get_data(), $schema, 'response');
            $this->assertTrue(!is_wp_error($valid), 'Response does not match schema');
        }
    }

    public function routesProvider(): array {
        $out = [];
        $ns = '/ap/v1';
        $server = rest_get_server();
        foreach ($server->get_routes() as $route => $handlers) {
            if (strpos($route, $ns) !== 0) {
                continue;
            }
            foreach ($handlers as $handler) {
                $args = [];
                foreach ($handler['args'] ?? [] as $k => $arg) {
                    if (!empty($arg['required']) && !array_key_exists('default', $arg)) {
                        continue 2; // skip handler with required arg without default
                    }
                    $args[$k] = $arg['default'] ?? null;
                }
                $methods = $handler['methods'] ?? 'GET';
                if (is_array($methods)) {
                    $method = is_string(key($methods)) ? key($methods) : reset($methods);
                } else {
                    $method = $methods;
                }
                if (is_string($method) && strpos($method, ',') !== false) {
                    $method = explode(',', $method)[0];
                }
                if (is_string($method) && strpos($method, '|') !== false) {
                    $method = explode('|', $method)[0];
                }
                $out[] = [$route, $method, $args, $handler['schema'] ?? null];
            }
        }
        return $out;
    }
}
