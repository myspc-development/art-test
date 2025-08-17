<?php
namespace ArtPulse\Admin\Tests;

require_once __DIR__ . '/../TestStubs.php';

use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;
use PHPUnit\Framework\TestCase;

class GetRoleWidgetsForCurrentUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $ref  = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
        if ($ref->hasProperty('builder_widgets')) {
            $b = $ref->getProperty('builder_widgets');
            $b->setAccessible(true);
            $b->setValue(null, []);
        }

        MockStorage::$current_roles = [];
    }

    public function test_union_of_widgets_for_all_roles(): void
    {
        DashboardWidgetRegistry::register('widget_a', 'Widget A', '', '', static function () {}, [
            'roles' => ['subscriber'],
        ]);
        DashboardWidgetRegistry::register('widget_b', 'Widget B', '', '', static function () {}, [
            'roles' => ['editor'],
        ]);
        DashboardWidgetRegistry::register('widget_common', 'Widget Common', '', '', static function () {}, [
            'roles' => ['subscriber', 'editor'],
        ]);

        MockStorage::$current_roles = ['subscriber', 'editor'];

        $widgets = DashboardWidgetTools::get_role_widgets_for_current_user();
        $ids     = array_map(fn($w) => $w['id'], $widgets);
        sort($ids);

        $this->assertSame(['widget_a', 'widget_b', 'widget_common'], $ids);
        $this->assertCount(3, $widgets);
    }
}

