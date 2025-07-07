<?php
namespace ArtPulse\Admin;

// WordPress function stubs
function current_user_can($cap) { return \ArtPulse\Admin\Tests\DashboardWidgetToolsTest::$can; }
function wp_die($msg = '') { \ArtPulse\Admin\Tests\DashboardWidgetToolsTest::$died = $msg ?: true; }
function check_admin_referer($action) {}
function get_option($key, $default = false) { return \ArtPulse\Admin\Tests\DashboardWidgetToolsTest::$options[$key] ?? $default; }
function update_option($key, $value) { \ArtPulse\Admin\Tests\DashboardWidgetToolsTest::$options[$key] = $value; }
function wp_safe_redirect($url) { \ArtPulse\Admin\Tests\DashboardWidgetToolsTest::$redirect = $url; throw new \Exception('redirect'); }
function add_query_arg($key, $value, $url) { return \ArtPulse\Admin\Tests\DashboardWidgetToolsTest::addQueryArg($key, $value, $url); }
function admin_url($path = '') { return $path; }
function wp_get_referer() { return null; }
function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\DashboardWidgetTools;

class DashboardWidgetToolsTest extends TestCase
{
    public static bool $can = true;
    public static bool $died = false;
    public static array $options = [];
    public static string $redirect = '';

    protected function setUp(): void
    {
        self::$can = true;
        self::$died = false;
        self::$options = [];
        self::$redirect = '';
        $_FILES = [];
    }

    public static function addQueryArg($key, $value, $url)
    {
        $sep = strpos($url, '?') === false ? '?' : '&';
        return $url . $sep . $key . '=' . $value;
    }

    /**
     * @runInSeparateProcess
     */
    public function test_handle_export_outputs_json_and_exits_when_allowed(): void
    {
        self::$options['ap_dashboard_widget_config'] = ['admin' => ['quick']];
        $expected = json_encode(['admin' => ['quick']], JSON_PRETTY_PRINT);
        $this->expectOutputString($expected);
        DashboardWidgetTools::handle_export();
    }

    public function test_handle_export_permission_denied(): void
    {
        self::$can = false;
        DashboardWidgetTools::handle_export();
        $this->assertTrue(self::$died);
    }

    public function test_handle_import_saves_sanitized_layout(): void
    {
        $data = ['Admin<script>' => ['One', 'Two<script>']];
        $tmp = tempnam(sys_get_temp_dir(), 'dw');
        file_put_contents($tmp, json_encode($data));
        $_FILES['ap_widget_file']['tmp_name'] = $tmp;
        try {
            DashboardWidgetTools::handle_import();
        } catch (\Exception $e) {
            $this->assertSame('redirect', $e->getMessage());
        }
        $expected = ['adminscript' => ['one', 'twoscript']];
        $this->assertSame($expected, self::$options['ap_dashboard_widget_config']);
        $this->assertStringContainsString('dw_import_success', self::$redirect);
    }

    public function test_handle_import_invalid_json_redirects(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'dw');
        file_put_contents($tmp, '{invalid');
        $_FILES['ap_widget_file']['tmp_name'] = $tmp;
        try {
            DashboardWidgetTools::handle_import();
        } catch (\Exception $e) {
            $this->assertSame('redirect', $e->getMessage());
        }
        $this->assertArrayNotHasKey('ap_dashboard_widget_config', self::$options);
        $this->assertStringContainsString('dw_import_error', self::$redirect);
    }

    public function test_handle_import_permission_denied(): void
    {
        self::$can = false;
        DashboardWidgetTools::handle_import();
        $this->assertTrue(self::$died);
    }
}
