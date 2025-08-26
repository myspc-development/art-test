<?php
namespace ArtPulse\Core\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

class DashboardControllerResetTest extends TestCase {

    protected function setUp(): void {
        MockStorage::$user_meta = [];
        MockStorage::$users = [];
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue(null, []);
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
        MockStorage::$users[1] = (object)['roles' => [$role]];
        MockStorage::$user_meta[1]['ap_dashboard_layout'] = [['id' => 'bad_widget']];

        DashboardController::reset_user_dashboard_layout(1);

        $this->assertSame($expected, MockStorage::$user_meta[1]['ap_dashboard_layout']);
    }
}
}
