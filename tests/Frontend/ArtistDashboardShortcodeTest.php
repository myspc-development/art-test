<?php
namespace ArtPulse\Frontend;

if (!function_exists(__NAMESPACE__ . '\is_user_logged_in')) {
function is_user_logged_in() { return true; }
}
if (!function_exists(__NAMESPACE__ . '\get_current_user_id')) {
function get_current_user_id() { return 1; }
}
if (!function_exists(__NAMESPACE__ . '\get_posts')) {
function get_posts($args = []) {
    \ArtPulse\Frontend\Tests\ArtistDashboardShortcodeTest::$passed_args = $args;
    return \ArtPulse\Frontend\Tests\ArtistDashboardShortcodeTest::$posts;
}
}
if (!function_exists(__NAMESPACE__ . '\get_permalink')) {
function get_permalink($id) { return '/view/' . $id; }
}
if (!function_exists(__NAMESPACE__ . '\get_the_title')) {
function get_the_title($post) { return $post->post_title; }
}
if (!function_exists(__NAMESPACE__ . '\get_edit_post_link')) {
function get_edit_post_link($id) { return '/edit/' . $id; }
}
if (!function_exists(__NAMESPACE__ . '\check_ajax_referer')) {
function check_ajax_referer($action, $name) {}
}
if (!function_exists(__NAMESPACE__ . '\current_user_can')) {
function current_user_can($cap, $id = 0) { return true; }
}
if (!function_exists(__NAMESPACE__ . '\get_post')) {
function get_post($id) { return (object)['ID'=>$id,'post_type'=>'artpulse_artwork','post_author'=>1]; }
}
if (!function_exists(__NAMESPACE__ . '\wp_delete_post')) {
function wp_delete_post($id, $force = false) { \ArtPulse\Frontend\Tests\ArtistDashboardShortcodeTest::$deleted = $id; }
}
if (!function_exists(__NAMESPACE__ . '\wp_send_json_success')) {
function wp_send_json_success($data) { \ArtPulse\Frontend\Tests\ArtistDashboardShortcodeTest::$json = $data; }
}
if (!function_exists(__NAMESPACE__ . '\wp_send_json_error')) {
function wp_send_json_error($data) { \ArtPulse\Frontend\Tests\ArtistDashboardShortcodeTest::$json_error = $data; }
}
if (!function_exists(__NAMESPACE__ . '\esc_html')) {
function esc_html($t) { return $t; }
}
if (!function_exists(__NAMESPACE__ . '\esc_url')) {
function esc_url($t) { return $t; }
}
if (!function_exists(__NAMESPACE__ . '\wp_enqueue_script')) {
function wp_enqueue_script($h){}
}
if (!function_exists(__NAMESPACE__ . '\do_shortcode')) {
function do_shortcode($code) {
    if ($code === '[ap_user_profile]') {
        return '<div class="ap-user-profile"></div>';
    }
}
    return '';
}

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\ArtistDashboardShortcode;

class ArtistDashboardShortcodeTest extends TestCase
{
    public static array $posts = [];
    public static array $passed_args = [];
    public static array $json = [];
    public static $json_error = null;
    public static $deleted = null;

    protected function setUp(): void
    {
        self::$posts = [ (object)['ID'=>5,'post_title'=>'Art One'] ];
        self::$passed_args = [];
        self::$json = [];
        self::$json_error = null;
        self::$deleted = null;
    }

    public function test_delete_button_rendered(): void
    {
        $html = ArtistDashboardShortcode::render();
        $this->assertStringContainsString('ap-delete-artwork', $html);
        $this->assertStringContainsString('ap-edit-artwork', $html);
        $this->assertStringContainsString('ap-user-profile', $html);
    }

    public function test_deletion_returns_ordered_html(): void
    {
        self::$posts = [
            (object)['ID' => 1, 'post_title' => 'First'],
            (object)['ID' => 3, 'post_title' => 'Second'],
        ];
        $_POST['artwork_id'] = 2;
        $_POST['nonce'] = 'n';

        ArtistDashboardShortcode::handle_ajax_delete_artwork();

        $this->assertSame(2, self::$deleted);
        $this->assertSame('menu_order', self::$passed_args['orderby'] ?? null);
        $this->assertSame('ASC', self::$passed_args['order'] ?? null);

        $html = self::$json['updated_list_html'] ?? '';
        $pos1 = strpos($html, '/view/1');
        $pos2 = strpos($html, '/view/3');
        $this->assertNotFalse($pos1);
        $this->assertNotFalse($pos2);
        $this->assertLessThan($pos2, $pos1);
    }
}
