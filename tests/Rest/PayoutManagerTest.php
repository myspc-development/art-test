<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Monetization\PayoutManager;


/**
 * @group REST
 */
class PayoutManagerTest extends \WP_UnitTestCase {

	private int $artist_id;

	public function set_up() {
		parent::set_up();
		\ArtPulse\Monetization\PayoutManager::maybe_install_table();
		do_action( 'init' );
		$this->artist_id = self::factory()->user->create();
		global $wpdb;
		$table = $wpdb->prefix . 'ap_payouts';
		$wpdb->insert(
			$table,
			array(
				'artist_id'   => $this->artist_id,
				'amount'      => 5.5,
				'status'      => 'paid',
				'method'      => 'paypal',
				'payout_date' => current_time( 'mysql' ),
			)
		);
		PayoutManager::register();
		do_action( 'rest_api_init' );
		wp_set_current_user( $this->artist_id );
	}

	public function test_list_payouts_returns_records(): void {
		$req  = new \WP_REST_Request( 'GET', '/artpulse/v1/user/payouts' );
		$res  = rest_get_server()->dispatch( $req );
		$data = $res->get_data();
		$this->assertSame( 200, $res->get_status() );
		$this->assertCount( 1, $data['payouts'] );
		$this->assertArrayHasKey( 'balance', $data );
	}

	public function test_update_settings_updates_method(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/user/payouts/settings' );
		$req->set_param( 'method', 'bank' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( 'bank', get_user_meta( $this->artist_id, 'ap_payout_method', true ) );
	}

	public function test_get_balance_returns_zero_when_table_missing(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_payouts';
		$wpdb->query( "DROP TABLE IF EXISTS $table" );

		$balance = PayoutManager::get_balance( $this->artist_id );
		$this->assertSame( 0.0, $balance );

		// Recreate table for other tests.
		PayoutManager::maybe_install_table();
	}
}
