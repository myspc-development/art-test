<?php
namespace ArtPulse\Rest\Tests;

class AllRoutesSecurityTest extends \WP_UnitTestCase {
    private static array $mustValidate = [
        '/ap/v1/roles',
        '/artpulse/v1/webhooks',
    ];
    /**
     * @dataProvider routesProvider
     * @group restapi
     */
    public function test_route_security(string $route, string $method, array $args): void {
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

        if (in_array($route, self::$mustValidate, true)) {
            $data = $res->get_data();
            $this->assertIsArray($data);
            $this->assertNotEmpty($data);
            $first = is_array($data) ? reset($data) : $data;
            $this->assertIsArray($first);
            $this->assertArrayHasKey('id', $first);
        }
    }

    private static function normalize_methods($m): array {
        if (is_array($m)) return array_values($m);
        $map = [
            \WP_REST_Server::READABLE   => 'GET',
            \WP_REST_Server::CREATABLE  => 'POST',
            \WP_REST_Server::EDITABLE   => 'PUT',
            \WP_REST_Server::DELETABLE  => 'DELETE',
            \WP_REST_Server::ALLMETHODS => ['GET','POST','PUT','PATCH','DELETE'],
        ];
        $out=[];
        foreach ($map as $flag=>$verb) {
            if (is_int($m) && ($m & $flag)) $out = array_merge($out, (array)$verb);
        }
        return $out ?: ['GET'];
    }

    public function routesProvider(): array {
        $out=[]; $namespaces=['/ap/v1','/artpulse/v1'];
        foreach (rest_get_server()->get_routes() as $route => $handlers) {
            if (!array_filter($namespaces, fn($ns)=>strpos($route,$ns)===0)) continue;
            foreach ($handlers as $h) {
                foreach (self::normalize_methods($h['methods'] ?? []) as $method) {
                    $args = array_map(fn($a)=>$a['default']??null, $h['args'] ?? []);
                    $out[] = [$route, $method, $args];
                }
            }
        }
        return $out;
    }
}
