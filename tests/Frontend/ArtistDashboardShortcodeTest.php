<?php
namespace ArtPulse\Frontend;

function is_user_logged_in() { return true; }
function get_current_user_id() { return 1; }
function get_posts($args) { return ArtistDashboardShortcodeTest::$posts; }
function get_edit_post_link($id) { return ''; }
function esc_html_e($t,$d=null){}
function _e($t,$d=null){}
function esc_html($t){ return $t; }
function esc_url($t){ return $t; }
function do_shortcode($code) {
    if ($code === '[ap_submission_form post_type="artpulse_artwork"]') {
        return '<form class="ap-artwork-submission"></form>';
    }
    if ($code === '[ap_profile_edit]') {
        return '<form class="ap-profile-edit"></form>';
    }
    return '';
}

namespace ArtPulse\Frontend\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Frontend\ArtistDashboardShortcode;

class ArtistDashboardShortcodeTest extends TestCase
{
    public static array $posts = [];

    protected function setUp(): void
    {
        self::$posts = [ (object)['ID'=>2,'post_title'=>'Art One'] ];
    }

    public function test_dashboard_includes_forms(): void
    {
        $html = ArtistDashboardShortcode::render([]);
        $this->assertStringContainsString('ap-artwork-submission', $html);
        $this->assertStringContainsString('ap-profile-edit', $html);
        $this->assertStringContainsString('id="ap-membership-info"', $html);
    }
}
