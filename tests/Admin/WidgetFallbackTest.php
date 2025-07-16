<?php
namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

class WidgetFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
    }

    public function test_missing_callback_outputs_fallback(): void
    {
        DashboardWidgetRegistry::register('foo', 'Foo', '', '', 'missing_func');
        $cb = DashboardWidgetRegistry::get_widget_callback('foo');
        ob_start();
        $cb();
        $html = ob_get_clean();
        $this->assertStringContainsString('Widget callback is missing', $html);
    }
}
