<?php
namespace ArtPulse\Admin;

// WordPress function stubs
function current_user_can($cap) { return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$can; }
function wp_die($msg = '') { \ArtPulse\Admin\Tests\UserLayoutManagerTest::$died = $msg ?: true; }
function check_admin_referer($action) {}
function wp_safe_redirect($url) { \ArtPulse\Admin\Tests\UserLayoutManagerTest::$redirect = $url; throw new \Exception('redirect'); }
function wp_get_referer() { return '/ref'; }
function admin_url($path = '') { return $path; }
function add_query_arg($key, $value, $base) { return $base . (str_contains($base, '?') ? '&' : '?') . $key . '=' . $value; }
function update_option($key, $value) { \ArtPulse\Admin\Tests\UserLayoutManagerTest::$options[$key] = $value; }
function get_option($key, $default = []) { return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$options[$key] ?? $default; }
function file_get_contents($path) { return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$file_contents; }
function get_user_meta($uid, $key, $single = false) { return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$meta[$uid][$key] ?? ''; }
function update_user_meta($uid, $key, $value) { \ArtPulse\Admin\Tests\UserLayoutManagerTest::$meta[$uid][$key] = $value; }
function get_userdata($uid) { return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$users[$uid] ?? null; }
function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
function wp_json_encode($data, $flags = 0) { return json_encode($data, $flags); }
function header($string, $replace = true, $code = 0) { \ArtPulse\Admin\Tests\UserLayoutManagerTest::$headers[] = $string; }
function __($text, $domain = null) { return $text; }

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Core\DashboardWidgetRegistry;

class UserLayoutManagerTest extends TestCase
{
    public static bool $can = true;
    public static $died = null;
    public static string $redirect = '';
    public static array $meta = [];
    public static array $users = [];
    public static array $options = [];
    public static string $file_contents = '';
    public static array $headers = [];

    protected function setUp(): void
    {
        self::$can = true;
        self::$died = null;
        self::$redirect = '';
        self::$meta = [];
        self::$users = [];
        self::$options = [];
        self::$file_contents = '';
        self::$headers = [];
        $_FILES = [];
    }

    public function test_user_layout_is_stored_and_retrieved(): void
    {
        DashboardWidgetRegistry::register('foo', 'Foo', '', '', '__return_null');
        DashboardWidgetRegistry::register('bar', 'Bar', '', '', '__return_null');

        UserLayoutManager::save_layout(1, ['bar', 'foo', 'foo', 'invalid']);

        $this->assertSame(['bar', 'foo'], self::$meta[1]['ap_dashboard_layout']);

        $layout = UserLayoutManager::get_layout(1);
        $this->assertSame(['bar', 'foo'], $layout);
    }

    public function test_get_layout_falls_back_to_role_then_registry(): void
    {
        DashboardWidgetRegistry::register('a', 'A', '', '', '__return_null');
        DashboardWidgetRegistry::register('b', 'B', '', '', '__return_null');

        self::$users[2] = (object)['roles' => ['subscriber']];
        self::$options['ap_dashboard_widget_config'] = ['subscriber' => [ ['id' => 'b'] ]];

        $layout = UserLayoutManager::get_layout(2);
        $this->assertSame([['id' => 'b', 'visible' => true]], $layout);

        self::$options['ap_dashboard_widget_config'] = [];
        $layout = UserLayoutManager::get_layout(2);
        $expected = array_map(
            fn($id) => ['id' => $id, 'visible' => true],
            array_column(DashboardWidgetRegistry::get_definitions(), 'id')
        );
        $this->assertSame($expected, $layout);

        self::$meta[2]['ap_dashboard_layout'] = ['a'];
        $layout = UserLayoutManager::get_layout(2);
        $this->assertSame(['a'], $layout);
    }

    public function test_save_role_layout_sanitizes_and_updates_option(): void
    {
        DashboardWidgetRegistry::register('sr_one', 'One', '', '', '__return_null');
        DashboardWidgetRegistry::register('sr_two', 'Two', '', '', '__return_null');

        UserLayoutManager::save_role_layout('editor<script>', [
            ['id' => 'sr_two'],
            ['id' => 'sr_one'],
            ['id' => 'sr_one'],
            'invalid'
        ]);

        $expected = [
            'editorscript' => [
                ['id' => 'sr_two', 'visible' => true],
                ['id' => 'sr_one', 'visible' => true],
            ],
        ];

        $this->assertSame($expected, self::$options['ap_dashboard_widget_config'] ?? null);
    }

    public function test_get_role_layout_returns_saved_or_fallback(): void
    {
        DashboardWidgetRegistry::register('gr_one', 'One', '', '', '__return_null');
        DashboardWidgetRegistry::register('gr_two', 'Two', '', '', '__return_null');

        self::$options['ap_dashboard_widget_config'] = [
            'subscriber' => [
                ['id' => 'gr_two'],
                ['id' => 'GR_ONE']
            ],
        ];

        $layout = UserLayoutManager::get_role_layout('subscriber');
        $this->assertSame([
            ['id' => 'gr_two', 'visible' => true],
            ['id' => 'gr_one', 'visible' => true],
        ], $layout);

        self::$options['ap_dashboard_widget_config'] = [];
        $expected = array_map(
            fn($id) => ['id' => $id, 'visible' => true],
            array_column(DashboardWidgetRegistry::get_definitions(), 'id')
        );
        $this->assertSame($expected, UserLayoutManager::get_role_layout('subscriber'));
    }

    /**
     * @runInSeparateProcess
     */
    public function test_role_layout_import_and_export_json(): void
    {
        DashboardWidgetRegistry::register('foo', 'Foo', '', '', '__return_null');
        DashboardWidgetRegistry::register('bar', 'Bar', '', '', '__return_null');

        $import = ['administrator' => [
            ['id' => 'bar'],
            ['id' => 'foo', 'visible' => false]
        ]];
        self::$file_contents = json_encode($import, JSON_PRETTY_PRINT);
        $_FILES['ap_widget_file'] = ['tmp_name' => '/tmp/test'];

        try {
            DashboardWidgetTools::handle_import();
        } catch (\Exception $e) {
            $this->assertSame('redirect', $e->getMessage());
        }

        $this->assertSame($import, self::$options['ap_dashboard_widget_config']);

        $expected_json = json_encode($import, JSON_PRETTY_PRINT);
        self::$options['ap_dashboard_widget_config'] = $import;
        $this->expectOutputString($expected_json);
        DashboardWidgetTools::handle_export();
    }

    public function test_export_layout_returns_pretty_json(): void
    {
        DashboardWidgetRegistry::register('foo', 'Foo', '', '', '__return_null');
        UserLayoutManager::save_role_layout('subscriber', [ ['id' => 'foo'] ]);

        $expected = json_encode([
            ['id' => 'foo', 'visible' => true]
        ], JSON_PRETTY_PRINT);
        $this->assertSame($expected, UserLayoutManager::export_layout('subscriber'));
    }

    public function test_import_layout_decodes_and_saves(): void
    {
        DashboardWidgetRegistry::register('foo', 'Foo', '', '', '__return_null');
        DashboardWidgetRegistry::register('bar', 'Bar', '', '', '__return_null');

        $json = json_encode([
            ['id' => 'bar'],
            ['id' => 'foo']
        ]);
        UserLayoutManager::import_layout('subscriber', $json);

        $this->assertSame([
            ['id' => 'bar', 'visible' => true],
            ['id' => 'foo', 'visible' => true]
        ], self::$options['ap_dashboard_widget_config']['subscriber']);
    }
}

