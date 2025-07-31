<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardWidgetRegistryCallbacksTest extends TestCase
{
    protected function setUp(): void
    {
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
    }

    public function test_member_widgets_return_callable_callbacks(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', '__return_null', ['roles' => ['member']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', static function () {}, ['roles' => ['member']]);

        $widgets = DashboardWidgetRegistry::get_widgets('member');

        $this->assertNotEmpty($widgets);
        foreach ($widgets as $cb) {
            $this->assertTrue(is_callable($cb));
        }
    }
}
