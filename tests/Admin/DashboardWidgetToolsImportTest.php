<?php
namespace ArtPulse\Admin;

// Stub WordPress functions
if (!function_exists(__NAMESPACE__ . '\\current_user_can')) {
    function current_user_can($cap) {
        return \ArtPulse\Admin\Tests\DashboardWidgetToolsImportTest::$can;
    }
}
if (!function_exists(__NAMESPACE__ . '\\wp_die')) {
    function wp_die($msg = '') { \ArtPulse\Admin\Tests\DashboardWidgetToolsImportTest::$died = $msg ?: true; }
}
if (!function_exists(__NAMESPACE__ . '\\check_admin_referer')) {
    function check_admin_referer($action) {}
}
if (!function_exists(__NAMESPACE__ . '\\wp_safe_redirect')) {
    function wp_safe_redirect($url) { \ArtPulse\Admin\Tests\DashboardWidgetToolsImportTest::$redirect = $url; throw new \Exception('redirect'); }
}
if (!function_exists(__NAMESPACE__ . '\\wp_get_referer')) {
    function wp_get_referer() { return '/ref'; }
}
if (!function_exists(__NAMESPACE__ . '\\admin_url')) {
    function admin_url($path = '') { return $path; }
}
if (!function_exists(__NAMESPACE__ . '\\add_query_arg')) {
    function add_query_arg($key, $value, $base) { return $base . (str_contains($base, '?') ? '&' : '?') . $key . '=' . $value; }
}
if (!function_exists(__NAMESPACE__ . '\\update_option')) {
    function update_option($key, $value) { \ArtPulse\Admin\Tests\DashboardWidgetToolsImportTest::$options[$key] = $value; }
}
if (!function_exists(__NAMESPACE__ . '\\file_get_contents')) {
    function file_get_contents($path) { return \ArtPulse\Admin\Tests\DashboardWidgetToolsImportTest::$file_contents; }
}
if (!function_exists(__NAMESPACE__ . '\\sanitize_key')) {
    function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
}

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardWidgetToolsImportTest extends TestCase
{
    public static bool $can = true;
    public static $died = null;
    public static string $redirect = '';
    public static array $options = [];
    public static string $file_contents = '';

    protected function setUp(): void
    {
        self::$can = true;
        self::$died = null;
        self::$redirect = '';
        self::$options = [];
        self::$file_contents = '';
        $_FILES = [];
    }

    public function test_unknown_ids_are_ignored_on_import(): void
    {
        DashboardWidgetRegistry::register('foo', 'Foo', '', '', '__return_null');
        DashboardWidgetRegistry::register('bar', 'Bar', '', '', '__return_null');
        self::$file_contents = json_encode([
            'administrator' => [
                ['id' => 'foo'],
                ['id' => 'invalid'],
                ['id' => 'bar', 'visible' => false]
            ]
        ]);
        $_FILES['ap_widget_file'] = ['tmp_name' => '/tmp/test'];

        try {
            DashboardWidgetTools::handle_import();
        } catch (\Exception $e) {
            $this->assertSame('redirect', $e->getMessage());
        }

        $expected = ['administrator' => [
            ['id' => 'foo', 'visible' => true],
            ['id' => 'bar', 'visible' => false]
        ]];
        $this->assertSame($expected, self::$options['ap_dashboard_widget_config'] ?? null);
    }
}
