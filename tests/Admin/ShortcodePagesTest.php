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

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\ShortcodePages;
use ArtPulse\Core\ShortcodeRegistry;

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
        ShortcodeRegistry::reset();
    }

    public function test_creates_selected_pages_and_updates_option(): void
    {
        ShortcodeRegistry::register('ap_login', 'Login', function () {});
        ShortcodeRegistry::register('ap_register', 'Register', function () {});

        ShortcodePages::create_pages(['[ap_login]', '[ap_register]']);

        $this->assertCount(2, self::$inserted);
        $this->assertSame([1,2], self::$updated['ap_shortcode_page_ids']);
    }

    public function test_supports_logout_shortcode(): void
    {
        ShortcodeRegistry::register('ap_logout', 'Logout', function () {});

        ShortcodePages::create_pages(['[ap_logout]']);

        $this->assertCount(1, self::$inserted);
        $this->assertSame('Logout', self::$inserted[0]['post_title']);
    }

    public function test_creates_pages_for_all_shortcodes(): void
    {
        ShortcodeRegistry::register('ap_login', 'Login', function () {});
        ShortcodeRegistry::register('ap_register', 'Register', function () {});
        ShortcodeRegistry::register('ap_logout', 'Logout', function () {});

        $map = ShortcodePages::get_shortcode_map();
        ShortcodePages::create_pages();

        $this->assertCount(count($map), self::$inserted);
        $this->assertSame(range(1, count($map)), self::$updated['ap_shortcode_page_ids']);
    }

    public function test_shortcode_map_includes_new_shortcodes(): void
    {
        ShortcodeRegistry::register('ap_artist_profile_form', 'Artist Profile Form', function () {});
        ShortcodeRegistry::register('ap_collection', 'Collection Detail', function () {});

        $map = ShortcodePages::get_shortcode_map();

        $this->assertArrayHasKey('[ap_artist_profile_form]', $map);
        $this->assertArrayHasKey('[ap_collection]', $map);
    }
}
