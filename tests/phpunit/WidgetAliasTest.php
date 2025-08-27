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
        $register = static function (string $slug): void {
            WidgetRegistry::register($slug, static fn(array $ctx = []): string => '<section data-slug="' . $slug . '">ok</section>');
        };
        $register('widget_membership');
        $register('widget_my_events');
        $register('widget_account_tools');
        $register('widget_site_stats');
        $register('widget_recommended_for_you');
        $register('widget_local_events');
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
            ['my-events',               'widget_my_events'],
            ['account-tools',           'widget_account_tools'],
            ['site_stats',              'widget_site_stats'],
            ['followed_artists',        'widget_my_follows'],
            ['widget_followed_artists', 'widget_my_follows'],
            ['recommended_for_you',     'widget_recommended_for_you'],
            ['local_events',            'widget_local_events'],
        ];
    }

    public function test_canonical_slug_renders_template(): void {
        $html = WidgetRegistry::render('widget_my_follows');
        $this->assertIsString($html);
        $this->assertNotSame('', trim($html));
        $this->assertStringContainsString('<div', $html);
    }
}
