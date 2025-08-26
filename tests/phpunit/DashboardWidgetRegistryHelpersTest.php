<?php
namespace {
    require_once __DIR__ . '/../TestStubs.php';
}

namespace ArtPulse\Core\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardWidgetRegistryHelpersTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
    }

    public function test_getById_canonicalizes_slug(): void {
        DashboardWidgetRegistry::register('widget_foo', 'Foo', '', '', static function () {});
        $def = DashboardWidgetRegistry::getById('foo');
        $this->assertIsArray($def);
        $this->assertSame('Foo', $def['label']);
    }

    public function test_exists_checks_canonical_slug(): void {
        DashboardWidgetRegistry::register('widget_bar', 'Bar', '', '', static function () {});
        $this->assertTrue(DashboardWidgetRegistry::exists('bar'));
        $this->assertTrue(DashboardWidgetRegistry::exists('widget_bar'));
        $this->assertFalse(DashboardWidgetRegistry::exists('missing'));
    }
}

}
