<?php
namespace ArtPulse\Admin {
    if (!defined('ABSPATH')) { define('ABSPATH', __DIR__); }
    function locate_template($t, $load = false) { return ''; }
    function load_template($t, $load = false) {}
    function esc_html__($t, $d = null) { return $t; }
    function esc_attr($t) { return $t; }
    function esc_html($t) { return $t; }
    function current_user_can($cap) { return true; }
    function wp_parse_args($args, $defaults) { return array_merge($defaults, $args); }
    function plugins_url($path, $file = null) { return $path; }
    function wp_enqueue_style() {}
    function trigger_error($msg, $level = E_USER_WARNING) { \ArtPulse\Admin\Tests\WidgetFallbackTest::$error = $msg; }
    function load_template_error() {}
}
namespace ArtPulse\Admin\Tests;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../inc/dashboard-registry.php';

class WidgetFallbackTest extends TestCase
{
    public static $error;
    protected function setUp(): void { self::$error = null; global $wp_meta_boxes; $wp_meta_boxes = ['dashboard' => ['normal' => ['core' => []]]]; }

    public function test_missing_template_outputs_fallback(): void
    {
        \ArtPulse\Admin\ap_register_dashboard_widget([
            'id' => 'foo',
            'title' => 'Foo',
            'render' => 'missing-template.php',
        ]);
        global $wp_meta_boxes;
        $cb = $wp_meta_boxes['dashboard']['normal']['core']['foo']['callback'];
        ob_start();
        $cb();
        $html = ob_get_clean();
        $this->assertStringContainsString('Widget template not found', $html);
    }
}
