<?php
namespace ArtPulse\DashboardBuilder\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardBuilderRegistryVisibilityTest extends TestCase
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

    public function test_default_visibility_is_public(): void
    {
        DashboardWidgetRegistry::register('alpha', [
            'title' => 'Alpha',
            'render_callback' => '__return_null',
        ]);

        $all = DashboardWidgetRegistry::get_all(null, true);
        $this->assertSame('public', $all['alpha']['visibility']);
    }

    public function test_filter_by_visibility(): void
    {
        DashboardWidgetRegistry::register('a', [
            'title' => 'A',
            'render_callback' => '__return_null',
            'visibility' => 'public',
        ]);
        DashboardWidgetRegistry::register('b', [
            'title' => 'B',
            'render_callback' => '__return_null',
            'visibility' => 'internal',
        ]);
        DashboardWidgetRegistry::register('c', [
            'title' => 'C',
            'render_callback' => '__return_null',
            'visibility' => 'deprecated',
        ]);

        $public = DashboardWidgetRegistry::get_all('public', true);
        $internal = DashboardWidgetRegistry::get_all('internal', true);

        $this->assertArrayHasKey('a', $public);
        $this->assertArrayNotHasKey('b', $public);
        $this->assertArrayHasKey('b', $internal);
        $this->assertArrayNotHasKey('c', $internal);
    }
}
