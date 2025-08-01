<?php
namespace ArtPulse\Ajax;

use ArtPulse\Tests\Stubs\WP_Query;

class_alias(WP_Query::class, '\\WP_Query');

function check_ajax_referer($action, $name) {}
function sanitize_text_field($value) { return $value; }
function get_the_title($id) { return 'Post ' . $id; }
function get_permalink($id) { return '/post/' . $id; }
function wp_send_json($data) { \ArtPulse\Ajax\Tests\FrontendFilterHandlerTest::$json = $data; }

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
        WP_Query::$default_posts = [];
        WP_Query::$default_max_pages = 3;
        WP_Query::$last_args = [];
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
        WP_Query::$default_posts = self::$posts;
        FrontendFilterHandler::handle_filter_posts();
        self::$query_args = WP_Query::$last_args;
        $this->assertSame(2, self::$query_args['paged']);
        $this->assertSame(5, self::$query_args['posts_per_page']);
        $this->assertCount(2, self::$json['posts']);
        $this->assertSame('/post/7', self::$json['posts'][0]['link']);
        $this->assertSame(2, self::$json['page']);
        $this->assertSame(3, self::$json['max_page']);
    }
}
