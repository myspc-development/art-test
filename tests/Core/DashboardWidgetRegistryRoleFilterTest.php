<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardWidgetRegistryRoleFilterTest extends TestCase
{
    protected function setUp(): void
    {
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
    }

    public function test_get_widgets_filters_by_role(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', '__return_null', ['roles' => ['member']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', '__return_null', ['roles' => ['administrator']]);
        $member = DashboardWidgetRegistry::get_widgets('member');
        $admin = DashboardWidgetRegistry::get_widgets('administrator');

        $this->assertArrayHasKey('alpha', $member);
        $this->assertArrayNotHasKey('beta', $member);
        $this->assertArrayHasKey('beta', $admin);
    }

    public function test_get_widgets_combines_roles(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', '__return_null', ['roles' => ['member']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', '__return_null', ['roles' => ['artist']]);

        $combined = DashboardWidgetRegistry::get_widgets(['member', 'artist']);

        $this->assertArrayHasKey('alpha', $combined);
        $this->assertArrayHasKey('beta', $combined);
    }
}
