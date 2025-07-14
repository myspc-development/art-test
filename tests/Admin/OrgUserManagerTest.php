<?php
namespace ArtPulse\Admin;

// --- WordPress function stubs ---
function add_action($hook, $callback, $priority = 10, $args = 1) {}
function add_submenu_page(...$args) {}
function wp_enqueue_script(...$args) {}
function wp_localize_script(...$args) {}
function plugin_dir_path($file) { return '/'; }
function plugin_dir_url($file) { return '/'; }
function file_exists($path) { return false; }
function esc_url_raw($url = '') { return $url; }
function rest_url($path = '') { return $path; }
function wp_create_nonce($action = '') { return 'nonce'; }
function current_user_can($cap) { return OrgUserManagerTest::$can; }
function wp_die($message = '') { OrgUserManagerTest::$died = true; }
function get_current_user_id() { return 1; }
function get_user_meta($uid, $key, $single = false) { return 5; }
function get_users($args = []) { return []; }
function esc_html__($text, $domain = '') { return $text; }
function esc_html($text) { return $text; }
function esc_attr($text) { return $text; }

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
