<?php
use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;

final class WidgetAliasTest extends TestCase {
    protected function setUp(): void {
        // ensure widgets+aliases registered for the test
        do_action('init');
        WidgetRegistry::register('widget_membership', static fn(array $ctx = []): string => '<div>membership</div>');
        WidgetRegistry::register('widget_my_follows', static fn(array $ctx = []): string => '<div>follows</div>');
    }

    /**
     * @dataProvider aliasProvider
     */
    public function test_legacy_aliases_canonicalize_and_render(string $legacy, string $expected): void {
        $this->assertSame($expected, WidgetRegistry::normalize_slug($legacy));
        $this->assertTrue(WidgetRegistry::exists($expected));
        $html = WidgetRegistry::render($legacy);
        $this->assertIsString($html);
        $this->assertNotSame('', trim($html));
    }

    public function aliasProvider(): array {
        return [
            ['membership',              'widget_membership'],
            ['followed_artists',        'widget_my_follows'],
            ['widget_followed_artists', 'widget_my_follows'],
        ];
    }
}
