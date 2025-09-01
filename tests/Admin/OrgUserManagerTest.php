<?php
namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\OrgUserManager;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**

 * @group ADMIN

 */

class OrgUserManagerTest extends TestCase {
    public static bool $can  = true;
    public static bool $died = false;

    protected function setUp(): void {
        parent::setUp();
        self::$can  = true;
        self::$died = false;
        Monkey\setUp();
        Functions\when('add_action')->justReturn();
        Functions\when('add_submenu_page')->justReturn();
        Functions\when('wp_enqueue_script')->justReturn();
        Functions\when('wp_localize_script')->justReturn();
        Functions\when('plugin_dir_url')->alias(fn($file) => '/');
        Functions\when('file_exists')->alias(fn($path) => false);
        Functions\when('esc_url_raw')->alias(fn($url = '') => $url);
        Functions\when('rest_url')->alias(fn($path = '') => $path);
        Functions\when('wp_create_nonce')->alias(fn($action = '') => 'nonce');
        Functions\when('current_user_can')->alias(fn($cap) => self::$can);
        Functions\when('wp_die')->alias(function ( $message = '' ) { self::$died = true; });
        Functions\when('get_current_user_id')->alias(fn() => 1);
        Functions\when('get_user_meta')->alias(fn($uid, $key, $single = false) => 5);
        Functions\when('get_users')->alias(fn($args = array()) => array());
        Functions\when('esc_html')->alias(fn($text) => $text);
        Functions\when('esc_attr')->alias(fn($text) => $text);
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_render_denied(): void {
        self::$can = false;
        ob_start();
        OrgUserManager::render();
        ob_end_clean();
        $this->assertTrue(self::$died);
    }

    public function test_render_allowed_outputs_html(): void {
        self::$can = true;
        ob_start();
        OrgUserManager::render();
        $out = ob_get_clean();
        $this->assertStringContainsString('ap-org-invite-form', $out);
        $this->assertStringContainsString('ap-invite-role', $out);
        $this->assertStringContainsString('Role', $out);
    }
}
