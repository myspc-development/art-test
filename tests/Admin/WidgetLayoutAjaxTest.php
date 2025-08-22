<?php
namespace ArtPulse\Admin ;
    if (!function_exists(__NAMESPACE__ . '\\check_ajax_referer')) {
        function check_ajax_referer($action, $name) {}
    }
    if (!function_exists(__NAMESPACE__ . '\\current_user_can')) {
        function current_user_can($cap) {
            return \ArtPulse\Admin\Tests\WidgetLayoutAjaxTest::$can;
        }
    }
    if (!function_exists(__NAMESPACE__ . '\\get_current_user_id')) {
        function get_current_user_id() { return \ArtPulse\Admin\Tests\WidgetLayoutAjaxTest::$uid; }
    }
    if (!function_exists(__NAMESPACE__ . '\\sanitize_key')) {
        function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
    }
    if (!function_exists(__NAMESPACE__ . '\\update_user_meta')) {
        function update_user_meta($uid, $key, $value) { \ArtPulse\Admin\Tests\WidgetLayoutAjaxTest::$meta[$uid][$key] = $value; }
    }
    if (!function_exists(__NAMESPACE__ . '\\wp_send_json_success')) {
        function wp_send_json_success($data = null) { \ArtPulse\Admin\Tests\WidgetLayoutAjaxTest::$json_success = $data ?? true; }
    }
    if (!function_exists(__NAMESPACE__ . '\\wp_send_json_error')) {
        function wp_send_json_error($data) { \ArtPulse\Admin\Tests\WidgetLayoutAjaxTest::$json_error = $data; }
    }
    if (!function_exists(__NAMESPACE__ . '\\add_action')) {
        function add_action($hook, $callback, $priority = 10, $args = 1) { \ArtPulse\Admin\Tests\WidgetLayoutAjaxTest::$hooks[$hook][] = $callback; }
    }
    if (!function_exists(__NAMESPACE__ . '\\do_action')) {
        function do_action($hook) { foreach (\ArtPulse\Admin\Tests\WidgetLayoutAjaxTest::$hooks[$hook] ?? [] as $cb) { call_user_func($cb); } }
    }
    if (!function_exists(__NAMESPACE__ . '\\apply_filters')) {
        function apply_filters($hook, $value) { return $value; }
    }


namespace ArtPulse\Admin\Tests ;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

class WidgetLayoutAjaxTest extends TestCase
{
    public static bool $can = true;
    public static int $uid = 1;
    public static array $meta = [];
    public static array $hooks = [];
    public static $json_success = null;
    public static $json_error = null;

    protected function setUp(): void
    {
        self::$can = true;
        self::$uid = 1;
        self::$meta = [];
        self::$hooks = [];
        self::$json_success = null;
        self::$json_error = null;
        require_once __DIR__ . '/../../includes/dashboard-widgets.php';
        $_POST = [];
        DashboardWidgetRegistry::register('a', 'a', '', '', 'strtolower');
        DashboardWidgetRegistry::register('b', 'b', '', '', 'strtolower');
        DashboardWidgetRegistry::register('c', 'c', '', '', 'strtolower');
    }

    protected function tearDown(): void
    {
        $_POST = [];
        self::$meta = [];
        self::$hooks = [];
        self::$json_success = null;
        self::$json_error = null;
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
        parent::tearDown();
    }

    public function test_save_widget_layout_sanitizes_and_saves(): void
    {
        $_POST['nonce'] = 'n';
        $_POST['layout'] = [
            ['id' => 'c', 'visible' => true],
            ['id' => 'b', 'visible' => false],
            ['id' => 'a', 'visible' => true],
            ['id' => 'a'],
            'bad'
        ];

        ap_save_widget_layout();

        $expected = [
            ['id' => 'c', 'visible' => true],
            ['id' => 'b', 'visible' => false],
            ['id' => 'a', 'visible' => true]
        ];
        $this->assertSame($expected, self::$meta[self::$uid]['ap_dashboard_layout'] ?? null);
        $this->assertSame(['saved' => true], self::$json_success);
        $this->assertNull(self::$json_error);
    }

    public function test_permission_denied_returns_error(): void
    {
        self::$can = false;
        $_POST['nonce'] = 'n';
        $_POST['layout'] = ['a'];

        ap_save_widget_layout();

        $this->assertArrayNotHasKey('ap_dashboard_layout', self::$meta[self::$uid] ?? []);
        $this->assertNull(self::$json_success);
        $this->assertSame(['message' => 'Permission denied'], self::$json_error);
    }
}

