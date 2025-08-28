<?php
namespace ArtPulse\Ajax\Tests;

use ArtPulse\Ajax\FrontendFilterHandler;
use ArtPulse\Tests\Stubs\MockStorage;

class FrontendFilterHandlerTest extends \WP_UnitTestCase
{
    public static array $posts = [];
    public static array $json = [];
    public static array $query_args = [];

    public function set_up(): void
    {
        parent::set_up();
        self::$posts      = [];
        MockStorage::$json = [];
        self::$query_args = [];
        WP_Query::$default_posts = [];
        WP_Query::$default_max_pages = 3;
        WP_Query::$last_args = [];
        $_GET = [];
    }

    public function tear_down(): void
    {
        $_GET              = [];
        self::$posts       = [];
        MockStorage::$json = [];
        self::$query_args  = [];
        parent::tear_down();
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
        $this->assertCount(2, MockStorage::$json['posts']);
        $this->assertSame('/post/7', MockStorage::$json['posts'][0]['link']);
        $this->assertSame(2, MockStorage::$json['page']);
        $this->assertSame(3, MockStorage::$json['max_page']);
    }
}
