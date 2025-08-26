<?php
namespace ArtPulse\DashboardBuilder\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Dashboard\WidgetVisibility;

class DashboardBuilderRegistryVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
        if ($ref->hasProperty('builder_widgets')) {
            $bw = $ref->getProperty('builder_widgets');
            $bw->setAccessible(true);
            $bw->setValue(null, []);
        }
    }

    public function test_default_visibility_is_public(): void
    {
        DashboardWidgetRegistry::register('alpha', [
            'title' => 'Alpha',
            'render_callback' => '__return_null',
        ]);

        $all = DashboardWidgetRegistry::get_all(null, true);
        $this->assertSame(WidgetVisibility::PUBLIC, $all['alpha']['visibility']);
    }

    public function test_filter_by_visibility(): void
    {
        DashboardWidgetRegistry::register('a', [
            'title' => 'A',
            'render_callback' => '__return_null',
            'visibility' => WidgetVisibility::PUBLIC,
        ]);
        DashboardWidgetRegistry::register('b', [
            'title' => 'B',
            'render_callback' => '__return_null',
            'visibility' => WidgetVisibility::INTERNAL,
        ]);
        DashboardWidgetRegistry::register('c', [
            'title' => 'C',
            'render_callback' => '__return_null',
            'visibility' => WidgetVisibility::DEPRECATED,
        ]);

        $public = DashboardWidgetRegistry::get_all(WidgetVisibility::PUBLIC, true);
        $internal = DashboardWidgetRegistry::get_all(WidgetVisibility::INTERNAL, true);

        $this->assertArrayHasKey('a', $public);
        $this->assertArrayNotHasKey('b', $public);
        $this->assertArrayHasKey('b', $internal);
        $this->assertArrayNotHasKey('c', $internal);
    }
}
