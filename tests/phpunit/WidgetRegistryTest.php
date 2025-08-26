<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;

class WidgetRegistryTest extends TestCase
{
    protected function tearDown(): void
    {
        $ref  = new \ReflectionClass(WidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
        $prop = $ref->getProperty('logged_missing');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
        WidgetRegistry::resetDebug();
    }

    public function test_register_and_render(): void
    {
        WidgetRegistry::setDebug(false);
        WidgetRegistry::register('foo', static fn(array $ctx = []): string => 'hello ' . ($ctx['name'] ?? 'world'));
        $this->assertTrue(WidgetRegistry::exists('foo'));
        $this->assertSame('hello bob', WidgetRegistry::render('foo', ['name' => 'bob']));
        $this->assertContains('foo', WidgetRegistry::list());
    }

    public function test_missing_slug_returns_placeholder_with_data_slug(): void
    {
        WidgetRegistry::setDebug(true);
        $html = WidgetRegistry::render('missing');
        $this->assertStringContainsString('ap-widget--missing', $html);
       $this->assertStringContainsString('data-slug="missing"', $html);
    }

    public function test_missing_slug_returns_empty_string_when_debug_disabled(): void
    {
        WidgetRegistry::setDebug(false);
        $html = WidgetRegistry::render('missing');
        $this->assertSame('', $html);
    }
}

