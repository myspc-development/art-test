<?php
namespace ArtPulse\Core\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

class GetUserDashboardLayoutTest extends TestCase
{
    protected function setUp(): void
    {
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
    }

    public static function layoutProvider(): iterable
    {
        yield 'member' => ['member', [['id' => 'alpha']]];
        yield 'artist' => ['artist', [['id' => 'beta']]];
        yield 'organization' => ['organization', [['id' => 'gamma']]];
        yield 'invalid role' => ['invalid', []];
    }

    /**
     * @dataProvider layoutProvider
     */
    public function test_get_user_dashboard_layout(string $role, array $expected): void
    {
        MockStorage::$users[1] = (object)['roles' => [$role]];
        $layout = DashboardController::get_user_dashboard_layout(1);
        $this->assertSame($expected, $layout);
    }
}
}


