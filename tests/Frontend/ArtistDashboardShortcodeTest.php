<?php
namespace ArtPulse\Frontend;

function is_user_logged_in() { return true; }
function get_current_user_id() { return 1; }
function get_posts($args = []) { return \ArtPulse\Frontend\Tests\ArtistDashboardShortcodeTest::$posts; }
function get_permalink($id) { return '/view/' . $id; }
function get_the_title($post) { return $post->post_title; }
function get_edit_post_link($id) { return '/edit/' . $id; }
function esc_html_e($t,$d=null){}
function esc_html($t) { return $t; }
function esc_url($t) { return $t; }
function wp_enqueue_script($h){}
function __( $t, $d=null ) { return $t; }

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\ArtistDashboardShortcode;

class ArtistDashboardShortcodeTest extends TestCase
{
    public static array $posts = [];

    protected function setUp(): void
    {
        self::$posts = [ (object)['ID'=>5,'post_title'=>'Art One'] ];
    }

    public function test_delete_button_rendered(): void
    {
        $html = ArtistDashboardShortcode::render();
        $this->assertStringContainsString('ap-delete-artwork', $html);
        $this->assertStringContainsString('ap-edit-artwork', $html);
    }
}
