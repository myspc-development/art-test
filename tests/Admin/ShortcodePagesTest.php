<?php
namespace ArtPulse\Admin;

function get_posts($args) { return \ArtPulse\Admin\Tests\ShortcodePagesTest::$posts_return; }
function wp_insert_post($arr) { \ArtPulse\Admin\Tests\ShortcodePagesTest::$inserted[] = $arr; return \ArtPulse\Admin\Tests\ShortcodePagesTest::$next_id++; }
function update_option($key, $value) { \ArtPulse\Admin\Tests\ShortcodePagesTest::$updated[$key] = $value; }
function get_option($key, $default = []) { return \ArtPulse\Admin\Tests\ShortcodePagesTest::$options[$key] ?? $default; }
function is_wp_error($obj) { return false; }

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\ShortcodePages;

class ShortcodePagesTest extends TestCase
{
    public static array $posts_return = [];
    public static array $inserted = [];
    public static array $updated = [];
    public static array $options = [];
    public static int $next_id = 1;

    protected function setUp(): void
    {
        self::$posts_return = [];
        self::$inserted = [];
        self::$updated = [];
        self::$options = [];
        self::$next_id = 1;
    }

    public function test_creates_selected_pages_and_updates_option(): void
    {
        ShortcodePages::create_pages(['[ap_login]', '[ap_register]']);

        $this->assertCount(2, self::$inserted);
        $this->assertSame([1,2], self::$updated['ap_shortcode_page_ids']);
    }

    public function test_supports_logout_shortcode(): void
    {
        ShortcodePages::create_pages(['[ap_logout]']);

        $this->assertCount(1, self::$inserted);
        $this->assertSame('Logout', self::$inserted[0]['post_title']);
    }

    public function test_creates_pages_for_all_shortcodes(): void
    {
        $map = ShortcodePages::get_shortcode_map();

        ShortcodePages::create_pages();

        $this->assertCount(count($map), self::$inserted);
        $this->assertSame(range(1, count($map)), self::$updated['ap_shortcode_page_ids']);
    }

    public function test_shortcode_map_includes_new_shortcodes(): void
    {
        $map = ShortcodePages::get_shortcode_map();
        $expected = [
            '[ap_artist_profile_form]',
            '[ap_collection]',
            '[ap_collections]',
            '[ap_competition_dashboard]',
            '[ap_event_chat]',
            '[ap_favorite_portfolio]',
            '[ap_favorites_analytics]',
            '[ap_messages]',
            '[ap_org_rsvp_dashboard]',
            '[ap_payouts]',
            '[ap_recommendations]',
            '[ap_render_ui]',
            '[ap_spotlights]',
        ];
        foreach ($expected as $code) {
            $this->assertArrayHasKey($code, $map);
        }
    }
}
