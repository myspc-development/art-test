<?php
require_once __DIR__ . '/../TestStubs.php';
if (!function_exists('esc_html_e')) {
    function esc_html_e($text, $domain = null) { echo $text; }
}
if (!function_exists('_e')) {
    function _e($text, $domain = null) { echo $text; }
}

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

final class WidgetAliasTest extends TestCase {
    protected function setUp(): void {
        // ensure widgets+aliases registered for the test
        do_action('init');
        MockStorage::$current_roles = ['read'];
        WidgetRegistry::register('widget_membership', static fn(array $ctx = []): string => '<div>membership</div>');
        WidgetRegistry::register('widget_my_follows', static function(array $ctx = []): string {
            ob_start();
            require __DIR__ . '/../../templates/widgets/widget-my-follows.php';
            return ob_get_clean();
        });
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

    public function test_canonical_slug_renders_template(): void {
        $html = WidgetRegistry::render('widget_my_follows');
        $this->assertIsString($html);
        $this->assertNotSame('', trim($html));
        $this->assertStringContainsString('<div', $html);
    }
}
