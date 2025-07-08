<?php
namespace ArtPulse\Admin;

// Stub WordPress functions
function current_user_can($cap) { return \ArtPulse\Admin\Tests\DashboardWidgetToolsImportTest::$can; }
function wp_die($msg = '') { \ArtPulse\Admin\Tests\DashboardWidgetToolsImportTest::$died = $msg ?: true; }
function check_admin_referer($action) {}
function wp_safe_redirect($url) { \ArtPulse\Admin\Tests\DashboardWidgetToolsImportTest::$redirect = $url; throw new \Exception('redirect'); }
function wp_get_referer() { return '/ref'; }
function admin_url($path = '') { return $path; }
function add_query_arg($key, $value, $base) { return $base . (str_contains($base, '?') ? '&' : '?') . $key . '=' . $value; }
function update_option($key, $value) { \ArtPulse\Admin\Tests\DashboardWidgetToolsImportTest::$options[$key] = $value; }
function file_get_contents($path) { return \ArtPulse\Admin\Tests\DashboardWidgetToolsImportTest::$file_contents; }
function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
function __($text, $domain = null) { return $text; }

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
