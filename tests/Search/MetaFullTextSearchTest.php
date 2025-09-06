<?php
namespace ArtPulse\Search\Tests;

use ArtPulse\Search\MetaFullTextSearch;

/**

 * @group SEARCH
 */

class MetaFullTextSearchTest extends \WP_UnitTestCase {

	public function test_rest_filter_adds_meta_query_for_valid_key(): void {
		$req = new \WP_REST_Request( 'GET', '/' );
		$req->set_param( 'meta_key', 'artist_name' );
		$req->set_param( 'meta_value', 'john' );
		$args = MetaFullTextSearch::rest_meta_search_filter( array(), $req, 'artpulse_artist' );
		$this->assertArrayHasKey( 'meta_query', $args );
		$this->assertSame( 'artist_name', $args['meta_query'][0]['key'] );
		$this->assertSame( 'john', $args['meta_query'][0]['value'] );
	}

	public function test_rest_filter_ignores_invalid_key(): void {
		$req = new \WP_REST_Request( 'GET', '/' );
		$req->set_param( 'meta_key', 'invalid' );
		$req->set_param( 'meta_value', 'val' );
		$args = MetaFullTextSearch::rest_meta_search_filter( array(), $req, 'artpulse_artist' );
		$this->assertArrayNotHasKey( 'meta_query', $args );
	}
}
