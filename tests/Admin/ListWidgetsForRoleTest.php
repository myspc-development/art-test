<?php
namespace ArtPulse\Admin\Tests;

use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Core\DashboardWidgetRegistry;

class ListWidgetsForRoleTest extends \WP_UnitTestCase
{
    protected function set_up(): void
    {
        parent::set_up();
        $ref  = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
        if ($ref->hasProperty('builder_widgets')) {
            $b = $ref->getProperty('builder_widgets');
            $b->setAccessible(true);
            $b->setValue([]);
        }
    }

    public function test_widget_without_callback_is_disabled(): void
    {
        DashboardWidgetRegistry::register('foo', [
            'title' => 'Foo',
            'render_callback' => '__return_null',
            'roles' => ['administrator'],
        ]);
        DashboardWidgetRegistry::register('bar', [
            'title' => 'Bar',
            'roles' => ['administrator'],
        ]);

        $widgets = DashboardWidgetTools::listWidgetsForRole('administrator');
        $map = [];
        foreach ($widgets as $w) {
            $map[$w['id']] = $w;
        }

        $this->assertArrayHasKey('bar', $map);
        $this->assertTrue($map['bar']['disabled']);
        $this->assertSame('no_renderer', $map['bar']['disabled_reason']);
        $this->assertFalse($map['foo']['disabled']);
    }
}

