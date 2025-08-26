<?php
namespace {
    require_once __DIR__ . '/../TestStubs.php';
}

namespace ArtPulse\Core\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

class DashboardPreviewUserTest extends TestCase {
    protected function setUp(): void {
        MockStorage::$users = [];
        MockStorage::$current_roles = ['manage_options'];

        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);

        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue(null, [
            'member' => ['alpha'],
            'artist' => ['beta'],
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

        $_GET = [];
    }

    protected function tearDown(): void {
        $_GET = [];
        MockStorage::$users = [];
        MockStorage::$current_roles = [];
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
        parent::tearDown();
    }

    public function test_preview_user_parameter_overrides_user(): void {
        MockStorage::$users[1] = (object)['roles' => ['member']];
        MockStorage::$users[2] = (object)['roles' => ['artist']];

        $layoutDefault = DashboardController::get_user_dashboard_layout(1);
        $this->assertSame([['id' => 'alpha']], $layoutDefault);

        $_GET['ap_preview_user'] = '2';
        $layoutPreview = DashboardController::get_user_dashboard_layout(1);
        $this->assertSame([['id' => 'beta']], $layoutPreview);
    }
}
}
