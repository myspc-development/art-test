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

    public function test_reset_invalid_member_layout(): void {
        foreach (DashboardController::get_widgets_for_role('member') as $id) {
            DashboardWidgetRegistry::register_widget($id, ['callback' => '__return_null']);
        }
        self::$users[1] = (object)['roles' => ['member']];
        self::$meta[1]['ap_dashboard_layout'] = [['id' => 'bad_widget']];

        DashboardController::reset_user_dashboard_layout(1);

        $expected = array_map(fn($id) => ['id' => $id], DashboardController::get_widgets_for_role('member'));
        $this->assertSame($expected, self::$meta[1]['ap_dashboard_layout']);
    }

    public function test_reset_invalid_artist_layout(): void {
        foreach (DashboardController::get_widgets_for_role('artist') as $id) {
            DashboardWidgetRegistry::register_widget($id, ['callback' => '__return_null']);
        }
        self::$users[2] = (object)['roles' => ['artist']];
        self::$meta[2]['ap_dashboard_layout'] = [['id' => 'other_widget']];

        DashboardController::reset_user_dashboard_layout(2);

        $expected = array_map(fn($id) => ['id' => $id], DashboardController::get_widgets_for_role('artist'));
        $this->assertSame($expected, self::$meta[2]['ap_dashboard_layout']);
    }

    public function test_reset_invalid_organization_layout(): void {
        foreach (DashboardController::get_widgets_for_role('organization') as $id) {
            DashboardWidgetRegistry::register_widget($id, ['callback' => '__return_null']);
        }
        self::$users[3] = (object)['roles' => ['organization']];
        self::$meta[3]['ap_dashboard_layout'] = [['id' => 'bad']];

        DashboardController::reset_user_dashboard_layout(3);

        $expected = array_map(fn($id) => ['id' => $id], DashboardController::get_widgets_for_role('organization'));
        $this->assertSame($expected, self::$meta[3]['ap_dashboard_layout']);
    }
}
}
