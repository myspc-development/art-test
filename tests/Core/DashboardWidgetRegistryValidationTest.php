<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardWidgetRegistryValidationTest extends TestCase
{
    protected function setUp(): void
    {
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
    }

    public function test_duplicate_ids_or_labels_not_registered(): void
    {
        DashboardWidgetRegistry::register('dup', 'Foo', '', '', '__return_null');
        DashboardWidgetRegistry::register('dup', 'Bar', '', '', '__return_null');
        DashboardWidgetRegistry::register('unique', 'Foo', '', '', '__return_null');

        $defs = DashboardWidgetRegistry::get_definitions();

        $this->assertCount(2, $defs);
        $this->assertArrayHasKey('dup', $defs);
        $this->assertArrayHasKey('unique', $defs);
    }
}
