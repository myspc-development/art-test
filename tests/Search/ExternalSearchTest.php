<?php
namespace ArtPulse\Search;

function get_option($key, $default = false) {
    return \ArtPulse\Search\Tests\ExternalSearchTest::$options[$key] ?? $default;
}
function apply_filters($hook, $value, ...$args) {
    if ($hook === 'algolia_search_records') {
        return \ArtPulse\Search\Tests\ExternalSearchTest::$algolia_results;
    }
    return $value;
}
function ep_search($args) {
    \ArtPulse\Search\Tests\ExternalSearchTest::$ep_args = $args;
    return (object)['posts' => \ArtPulse\Search\Tests\ExternalSearchTest::$ep_posts];
}

namespace ArtPulse\Search\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Search\ExternalSearch;

class ExternalSearchTest extends TestCase
{
    public static array $options = [];
    public static array $algolia_results = [];
    public static array $ep_posts = [];
    public static array $ep_args = [];

    protected function setUp(): void
    {
        self::$options = [];
        self::$algolia_results = [];
        self::$ep_posts = [];
        self::$ep_args = [];
    }

    protected function tearDown(): void
    {
        self::$options = [];
        self::$algolia_results = [];
        self::$ep_posts = [];
        self::$ep_args = [];
        parent::tearDown();
    }

    public function test_search_returns_algolia_results_when_enabled(): void
    {
        self::$options['artpulse_settings'] = ['search_service' => 'algolia'];
        self::$algolia_results = [ (object)['ID' => 1] ];
        $results = ExternalSearch::search('artist', ['limit' => 1]);
        $this->assertSame(self::$algolia_results, $results);
    }

    public function test_search_calls_ep_search_when_elasticpress_enabled(): void
    {
        self::$options['artpulse_settings'] = ['search_service' => 'elasticpress'];
        self::$ep_posts = [ (object)['ID' => 2] ];
        $results = ExternalSearch::search('artist', ['s' => 'query']);
        $this->assertSame(self::$ep_posts, $results);
        $this->assertSame('artpulse_artist', self::$ep_args['post_type'] ?? null);
    }
}
