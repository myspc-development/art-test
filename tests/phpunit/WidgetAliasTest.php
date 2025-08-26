<?php
use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;

final class WidgetAliasTest extends TestCase {
    protected function setUp(): void {
        // ensure widgets+aliases registered for the test
        do_action('init');
    }

    public function test_membership_alias_renders_same_as_canonical(): void {
        $a = WidgetRegistry::render('membership');
        $b = WidgetRegistry::render('widget_membership');
        $this->assertIsString($a);
        $this->assertIsString($b);
        $this->assertNotSame('', $a);
        $this->assertStringContainsString('<section', $a);
        $this->assertStringContainsString('<section', $b);
    }
}
