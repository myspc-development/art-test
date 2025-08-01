<?php
namespace ArtPulse\Ajax;

if (!function_exists(__NAMESPACE__ . '\check_ajax_referer')) {
function check_ajax_referer($action, $name) {}
}
if (!function_exists(__NAMESPACE__ . '\sanitize_text_field')) {
function sanitize_text_field($value) { return $value; }
}
class WP_Query {
    public array $posts = [];
    public int $max_num_pages = 3;
    public function __construct($args) {
        \ArtPulse\Ajax\Tests\FrontendFilterHandlerTest::$query_args = $args;
        $this->posts = \ArtPulse\Ajax\Tests\FrontendFilterHandlerTest::$posts;
    }
}
if (!function_exists(__NAMESPACE__ . '\get_the_title')) {
function get_the_title($id) { return 'Post ' . $id; }
}
if (!function_exists(__NAMESPACE__ . '\get_permalink')) {
function get_permalink($id) { return '/post/' . $id; }
}
if (!function_exists(__NAMESPACE__ . '\wp_send_json')) {
function wp_send_json($data) { \ArtPulse\Ajax\Tests\FrontendFilterHandlerTest::$json = $data; }
}

namespace ArtPulse\Ajax\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Ajax\FrontendFilterHandler;

class FrontendFilterHandlerTest extends TestCase
{
    public static array $posts = [];
    public static array $json = [];
    public static array $query_args = [];

    protected function setUp(): void
    {
        self::$posts = [];
        self::$json = [];
        self::$query_args = [];
        $_GET = [];
    }

    public function test_handle_filter_posts_outputs_json(): void
    {
        self::$posts = [7, 8];
        $_GET = [
            'page' => 2,
            'per_page' => 5,
            'terms' => 'a,b',
            'nonce' => 'n',
        ];
        FrontendFilterHandler::handle_filter_posts();
        $this->assertSame(2, self::$query_args['paged']);
        $this->assertSame(5, self::$query_args['posts_per_page']);
        $this->assertCount(2, self::$json['posts']);
        $this->assertSame('/post/7', self::$json['posts'][0]['link']);
        $this->assertSame(2, self::$json['page']);
        $this->assertSame(3, self::$json['max_page']);
    }
}
