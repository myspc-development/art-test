<?php
namespace ArtPulse\Core\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

class DashboardControllerMultiRoleTest extends TestCase {
    protected function setUp(): void {
        MockStorage::$users = [];

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

    protected function tearDown(): void {
        $_GET = [];
        MockStorage::$users = [];
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
        parent::tearDown();
    }

    public function test_member_priority_over_artist(): void {
        MockStorage::$users[1] = (object)['roles' => ['artist', 'member']];
        $layout = DashboardController::get_user_dashboard_layout(1);
        $this->assertSame([['id' => 'alpha']], $layout);
    }

    public function test_artist_priority_over_organization(): void {
        MockStorage::$users[2] = (object)['roles' => ['organization', 'artist']];
        $layout = DashboardController::get_user_dashboard_layout(2);
        $this->assertSame([['id' => 'beta']], $layout);
    }

    public function test_preview_role_override(): void {
        $_GET['ap_preview_role'] = 'organization';
        MockStorage::$users[3] = (object)['roles' => ['member', 'artist']];
        $layout = DashboardController::get_user_dashboard_layout(3);
        $this->assertSame([['id' => 'gamma']], $layout);
    }
}
}
