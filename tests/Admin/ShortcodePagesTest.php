<?php
namespace ArtPulse\Admin;

function get_posts($args) { return \ArtPulse\Admin\Tests\ShortcodePagesTest::$posts_return; }
function wp_insert_post($arr) { \ArtPulse\Admin\Tests\ShortcodePagesTest::$inserted[] = $arr; return \ArtPulse\Admin\Tests\ShortcodePagesTest::$next_id++; }
function update_option($key, $value) { \ArtPulse\Admin\Tests\ShortcodePagesTest::$updated[$key] = $value; }
function get_option($key, $default = []) { return \ArtPulse\Admin\Tests\ShortcodePagesTest::$options[$key] ?? $default; }
function is_wp_error($obj) { return false; }
function __( $text, $domain = null ) { return $text; }

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
}
