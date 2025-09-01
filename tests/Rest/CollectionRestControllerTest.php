<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\CollectionRestController;

/**
 * @group REST
 */
class CollectionRestControllerTest extends \WP_UnitTestCase {

	private int $event_id;
	private int $artwork_id;
	private int $collection_id;

	public function set_up() {
		parent::set_up();
		do_action( 'init' );

		$this->event_id = self::factory()->post->create(
			array(
				'post_type'    => 'artpulse_event',
				'post_title'   => 'Event',
				'post_content' => 'Event description',
				'post_status'  => 'publish',
			)
		);

		$this->artwork_id = self::factory()->post->create(
			array(
				'post_type'    => 'artpulse_artwork',
				'post_title'   => 'Artwork',
				'post_content' => 'Artwork description',
				'post_status'  => 'publish',
			)
		);

		$this->collection_id = self::factory()->post->create(
			array(
				'post_type'    => 'ap_collection',
				'post_title'   => 'Collection',
				'post_content' => 'Collection description',
				'post_status'  => 'publish',
				'meta_input'   => array(
					'ap_collection_items' => array( $this->event_id, $this->artwork_id ),
				),
			)
		);

		CollectionRestController::register();
		do_action( 'rest_api_init' );
	}

	public function test_get_collections(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/collections' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$col = $data[0];
		$this->assertSame( $this->collection_id, $col['id'] );
		$this->assertSame( 'Collection description', $col['description'] );
		$this->assertArrayHasKey( 'thumbnail', $col );
		$this->assertCount( 2, $col['items'] );
		$first = $col['items'][0];
		$this->assertSame( 'artpulse_event', $first['type'] );
		$this->assertSame( $this->event_id, $first['id'] );
		$this->assertSame( 'Event', $first['title'] );
		$this->assertArrayHasKey( 'excerpt', $first );
		$this->assertArrayHasKey( 'thumbnail', $first );
	}

	public function test_get_single_collection(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/collection/' . $this->collection_id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( $this->collection_id, $data['id'] );
		$this->assertSame( 'Collection description', $data['description'] );
		$this->assertArrayHasKey( 'thumbnail', $data );
		$this->assertCount( 2, $data['items'] );
		$item = $data['items'][1];
		$this->assertSame( 'artpulse_artwork', $item['type'] );
		$this->assertSame( $this->artwork_id, $item['id'] );
		$this->assertSame( 'Artwork', $item['title'] );
		$this->assertArrayHasKey( 'excerpt', $item );
		$this->assertArrayHasKey( 'thumbnail', $item );
	}

	public function test_create_collection(): void {
		$user = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user );

		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/collections' );
		$req->set_param( 'title', 'New Collection' );
		$req->set_param( 'items', array( $this->event_id ) );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$id = $res->get_data()['id'];
		$this->assertNotEmpty( $id );
		$this->assertEquals( array( $this->event_id ), get_post_meta( $id, 'ap_collection_items', true ) );
	}

	public function test_artist_can_create_collection(): void {
		$user = self::factory()->user->create( array( 'role' => 'artist' ) );
		wp_set_current_user( $user );

		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/collections' );
		$req->set_param( 'title', 'Artist Collection' );
		$req->set_param( 'items', array( $this->event_id ) );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
	}
}
