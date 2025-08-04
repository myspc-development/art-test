<?php
namespace ArtPulse\Admin;

if (!function_exists(__NAMESPACE__ . '\\get_posts')) {
    function get_posts($args) { return \ArtPulse\Admin\Tests\ShortcodePagesTest::$posts_return; }
}
if (!function_exists(__NAMESPACE__ . '\\wp_insert_post')) {
    function wp_insert_post($arr) { \ArtPulse\Admin\Tests\ShortcodePagesTest::$inserted[] = $arr; return \ArtPulse\Admin\Tests\ShortcodePagesTest::$next_id++; }
}
if (!function_exists(__NAMESPACE__ . '\\update_option')) {
    function update_option($key, $value) { \ArtPulse\Admin\Tests\ShortcodePagesTest::$updated[$key] = $value; }
}
if (!function_exists(__NAMESPACE__ . '\\get_option')) {
    function get_option($key, $default = []) { return \ArtPulse\Admin\Tests\ShortcodePagesTest::$options[$key] ?? $default; }
}
if (!function_exists(__NAMESPACE__ . '\\is_wp_error')) {
    function is_wp_error($obj) { return false; }
}
if (!function_exists(__NAMESPACE__ . '\\__')) {
    function __($text, $domain = null) { return $text; }
}

if (!class_exists(__NAMESPACE__ . '\\wpdb')) {
    class wpdb {
        public static array $col_results = [];
        public $posts = 'wp_posts';
        public function esc_like($text) { return $text; }
        public function prepare($query, $like) { return $query; }
        public function get_col($query) {
            return array_shift(self::$col_results) ?? [];
        }
    }
}

$GLOBALS['wpdb'] = new wpdb();

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
        \ArtPulse\Admin\wpdb::$col_results = [];
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

    public function test_collects_existing_shortcode_pages(): void
    {
        \ArtPulse\Admin\wpdb::$col_results = [[10, 11]];

        ShortcodePages::create_pages(['[ap_login]']);

        $this->assertCount(0, self::$inserted);
        $this->assertSame([10, 11], self::$updated['ap_shortcode_page_ids']);
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
            '[ap_react_dashboard]',
            '[ap_payouts]',
            '[ap_recommendations]',
            '[ap_render_ui]',
            '[ap_spotlights]',
            '[ap_widget]',
        ];
        foreach ($expected as $code) {
            $this->assertArrayHasKey($code, $map);
        }
    }
}
