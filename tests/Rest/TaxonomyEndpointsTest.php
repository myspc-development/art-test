<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Taxonomies\TaxonomiesRegistrar;

/**
 * @group REST
 */
class TaxonomyEndpointsTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		TaxonomiesRegistrar::register();
		do_action( 'init' );
		wp_insert_term( 'Exhibition', 'event_type' );
		wp_insert_term( 'Painting', 'artwork_style' );
		do_action( 'rest_api_init' );
	}

	public function test_event_type_endpoint_returns_terms(): void {
		$req = new \WP_REST_Request( 'GET', '/wp/v2/event_type' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertIsArray( $res->get_data() );
	}

	public function test_artwork_style_endpoint_returns_terms(): void {
		$req = new \WP_REST_Request( 'GET', '/wp/v2/artwork_style' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertIsArray( $res->get_data() );
	}
}
