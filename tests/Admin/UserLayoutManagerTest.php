<?php
namespace {
    if (!function_exists('__return_null')) {
        function __return_null() { return null; }
    }
}

namespace ArtPulse\Admin {
// WordPress function stubs
if (!function_exists(__NAMESPACE__ . '\\current_user_can')) {
    function current_user_can($cap) {
        return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$can;
    }
}
if (!function_exists(__NAMESPACE__ . '\\wp_die')) {
    function wp_die($msg = '') { \ArtPulse\Admin\Tests\UserLayoutManagerTest::$died = $msg ?: true; }
}
if (!function_exists(__NAMESPACE__ . '\\check_admin_referer')) {
    function check_admin_referer($action) {}
}
if (!function_exists(__NAMESPACE__ . '\\wp_safe_redirect')) {
    function wp_safe_redirect($url) { \ArtPulse\Admin\Tests\UserLayoutManagerTest::$redirect = $url; throw new \Exception('redirect'); }
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
    function update_option($key, $value) { \ArtPulse\Admin\Tests\UserLayoutManagerTest::$options[$key] = $value; }
}
if (!function_exists(__NAMESPACE__ . '\\get_option')) {
    function get_option($key, $default = []) { return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$options[$key] ?? $default; }
}
if (!function_exists(__NAMESPACE__ . '\\file_get_contents')) {
    function file_get_contents($path) { return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$file_contents; }
}
if (!function_exists(__NAMESPACE__ . '\\get_user_meta')) {
    function get_user_meta($uid, $key, $single = false) { return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$meta[$uid][$key] ?? ''; }
}
if (!function_exists(__NAMESPACE__ . '\\update_user_meta')) {
    function update_user_meta($uid, $key, $value) { \ArtPulse\Admin\Tests\UserLayoutManagerTest::$meta[$uid][$key] = $value; }
}
if (!function_exists(__NAMESPACE__ . '\\get_userdata')) {
    function get_userdata($uid) { return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$users[$uid] ?? null; }
}
if (!function_exists(__NAMESPACE__ . '\\sanitize_key')) {
    function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
}
if (!function_exists(__NAMESPACE__ . '\\wp_json_encode')) {
    function wp_json_encode($data, $flags = 0) { return json_encode($data, $flags); }
}
if (!function_exists(__NAMESPACE__ . '\\error_log')) {
    function error_log($msg) { \ArtPulse\Admin\Tests\UserLayoutManagerTest::$logs[] = $msg; }
}
if (!function_exists(__NAMESPACE__ . '\\header')) {
    function header($string, $replace = true, $code = 0) { \ArtPulse\Admin\Tests\UserLayoutManagerTest::$headers[] = $string; }
}
}

namespace ArtPulse\Admin\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\UserLayoutManager;
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
    public static array $logs = [];

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
        self::$logs = [];
        $_FILES = [];
    }

    protected function tearDown(): void
    {
        $_FILES = [];
        self::$meta = [];
        self::$users = [];
        self::$options = [];
        self::$file_contents = '';
        self::$headers = [];
        self::$logs = [];
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
        parent::tearDown();
    }

    public function test_user_layout_is_stored_and_retrieved(): void
    {
        DashboardWidgetRegistry::register('foo', 'Foo', '', '', '__return_null');
        DashboardWidgetRegistry::register('bar', 'Bar', '', '', '__return_null');

        UserLayoutManager::save_layout(1, [
            ['id' => 'bar'],
            ['id' => 'foo'],
            ['id' => 'foo'],
            'invalid'
        ]);

        $expected_saved = [
            ['id' => 'bar', 'visible' => true],
            ['id' => 'foo', 'visible' => true],
        ];

        $this->assertSame($expected_saved, self::$meta[1]['ap_dashboard_layout']);

        $layout = UserLayoutManager::get_layout(1);
        $this->assertSame($expected_saved, $layout);
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
        $this->assertSame([['id' => 'a', 'visible' => true]], $layout);
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

        $result = UserLayoutManager::get_role_layout('subscriber');
        $layout = $result['layout'];
        $this->assertSame([
            ['id' => 'gr_two', 'visible' => true],
            ['id' => 'gr_one', 'visible' => true],
        ], $layout);
        $this->assertSame([], $result['logs']);

        self::$options['ap_dashboard_widget_config'] = [];
        $expected = array_map(
            fn($id) => ['id' => $id, 'visible' => true],
            array_column(DashboardWidgetRegistry::get_definitions(), 'id')
        );
        $this->assertSame($expected, UserLayoutManager::get_role_layout('subscriber')['layout']);
    }

    public function test_get_role_layout_logs_and_stubs_invalid_widget(): void
    {
        DashboardWidgetRegistry::register('good', 'Good', '', '', '__return_null');

        self::$options['ap_dashboard_widget_config'] = [
            'subscriber' => [
                ['id' => 'good'],
                ['id' => 'missing'],
            ],
        ];

        $result = UserLayoutManager::get_role_layout('subscriber');
        $this->assertSame(['missing'], $result['logs']);
        $this->assertSame('missing', $result['layout'][1]['id']);
        $stub = DashboardWidgetRegistry::get('missing');
        $this->assertNotNull($stub);
        $this->assertIsCallable($stub['callback']);
    }

    public function test_get_role_layout_logs_invalid_widgets(): void
    {
        DashboardWidgetRegistry::register('good', 'Good', '', '', '__return_null');
        DashboardWidgetRegistry::register('broken', 'Broken', '', '', '__return_null');

        // Make the "broken" widget callback uncallable.
        $ref  = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $widgets = $prop->getValue();
        $widgets['broken']['callback'] = 'not_callable';
        $prop->setValue(null, $widgets);

        self::$options['ap_dashboard_widget_config'] = [
            'subscriber' => [
                ['id' => 'good'],
                ['id' => 'missing'],
                ['id' => 'broken'],
            ],
        ];

        $layout = UserLayoutManager::get_role_layout('subscriber');
        $this->assertSame([['id' => 'good', 'visible' => true]], $layout);

        $this->assertNotEmpty(self::$logs);
        $log = self::$logs[0];
        $this->assertStringContainsString('subscriber', $log);
        $this->assertStringContainsString('missing', $log);
        $this->assertStringContainsString('broken', $log);
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

    public function test_reset_layout_for_role_removes_config(): void
    {
        self::$options['ap_dashboard_widget_config'] = [
            'subscriber' => [ ['id' => 'foo'] ]
        ];

        UserLayoutManager::reset_layout_for_role('subscriber');

        $this->assertArrayNotHasKey('subscriber', self::$options['ap_dashboard_widget_config']);
    }

    public function test_default_role_layout_omits_manager_widget(): void
    {
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);

        DashboardWidgetRegistry::register('artpulse_dashboard_widget', 'Manager', '', '', '__return_null');
        DashboardWidgetRegistry::register('foo', 'Foo', '', '', '__return_null');

        $layout = UserLayoutManager::get_role_layout('subscriber')['layout'];
        $ids = array_column($layout, 'id');

        $this->assertContains('foo', $ids);
        $this->assertNotContains('artpulse_dashboard_widget', $ids);
    }
}
}

