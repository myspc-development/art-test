<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\OrgRoleManager;

function get_user_meta($uid, $key, $single = false) {
    return OrgRoleManagerTest::$meta[$uid][$key] ?? '';
}
function get_post_meta($pid, $key, $single = false) {
    return OrgRoleManagerTest::$post_meta[$pid][$key] ?? '';
}
function get_current_user_id() {
    return OrgRoleManagerTest::$current_id;
}

class OrgRoleManagerTest extends TestCase
{
    public static array $meta = [];
    public static array $post_meta = [];
    public static int $current_id = 1;

    protected function setUp(): void
    {
        self::$meta = [];
        self::$post_meta = [];
        self::$current_id = 1;
    }

    public function test_current_user_can_checks_role(): void
    {
        self::$meta[1]['ap_organization_id'] = 10;
        self::$meta[1]['ap_org_role'] = 'event_manager';
        $this->assertTrue(OrgRoleManager::current_user_can('manage_events', 10));
        $this->assertFalse(OrgRoleManager::current_user_can('manage_users', 10));
    }

    public function test_user_can_with_custom_roles(): void
    {
        self::$meta[1]['ap_organization_id'] = 5;
        self::$meta[1]['ap_org_roles'] = ['finance_manager'];
        self::$post_meta[5]['ap_org_roles'] = [
            'finance_manager' => ['name' => 'Finance Manager', 'caps' => ['manage_finances', 'view_finance']],
        ];
        $this->assertTrue(OrgRoleManager::user_can(1, 5, 'manage_finances'));
        $this->assertFalse(OrgRoleManager::user_can(1, 5, 'manage_users'));
    }
}
