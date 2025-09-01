<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Marketplace\AuctionManager;


/**
 * @group REST
 */
class AuctionManagerTest extends \WP_UnitTestCase {

	private int $artwork_id;
	private int $artist_id;
	private int $bidder_id;

	public function set_up() {
		parent::set_up();
		\ArtPulse\DB\create_monetization_tables();
		$this->artist_id  = self::factory()->user->create();
		$this->bidder_id  = self::factory()->user->create();
		$this->artwork_id = wp_insert_post(
			array(
				'post_type'   => 'artpulse_artwork',
				'post_title'  => 'Auction Piece',
				'post_status' => 'publish',
				'post_author' => $this->artist_id,
			)
		);
		AuctionManager::register();
		do_action( 'init' );
		do_action( 'rest_api_init' );
		global $wpdb;
		$table = $wpdb->prefix . 'ap_auctions';
		$wpdb->insert(
			$table,
			array(
				'artwork_id'    => $this->artwork_id,
				'start_time'    => current_time( 'mysql', true ),
				'end_time'      => date( 'Y-m-d H:i:s', time() + 3600 ),
				'reserve_price' => 0,
				'buy_now_price' => 0,
				'min_increment' => 1,
				'starting_bid'  => 5,
				'is_active'     => 1,
			)
		);
	}

	public function test_place_bid_adds_record(): void {
		wp_set_current_user( $this->bidder_id );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/bids' );
		$req->set_param( 'artwork_id', $this->artwork_id );
		$req->set_param( 'amount', 6 );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		global $wpdb;
		$table = $wpdb->prefix . 'ap_bids';
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE artwork_id = %d", $this->artwork_id ) );
		$this->assertSame( 1, $count );
	}

	public function test_list_live_returns_auction(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/auctions/live' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( $this->artwork_id, $data[0]['artwork_id'] );
	}
}
