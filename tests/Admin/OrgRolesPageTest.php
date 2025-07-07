<?php
namespace ArtPulse\Admin;

// --- WordPress stubs ---
function add_action($hook, $callback, $priority = 10, $args = 1) {}
function add_submenu_page(...$args) { \ArtPulse\Admin\Tests\OrgRolesPageTest::$submenu = $args; }
function current_user_can($cap) { return \ArtPulse\Admin\Tests\OrgRolesPageTest::$caps[$cap] ?? false; }
function wp_die($msg = '') { \ArtPulse\Admin\Tests\OrgRolesPageTest::$died = true; }
function get_current_user_id() { return 1; }
function get_user_meta($uid, $key, $single = false) { return 5; }
function wp_nonce_field($action = '') {}
function admin_url($path = '') { return $path; }
function esc_url($url = '') { return $url; }
function esc_html__($text, $domain = '') { return $text; }
function esc_html($text) { return $text; }
function esc_attr($text) { return $text; }
function wp_redirect($url) { \ArtPulse\Admin\Tests\OrgRolesPageTest::$redirect = $url; }
function check_admin_referer($action = '') {}

namespace ArtPulse\Core;
class OrgRoleManager {
    public const ALL_CAPABILITIES = ['view_events'];
    public static array $roles = [ 'admin' => ['name' => 'Admin', 'caps' => ['view_events']] ];
    public static function get_roles(int $org_id): array { return self::$roles; }
    public static function save_roles(int $org_id, array $roles): void { \ArtPulse\Admin\Tests\OrgRolesPageTest::$saved = $roles; }
}
class RoleAuditLogger {
    public static array $logged = [];
    public static function log(...$args): void { self::$logged = $args; }
}

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\OrgRolesPage;

class OrgRolesPageTest extends TestCase
{
    public static array $caps = [];
    public static bool $died = false;
    public static array $submenu = [];
    public static array $saved = [];
    public static string $redirect = '';

    protected function setUp(): void
    {
        self::$caps = ['view_artpulse_dashboard' => true, 'manage_options' => true];
        self::$died = false;
        self::$submenu = [];
        self::$saved = [];
        self::$redirect = '';
        $_POST = [];
        $_GET = [];
    }

    public function test_add_menu_defaults_to_manage_options(): void
    {
        self::$caps['view_artpulse_dashboard'] = false;
        OrgRolesPage::addMenu();
        $this->assertSame('manage_options', self::$submenu[2] ?? null);
    }

    public function test_render_allows_manage_options(): void
    {
        self::$caps['view_artpulse_dashboard'] = false;
        ob_start();
        OrgRolesPage::render();
        $html = ob_get_clean();
        $this->assertFalse(self::$died);
        $this->assertStringContainsString('Roles & Permissions', $html);
    }

    public function test_handle_form_allowed_for_manage_options(): void
    {
        self::$caps['view_artpulse_dashboard'] = false;
        $_POST['roles'] = ['admin' => ['name' => 'Admin', 'caps' => ['view_events']]];
        ob_start();
        OrgRolesPage::handleForm();
        ob_end_clean();
        $this->assertFalse(self::$died);
        $this->assertNotEmpty(self::$saved);
        $this->assertSame('admin.php?page=ap-org-roles&updated=1', self::$redirect);
    }

    public function test_handle_form_denied_without_caps(): void
    {
        self::$caps['view_artpulse_dashboard'] = false;
        self::$caps['manage_options'] = false;
        ob_start();
        OrgRolesPage::handleForm();
        ob_end_clean();
        $this->assertTrue(self::$died);
    }
}
