<?php
namespace ArtPulse\Widgets\Placeholder;

function esc_html( $text ) { return $text; }
function apply_filters( $tag, $value, $args = null ) { return $value; }
function wp_json_encode( $data, $options = 0, $depth = 512 ) { return json_encode( $data, $options, $depth ); }

namespace ArtPulse\Widgets\Placeholder\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Widgets\Placeholder\ApPlaceholderWidget;

class ApPlaceholderWidgetTest extends TestCase
{
    protected function setUp(): void
    {
        if (!defined('ABSPATH')) {
            define('ABSPATH', __DIR__);
        }
        if (!defined('WP_DEBUG')) {
            define('WP_DEBUG', true);
        }
    }

    public function test_debug_encoded_when_non_empty(): void
    {
        ob_start();
        ApPlaceholderWidget::render(['debug' => ['foo' => 'bar']]);
        $html = ob_get_clean();
        $this->assertStringContainsString(json_encode(['foo' => 'bar']), $html);
        $this->assertStringContainsString('ap-widget__debug', $html);
    }

    public function test_debug_not_shown_when_empty(): void
    {
        ob_start();
        ApPlaceholderWidget::render(['debug' => '']);
        $html = ob_get_clean();
        $this->assertStringNotContainsString('ap-widget__debug', $html);
    }
}
