<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Core\DirectoryManager;

/**
 * @group REST
 */
class DirectoryManagerTest extends \WP_UnitTestCase {

	private int $org_id;
	private int $logo_id;

	public function set_up() {
		parent::set_up();
		// create a dummy image file for the logo
		$upload_dir = wp_upload_dir();
		$filename   = $upload_dir['basedir'] . '/test-logo.jpg';
		if ( ! file_exists( $filename ) ) {
			file_put_contents( $filename, 'logo' );
		}
		$this->logo_id = self::factory()->attachment->create_upload_object( $filename );

		$this->org_id = wp_insert_post(
			array(
				'post_title'  => 'Org',
				'post_type'   => 'artpulse_org',
				'post_status' => 'publish',
				'meta_input'  => array(
					'ead_org_logo_id' => $this->logo_id,
				),
			)
		);

		DirectoryManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_org_logo_populates_featured_media_url(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/filter' );
		$req->set_param( 'type', 'org' );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertNotEmpty( $data[0]['featured_media_url'] );
		$this->assertStringContainsString( 'test-logo.jpg', $data[0]['featured_media_url'] );
	}

	public function test_filter_artists_by_medium(): void {
		$medium = wp_insert_term( 'Painting', 'artist_specialty' );
		$style  = wp_insert_term( 'Modern', 'artwork_style' );

		$artist = wp_insert_post(
			array(
				'post_title'  => 'Painter',
				'post_type'   => 'artpulse_artist',
				'post_status' => 'publish',
			)
		);
		wp_set_object_terms( $artist, array( $medium['term_id'] ), 'artist_specialty' );
		wp_set_object_terms( $artist, array( $style['term_id'] ), 'artwork_style' );

		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/filter' );
		$req->set_param( 'type', 'artist' );
		$req->set_param( 'medium', $medium['term_id'] );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( $artist, $data[0]['id'] );
		$this->assertSame( array( 'Painting' ), $data[0]['medium'] );
		$this->assertSame( array( 'Modern' ), $data[0]['style'] );
	}

	public function test_filter_artists_by_style(): void {
		$medium = wp_insert_term( 'Sculpture', 'artist_specialty' );
		$style  = wp_insert_term( 'Abstract', 'artwork_style' );

		$artist = wp_insert_post(
			array(
				'post_title'  => 'Sculptor',
				'post_type'   => 'artpulse_artist',
				'post_status' => 'publish',
			)
		);
		wp_set_object_terms( $artist, array( $medium['term_id'] ), 'artist_specialty' );
		wp_set_object_terms( $artist, array( $style['term_id'] ), 'artwork_style' );

		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/filter' );
		$req->set_param( 'type', 'artist' );
		$req->set_param( 'style', $style['term_id'] );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( $artist, $data[0]['id'] );
		$this->assertSame( array( 'Sculpture' ), $data[0]['medium'] );
		$this->assertSame( array( 'Abstract' ), $data[0]['style'] );
	}

	public function test_filter_artworks_by_medium_and_style(): void {
		$medium = wp_insert_term( 'Oil', 'artpulse_medium' );
		$style  = wp_insert_term( 'Impressionism', 'artwork_style' );

		$artwork = wp_insert_post(
			array(
				'post_title'  => 'Oil Painting',
				'post_type'   => 'artpulse_artwork',
				'post_status' => 'publish',
				'meta_input'  => array(
					'_ap_artwork_medium' => 'Oil',
				),
			)
		);
		wp_set_object_terms( $artwork, array( $medium['term_id'] ), 'artpulse_medium' );
		wp_set_object_terms( $artwork, array( $style['term_id'] ), 'artwork_style' );

		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/filter' );
		$req->set_param( 'type', 'artwork' );
		$req->set_param( 'medium', $medium['term_id'] );
		$req->set_param( 'style', $style['term_id'] );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( $artwork, $data[0]['id'] );
		$this->assertSame( 'Oil', $data[0]['medium'] );
		$this->assertSame( 'Impressionism', $data[0]['style'] );
	}

	public function test_filter_by_first_letter(): void {
		$a   = wp_insert_post(
			array(
				'post_title'  => 'Alice',
				'post_type'   => 'artpulse_artist',
				'post_status' => 'publish',
			)
		);
		$b   = wp_insert_post(
			array(
				'post_title'  => 'Bob',
				'post_type'   => 'artpulse_artist',
				'post_status' => 'publish',
			)
		);
		$num = wp_insert_post(
			array(
				'post_title'  => '3D Modeler',
				'post_type'   => 'artpulse_artist',
				'post_status' => 'publish',
			)
		);

		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/filter' );
		$req->set_param( 'type', 'artist' );
		$req->set_param( 'first_letter', 'A' );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( $a, $data[0]['id'] );

		$req->set_param( 'first_letter', '#' );
		$res  = rest_get_server()->dispatch( $req );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( $num, $data[0]['id'] );
	}

	public function test_filter_results_are_cached(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/filter' );
		$req->set_param( 'type', 'org' );
		rest_get_server()->dispatch( $req );

		$key = DirectoryManager::get_cache_key(
			array(
				'type'         => 'org',
				'limit'        => 10,
				'event_type'   => 0,
				'medium'       => 0,
				'style'        => 0,
				'org_type'     => '',
				'location'     => '',
				'city'         => '',
				'region'       => '',
				'for_sale'     => null,
				'keyword'      => '',
				'first_letter' => '',
			)
		);

		$this->assertIsArray( get_transient( $key ) );
	}

	public function test_clear_cache_deletes_transients(): void {
		$key = DirectoryManager::get_cache_key(
			array(
				'type'         => 'org',
				'limit'        => 10,
				'event_type'   => 0,
				'medium'       => 0,
				'style'        => 0,
				'org_type'     => '',
				'location'     => '',
				'city'         => '',
				'region'       => '',
				'for_sale'     => null,
				'keyword'      => '',
				'first_letter' => '',
			)
		);

		set_transient( $key, array( $this->org_id ), MINUTE_IN_SECONDS * 5 );

		DirectoryManager::clear_cache( $this->org_id, get_post( $this->org_id ), true );

		$this->assertFalse( get_transient( $key ) );
	}
}
