<?php
namespace {
    function get_user_meta($uid, $key, $single = false) {
        return \ArtPulse\Core\Tests\DashboardControllerResetTest::$meta[$uid][$key] ?? '';
    }
    function update_user_meta($uid, $key, $value) {
        \ArtPulse\Core\Tests\DashboardControllerResetTest::$meta[$uid][$key] = $value;
    }
    function delete_user_meta($uid, $key) {
        unset(\ArtPulse\Core\Tests\DashboardControllerResetTest::$meta[$uid][$key]);
    }
    function get_userdata($uid) {
        return \ArtPulse\Core\Tests\DashboardControllerResetTest::$users[$uid] ?? null;
    }
}

namespace ArtPulse\Core\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardControllerResetTest extends TestCase {
    public static array $meta = [];
    public static array $users = [];

    protected function setUp(): void {
        self::$meta = [];
        self::$users = [];
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue([]);
    }

    public static function resetProvider(): iterable {
        yield 'member' => ['member', []];
        yield 'artist' => ['artist', []];
        yield 'organization' => ['organization', []];
    }

    /**
     * @dataProvider resetProvider
     */
    public function test_reset_invalid_layout(string $role, array $expected): void {
        foreach (DashboardController::get_widgets_for_role($role) as $id) {
            DashboardWidgetRegistry::register_widget($id, ['callback' => '__return_null']);
        }
        self::$users[1] = (object)['roles' => [$role]];
        self::$meta[1]['ap_dashboard_layout'] = [['id' => 'bad_widget']];

        DashboardController::reset_user_dashboard_layout(1);

        $this->assertSame($expected, self::$meta[1]['ap_dashboard_layout']);
    }
}
}
