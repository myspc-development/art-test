<?php
namespace ArtPulse\Rest;

// --- WordPress stubs ---
function get_current_user_id() {
    return \ArtPulse\Rest\Tests\OrgRolesControllerTest::$current_user_id;
}
function get_user_meta(int $user_id, string $key, bool $single = false) {
    return \ArtPulse\Rest\Tests\OrgRolesControllerTest::$user_meta[$user_id][$key] ?? '';
}
function rest_ensure_response($data) { return $data; }
function check_ajax_referer($action, $name) {}
function wp_send_json_success($data) { \ArtPulse\Rest\Tests\OrgRolesControllerTest::$json_success = $data; }
function wp_send_json_error($data) { \ArtPulse\Rest\Tests\OrgRolesControllerTest::$json_error = $data; }

class WP_REST_Request {
    private array $params;
    public function __construct(array $params = []) { $this->params = $params; }
    public function get_param(string $key) { return $this->params[$key] ?? null; }
}

namespace ArtPulse\Core;
class OrgRoleManager {
    public static array $roles = [];
    public static array $received = [];
    public static function get_roles(int $org_id): array {
        self::$received[] = $org_id;
        return self::$roles;
    }
}

namespace ArtPulse\Rest\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Rest\OrgRolesController;
use ArtPulse\Rest\WP_REST_Request;
use ArtPulse\Core\OrgRoleManager;

class OrgRolesControllerTest extends TestCase {
    public static int $current_user_id = 1;
    public static array $user_meta = [];
    public static $json_success = null;
    public static $json_error = null;

    protected function setUp(): void {
        self::$current_user_id = 1;
        self::$user_meta = [];
        self::$json_success = null;
        self::$json_error = null;
        OrgRoleManager::$roles = ['viewer' => ['name' => 'Viewer', 'caps' => []]];
        OrgRoleManager::$received = [];
        $_POST = [];
    }

    public function test_get_roles_defaults_to_user_org(): void {
        self::$user_meta[1]['ap_organization_id'] = 10;
        $req = new WP_REST_Request();
        $res = OrgRolesController::get_roles($req);
        $expected = [
            [
                'key'         => 'viewer',
                'label'       => 'Viewer',
                'description' => '',
                'user_count'  => 0,
            ],
        ];
        $this->assertSame($expected, $res);
        $this->assertSame([10], OrgRoleManager::$received);
    }

    public function test_get_roles_uses_request_org(): void {
        self::$user_meta[1]['ap_organization_id'] = 10;
        $req = new WP_REST_Request(['org_id' => 5]);
        $res = OrgRolesController::get_roles($req);
        $expected = [
            [
                'key'         => 'viewer',
                'label'       => 'Viewer',
                'description' => '',
                'user_count'  => 0,
            ],
        ];
        $this->assertSame($expected, $res);
        $this->assertSame([5], OrgRoleManager::$received);
    }

    public function test_ajax_get_roles_returns_roles(): void {
        self::$user_meta[1]['ap_organization_id'] = 7;
        $_POST = ['nonce' => 'n'];
        OrgRolesController::ajax_get_roles();
        $expected = [
            [
                'key'         => 'viewer',
                'label'       => 'Viewer',
                'description' => '',
                'user_count'  => 0,
            ],
        ];
        $this->assertSame($expected, self::$json_success);
        $this->assertSame([7], OrgRoleManager::$received);
    }

    public function test_ajax_get_roles_uses_post_org_id(): void {
        $_POST = ['nonce' => 'n', 'org_id' => 3];
        OrgRolesController::ajax_get_roles();
        $expected = [
            [
                'key'         => 'viewer',
                'label'       => 'Viewer',
                'description' => '',
                'user_count'  => 0,
            ],
        ];
        $this->assertSame($expected, self::$json_success);
        $this->assertSame([3], OrgRoleManager::$received);
    }
}

