<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

class DashboardLayoutRoleFilterTest extends TestCase
{
    protected function setUp(): void
    {
        MockStorage::$user_meta = [];
        MockStorage::$options = [];
        MockStorage::$users = [];

        // Reset registry widgets
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);

        // Reset role widgets map
        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue(null, []);
    }

    public function test_user_meta_layout_is_filtered_by_role(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', null, ['roles' => ['member']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', null, ['roles' => ['artist']]);

        MockStorage::$users[1] = (object)['roles' => ['member']];
        MockStorage::$user_meta[1]['ap_dashboard_layout'] = [
            ['id' => 'alpha'],
            ['id' => 'beta'],
            ['id' => 'unknown'],
        ];

        $layout = DashboardController::get_user_dashboard_layout(1);
        $this->assertSame([['id' => 'alpha']], $layout);
    }

    public function test_option_layout_is_filtered_by_role(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', null, ['roles' => ['member']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', null, ['roles' => ['artist']]);

        MockStorage::$users[2] = (object)['roles' => ['member']];
        MockStorage::$options['ap_dashboard_widget_config'] = [
            'member' => [
                ['id' => 'alpha'],
                ['id' => 'beta'],
            ],
        ];

        $layout = DashboardController::get_user_dashboard_layout(2);
        $this->assertSame([['id' => 'alpha']], $layout);
    }

    public function test_default_layout_is_filtered_by_role_widgets(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', null, ['roles' => ['member']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', null, ['roles' => ['artist']]);

        $ref = new \ReflectionClass(DashboardController::class);
        $prop = $ref->getProperty('role_widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, [
            'member' => ['alpha', 'beta'],
        ]);

        MockStorage::$users[3] = (object)['roles' => ['member']];

        $layout = DashboardController::get_user_dashboard_layout(3);
        $this->assertSame([['id' => 'alpha']], $layout);
    }

    public function test_user_meta_layout_includes_widgets_without_roles(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', null, ['roles' => ['artist']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', null);

        MockStorage::$users[4] = (object)['roles' => ['member']];
        MockStorage::$user_meta[4]['ap_dashboard_layout'] = [
            ['id' => 'alpha'],
            ['id' => 'beta'],
        ];

        $layout = DashboardController::get_user_dashboard_layout(4);
        $this->assertSame([['id' => 'beta']], $layout);
    }

    public function test_option_layout_includes_widgets_without_roles(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', null, ['roles' => ['artist']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', null);

        MockStorage::$users[5] = (object)['roles' => ['member']];
        MockStorage::$options['ap_dashboard_widget_config'] = [
            'member' => [
                ['id' => 'alpha'],
                ['id' => 'beta'],
            ],
        ];

        $layout = DashboardController::get_user_dashboard_layout(5);
        $this->assertSame([['id' => 'beta']], $layout);
    }

    public function test_default_layout_includes_widgets_without_roles(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', null, ['roles' => ['artist']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', null);

        $ref = new \ReflectionClass(DashboardController::class);
        $prop = $ref->getProperty('role_widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, [
            'member' => ['alpha', 'beta'],
        ]);

        MockStorage::$users[6] = (object)['roles' => ['member']];

        $layout = DashboardController::get_user_dashboard_layout(6);
        $this->assertSame([['id' => 'beta']], $layout);
    }
}
