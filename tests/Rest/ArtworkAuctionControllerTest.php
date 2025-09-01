<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\ArtworkAuctionController;

/**
 * @group REST
 */
class ArtworkAuctionControllerTest extends \WP_UnitTestCase {

	private int $artwork_id;
	private int $user_id;

	public function set_up() {
		parent::set_up();
		$this->user_id    = self::factory()->user->create();
		$this->artwork_id = wp_insert_post(
			array(
				'post_type'   => 'artpulse_artwork',
				'post_title'  => 'Painting',
				'post_status' => 'publish',
				'post_author' => $this->user_id,
			)
		);
		update_post_meta( $this->artwork_id, 'artwork_auction_enabled', '1' );
		update_post_meta( $this->artwork_id, 'artwork_auction_start', '2000-01-01 00:00' );
		update_post_meta( $this->artwork_id, 'artwork_auction_end', '2100-01-01 00:00' );
		update_post_meta(
			$this->artwork_id,
			'artwork_bids',
			array(
				array(
					'user_id' => $this->user_id,
					'amount'  => 10,
					'time'    => 'now',
				),
			)
		);
		ArtworkAuctionController::register();
		do_action( 'rest_api_init' );
	}

	public function test_status_returns_highest_bid(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/artwork/' . $this->artwork_id . '/auction' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( 10.0, $data['highest_bid'] );
	}

	public function test_bid_adds_new_bid(): void {
		wp_set_current_user( $this->user_id );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/artwork/' . $this->artwork_id . '/bid' );
		$req->set_param( 'amount', 20 );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$bids = get_post_meta( $this->artwork_id, 'artwork_bids', true );
		$this->assertCount( 2, $bids );
		$this->assertSame( 20.0, $bids[1]['amount'] );
	}
}
