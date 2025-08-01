<?php
namespace ArtPulse\Admin;

// --- WordPress function stubs ---
if (!function_exists(__NAMESPACE__ . '\\add_action')) {
    function add_action($hook, $callback, $priority = 10, $args = 1) {}
}
if (!function_exists(__NAMESPACE__ . '\\add_submenu_page')) {
    function add_submenu_page(...$args) {}
}
if (!function_exists(__NAMESPACE__ . '\\wp_enqueue_script')) {
    function wp_enqueue_script(...$args) {}
}
if (!function_exists(__NAMESPACE__ . '\\wp_localize_script')) {
    function wp_localize_script(...$args) {}
}
if (!function_exists(__NAMESPACE__ . '\\plugin_dir_path')) {
    function plugin_dir_path($file) { return '/'; }
}
if (!function_exists(__NAMESPACE__ . '\\plugin_dir_url')) {
    function plugin_dir_url($file) { return '/'; }
}
if (!function_exists(__NAMESPACE__ . '\\file_exists')) {
    function file_exists($path) { return false; }
}
if (!function_exists(__NAMESPACE__ . '\\esc_url_raw')) {
    function esc_url_raw($url = '') { return $url; }
}
if (!function_exists(__NAMESPACE__ . '\\rest_url')) {
    function rest_url($path = '') { return $path; }
}
if (!function_exists(__NAMESPACE__ . '\\wp_create_nonce')) {
    function wp_create_nonce($action = '') { return 'nonce'; }
}
if (!function_exists(__NAMESPACE__ . '\\current_user_can')) {
    function current_user_can($cap) {
        return OrgUserManagerTest::$can;
    }
}
if (!function_exists(__NAMESPACE__ . '\\wp_die')) {
    function wp_die($message = '') { OrgUserManagerTest::$died = true; }
}
if (!function_exists(__NAMESPACE__ . '\\get_current_user_id')) {
    function get_current_user_id() { return 1; }
}
if (!function_exists(__NAMESPACE__ . '\\get_user_meta')) {
    function get_user_meta($uid, $key, $single = false) { return 5; }
}
if (!function_exists(__NAMESPACE__ . '\\get_users')) {
    function get_users($args = []) { return []; }
}
if (!function_exists(__NAMESPACE__ . '\\esc_html')) {
    function esc_html($text) { return $text; }
}
if (!function_exists(__NAMESPACE__ . '\\esc_attr')) {
    function esc_attr($text) { return $text; }
}

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\OrgUserManager;

class OrgUserManagerTest extends TestCase
{
    public static bool $can = true;
    public static bool $died = false;

    protected function setUp(): void
    {
        self::$can = true;
        self::$died = false;
    }

    public function test_render_denied(): void
    {
        self::$can = false;
        ob_start();
        OrgUserManager::render();
        ob_end_clean();
        $this->assertTrue(self::$died);
    }

    public function test_render_allowed_outputs_html(): void
    {
        self::$can = true;
        ob_start();
        OrgUserManager::render();
        $out = ob_get_clean();
        $this->assertStringContainsString('ap-org-invite-form', $out);
        $this->assertStringContainsString('ap-invite-role', $out);
        $this->assertStringContainsString('Role', $out);
    }
}
