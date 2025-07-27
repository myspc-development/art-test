<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\WidgetRoleSync;
use ArtPulse\Core\DashboardController;

class WidgetRoleSyncTest extends TestCase
{
    protected function setUp(): void
    {
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);

        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue([]);
    }

    public function test_roles_inferred_for_missing_widget(): void
    {
        DashboardWidgetRegistry::register_widget('artist_portfolio_widget', [
            'label' => 'Portfolio',
            'callback' => '__return_null'
        ]);

        WidgetRoleSync::sync();

        $defs = DashboardWidgetRegistry::get_all();
        $this->assertSame(['artist'], $defs['artist_portfolio_widget']['roles']);
    }
}
