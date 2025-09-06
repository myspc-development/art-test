<?php
namespace ArtPulse\Search;

if ( ! function_exists( __NAMESPACE__ . '\get_option' ) ) {
	function get_option( $key, $default = false ) {
		return \ArtPulse\Search\Tests\ExternalSearchTest::$options[ $key ] ?? $default;
	}
}
if ( ! function_exists( __NAMESPACE__ . '\apply_filters' ) ) {
	function apply_filters( $hook, $value, ...$args ) {
		if ( $hook === 'algolia_search_records' ) {
			return \ArtPulse\Search\Tests\ExternalSearchTest::$algolia_results;
		}
	}
	return $value;
}
if ( ! function_exists( __NAMESPACE__ . '\ep_search' ) ) {
	function ep_search( $args ) {
		\ArtPulse\Search\Tests\ExternalSearchTest::$ep_args = $args;
		return (object) array( 'posts' => \ArtPulse\Search\Tests\ExternalSearchTest::$ep_posts );
	}
}

namespace ArtPulse\Search\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Search\ExternalSearch;

/**

 * @group SEARCH
 */

class ExternalSearchTest extends TestCase {

	public static array $options         = array();
	public static array $algolia_results = array();
	public static array $ep_posts        = array();
	public static array $ep_args         = array();

	protected function setUp(): void {
		self::$options         = array();
		self::$algolia_results = array();
		self::$ep_posts        = array();
		self::$ep_args         = array();
	}

	protected function tearDown(): void {
		self::$options         = array();
		self::$algolia_results = array();
		self::$ep_posts        = array();
		self::$ep_args         = array();
		parent::tearDown();
	}

	public function test_search_returns_algolia_results_when_enabled(): void {
		self::$options['artpulse_settings'] = array( 'search_service' => 'algolia' );
		self::$algolia_results              = array( (object) array( 'ID' => 1 ) );
		$results                            = ExternalSearch::search( 'artist', array( 'limit' => 1 ) );
		$this->assertSame( self::$algolia_results, $results );
	}

	public function test_search_calls_ep_search_when_elasticpress_enabled(): void {
		self::$options['artpulse_settings'] = array( 'search_service' => 'elasticpress' );
		self::$ep_posts                     = array( (object) array( 'ID' => 2 ) );
		$results                            = ExternalSearch::search( 'artist', array( 's' => 'query' ) );
		$this->assertSame( self::$ep_posts, $results );
		$this->assertSame( 'artpulse_artist', self::$ep_args['post_type'] ?? null );
	}
}
