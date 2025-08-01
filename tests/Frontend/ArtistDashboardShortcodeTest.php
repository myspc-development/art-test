<?php
namespace ArtPulse\Frontend;

function is_user_logged_in() { return true; }
function get_current_user_id() { return 1; }
function get_posts($args = []) {
    \ArtPulse\Frontend\Tests\ArtistDashboardShortcodeTest::$passed_args = $args;
    return \ArtPulse\Frontend\Tests\ArtistDashboardShortcodeTest::$posts;
}
function get_permalink($id) { return '/view/' . $id; }
function get_the_title($post) { return $post->post_title; }
function get_edit_post_link($id) { return '/edit/' . $id; }
function check_ajax_referer($action, $name) {}
function current_user_can($cap, $id = 0) { return true; }
function get_post($id) { return (object)['ID'=>$id,'post_type'=>'artpulse_artwork','post_author'=>1]; }
function wp_delete_post($id, $force = false) { \ArtPulse\Frontend\Tests\ArtistDashboardShortcodeTest::$deleted = $id; }
function wp_send_json_success($data) { \ArtPulse\Frontend\Tests\ArtistDashboardShortcodeTest::$json = $data; }
function wp_send_json_error($data) { \ArtPulse\Frontend\Tests\ArtistDashboardShortcodeTest::$json_error = $data; }
function esc_html($t) { return $t; }
function esc_url($t) { return $t; }
function wp_enqueue_script($h){}
function do_shortcode($code) {
    if ($code === '[ap_user_profile]') {
        return '<div class="ap-user-profile"></div>';
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

    protected function tearDown(): void
    {
        $_POST = [];
        self::$posts = [];
        self::$passed_args = [];
        self::$json = [];
        self::$json_error = null;
        self::$deleted = null;
        parent::tearDown();
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
