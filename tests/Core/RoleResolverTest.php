<?php
namespace {
    require_once __DIR__ . '/../TestStubs.php';
}

namespace ArtPulse\Core\Tests {
use PHPUnit\Framework\TestCase;
use ArtPulse\Core\RoleResolver;
use ArtPulse\Tests\Stubs\MockStorage;

class RoleResolverTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        MockStorage::$users = [];
        MockStorage::$current_roles = [];
    }

    public function test_maps_wp_roles_to_member(): void {
        MockStorage::$users[1] = (object)['roles' => ['subscriber']];
        $this->assertSame('member', RoleResolver::resolve(1));
    }

    public function test_preview_role_maps_to_member(): void {
        MockStorage::$users[2] = (object)['roles' => ['administrator']];
        MockStorage::$current_roles = ['manage_options'];
        $_GET['ap_preview_role'] = 'subscriber';
        $this->assertSame('member', RoleResolver::resolve(2));
        unset($_GET['ap_preview_role']);
    }
}
}
