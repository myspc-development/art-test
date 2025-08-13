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

    public function test_admin_defaults_to_organization(): void {
        MockStorage::$users[2] = (object)['roles' => ['administrator']];
        $this->assertSame('organization', RoleResolver::resolve(2));
    }

    public function test_admin_preview_overrides_role(): void {
        MockStorage::$users[3] = (object)['roles' => ['administrator']];
        MockStorage::$current_roles = ['manage_options'];
        $_GET['ap_preview_role'] = 'artist';
        $this->assertSame('artist', RoleResolver::resolve(3));
        unset($_GET['ap_preview_role']);
    }

    public function test_invalid_preview_is_ignored(): void {
        MockStorage::$users[4] = (object)['roles' => ['administrator']];
        MockStorage::$current_roles = ['manage_options'];
        $_GET['ap_preview_role'] = 'invalid';
        $this->assertSame('organization', RoleResolver::resolve(4));
        unset($_GET['ap_preview_role']);
    }
}
}
