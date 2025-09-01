<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\FavoriteRestController;
use ArtPulse\Community\FavoritesManager;

/**
 * @group REST
 */
class FavoriteRestControllerTest extends \WP_UnitTestCase {

	private int $user_id;
	private array $posts;

	public function set_up() {
		parent::set_up();
		FavoritesManager::install_favorites_table();

		$this->user_id = self::factory()->user->create();
		wp_set_current_user( $this->user_id );

		$this->posts = array();
		foreach ( array( 'artpulse_event', 'artpulse_artist', 'artpulse_org', 'artpulse_artwork' ) as $type ) {
			$this->posts[ $type ] = self::factory()->post->create(
				array(
					'post_type'   => $type,
					'post_title'  => ucfirst( $type ),
					'post_status' => 'publish',
				)
			);
		}

		FavoriteRestController::register();
		do_action( 'rest_api_init' );
	}

	public function test_add_and_remove_favorites_for_each_type(): void {
		foreach ( $this->posts as $type => $id ) {
			// Add
			$add = new \WP_REST_Request( 'POST', '/artpulse/v1/favorites' );
			$add->set_param( 'object_id', $id );
			$add->set_param( 'object_type', $type );
			$res = rest_get_server()->dispatch( $add );
			$this->assertSame( 200, $res->get_status() );
			$this->assertSame( 'added', $res->get_data()['status'] );
			$this->assertTrue( FavoritesManager::is_favorited( $this->user_id, $id, $type ) );
			$this->assertSame( '1', get_post_meta( $id, 'ap_favorite_count', true ) );

			// Remove
			$remove = new \WP_REST_Request( 'POST', '/artpulse/v1/favorites' );
			$remove->set_param( 'object_id', $id );
			$remove->set_param( 'object_type', $type );
			$res = rest_get_server()->dispatch( $remove );
			$this->assertSame( 200, $res->get_status() );
			$this->assertSame( 'removed', $res->get_data()['status'] );
			$this->assertFalse( FavoritesManager::is_favorited( $this->user_id, $id, $type ) );
			$this->assertSame( '0', get_post_meta( $id, 'ap_favorite_count', true ) );
		}
	}

	public function test_list_favorites_endpoint(): void {
		$id   = array_values( $this->posts )[0];
		$type = array_keys( $this->posts )[0];
		FavoritesManager::add_favorite( $this->user_id, $id, $type );

		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/favorites' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( $id, $data[0]['object_id'] );
	}
}
