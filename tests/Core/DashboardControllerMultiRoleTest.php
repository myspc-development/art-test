<?php
namespace {
    if (!defined('ARTPULSE_PLUGIN_FILE')) {
        define('ARTPULSE_PLUGIN_FILE', __DIR__ . '/../../artpulse.php');
    }
    function get_user_meta($uid, $key, $single = false) { return ''; }
    function get_option($key, $default = []) { return $default; }
    function get_userdata($uid) { return \ArtPulse\Core\Tests\DashboardControllerMultiRoleTest::$users[$uid] ?? null; }
    function current_user_can($cap) { return true; }
}

namespace ArtPulse\Core\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardControllerMultiRoleTest extends TestCase {
    public static array $users = [];

    protected function setUp(): void {
        self::$users = [];

        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);

        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue([
            'member'       => ['alpha'],
            'artist'       => ['beta'],
            'organization' => ['gamma'],
        ]);

        DashboardWidgetRegistry::register_widget('alpha', [
            'label'    => 'Alpha',
            'callback' => '__return_null',
            'roles'    => ['member'],
        ]);
        DashboardWidgetRegistry::register_widget('beta', [
            'label'    => 'Beta',
            'callback' => '__return_null',
            'roles'    => ['artist'],
        ]);
        DashboardWidgetRegistry::register_widget('gamma', [
            'label'    => 'Gamma',
            'callback' => '__return_null',
            'roles'    => ['organization'],
        ]);
        $_GET = [];
    }

    public function test_member_priority_over_artist(): void {
        self::$users[1] = (object)['roles' => ['artist', 'member']];
        $layout = DashboardController::get_user_dashboard_layout(1);
        $this->assertSame([['id' => 'alpha']], $layout);
    }

    public function test_artist_priority_over_organization(): void {
        self::$users[2] = (object)['roles' => ['organization', 'artist']];
        $layout = DashboardController::get_user_dashboard_layout(2);
        $this->assertSame([['id' => 'beta']], $layout);
    }

    public function test_preview_role_override(): void {
        $_GET['ap_preview_role'] = 'organization';
        self::$users[3] = (object)['roles' => ['member', 'artist']];
        $layout = DashboardController::get_user_dashboard_layout(3);
        $this->assertSame([['id' => 'gamma']], $layout);
    }
}
}
