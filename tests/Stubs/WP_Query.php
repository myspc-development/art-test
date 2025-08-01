<?php
namespace ArtPulse\Tests\Stubs;

class WP_Query {
    public array $posts = [];
    public int $max_num_pages = 1;
    public static array $last_args = [];
    public static array $default_posts = [];
    public static int $default_max_pages = 1;

    public function __construct(array $args = [])
    {
        self::$last_args = $args;
        $this->posts = self::$default_posts;
        $this->max_num_pages = self::$default_max_pages;
    }
}
