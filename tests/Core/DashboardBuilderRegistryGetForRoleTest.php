<?php
namespace ArtPulse\DashboardBuilder\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardBuilderRegistryGetForRoleTest extends TestCase
{
    protected function setUp(): void
    {
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
        if ($ref->hasProperty('builder_widgets')) {
            $bw = $ref->getProperty('builder_widgets');
            $bw->setAccessible(true);
            $bw->setValue([]);
        }
    }

    public function test_get_for_role_requires_explicit_match(): void
    {
        DashboardWidgetRegistry::register('alpha', [
            'title' => 'Alpha',
            'render_callback' => '__return_null',
            'roles' => ['member']
        ]);
        DashboardWidgetRegistry::register('beta', [
            'title' => 'Beta',
            'render_callback' => '__return_null',
            'roles' => ['artist']
        ]);
        DashboardWidgetRegistry::register('unassigned', [
            'title' => 'Unassigned',
            'render_callback' => '__return_null'
        ]);

        $member = DashboardWidgetRegistry::get_for_role('member');
        $artist = DashboardWidgetRegistry::get_for_role('artist');

        $this->assertArrayHasKey('alpha', $member);
        $this->assertArrayNotHasKey('beta', $member);
        $this->assertArrayNotHasKey('unassigned', $member);

        $this->assertArrayHasKey('beta', $artist);
        $this->assertArrayNotHasKey('alpha', $artist);
        $this->assertArrayNotHasKey('unassigned', $artist);
    }
}
