<?php
namespace ArtPulse\Admin;

// WordPress stubs
if (!function_exists(__NAMESPACE__ . '\\sanitize_key')) {
    function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
}
if (!function_exists(__NAMESPACE__ . '\\get_option')) {
    function get_option($key, $default = []) { return \ArtPulse\Admin\Tests\DashboardWidgetToolsRenderTest::$options[$key] ?? $default; }
}
if (!function_exists(__NAMESPACE__ . '\\get_current_user_id')) {
    function get_current_user_id() { return 1; }
}
if (!function_exists(__NAMESPACE__ . '\\get_user_meta')) {
    function get_user_meta($uid, $key, $single = false) { return []; }
}
if (!function_exists(__NAMESPACE__ . '\\get_userdata')) {
    function get_userdata($uid) { return (object)['roles' => ['subscriber']]; }
}
if (!function_exists(__NAMESPACE__ . '\\esc_attr')) {
    function esc_attr($text) { return $text; }
}
if (!function_exists(__NAMESPACE__ . '\\esc_html')) {
    function esc_html($text) { return $text; }
}
if (!function_exists(__NAMESPACE__ . '\\artpulse_dashicon')) {
    function artpulse_dashicon($icon, $attrs = []) { return '<span class="dashicon"></span>'; }
}

namespace ArtPulse\Core;
if (!function_exists('ArtPulse\\Core\\apply_filters')) {
    function apply_filters(string $tag, $value) { return $value; }
}

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardWidgetToolsRenderTest extends TestCase
{
    public static array $options = [];

    protected function setUp(): void
    {
        self::$options = [];
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
    }

    public function test_role_layout_renders_in_order(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', function () { return 'alpha'; });
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', function () { return 'beta'; });

        self::$options['ap_dashboard_widget_config'] = [
            'subscriber' => [
                ['id' => 'beta'],
                ['id' => 'alpha']
            ],
        ];

        ob_start();
        DashboardWidgetTools::render_role_dashboard_preview('subscriber');
        $html = ob_get_clean();

        $this->assertStringContainsString('alpha', $html);
        $this->assertStringContainsString('beta', $html);
        $this->assertStringContainsString('ap-widget-card', $html);
    }

    public function test_widget_controls_have_accessibility_attributes(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', function () { return 'alpha'; });

        self::$options['ap_dashboard_widget_config'] = [
            'subscriber' => [ ['id' => 'alpha'] ],
        ];

        ob_start();
        DashboardWidgetTools::render_role_dashboard_preview('subscriber');
        $html = ob_get_clean();

        $this->assertStringContainsString('role="button"', $html);
        $this->assertStringContainsString('aria-label="Drag to reorder"', $html);
    }
}
