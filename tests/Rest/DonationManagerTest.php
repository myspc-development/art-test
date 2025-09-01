<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Monetization\DonationManager;


/**
 * @group REST
 */
class DonationManagerTest extends \WP_UnitTestCase {

	private int $artist_id;
	private int $user_id;

	public function set_up() {
		parent::set_up();
		\ArtPulse\Monetization\DonationManager::maybe_install_table();
		do_action( 'init' );
		$this->artist_id = self::factory()->user->create();
		$this->user_id   = self::factory()->user->create();
		DonationManager::register();
		do_action( 'rest_api_init' );
		wp_set_current_user( $this->user_id );
	}

	public function test_create_donation_inserts_row(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_donations';
		$this->assertSame( '0', $wpdb->get_var( "SELECT COUNT(*) FROM $table" ) );

		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/donations' );
		$req->set_param( 'artist_id', $this->artist_id );
		$req->set_param( 'amount', 10 );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );

		$this->assertSame( '1', $wpdb->get_var( "SELECT COUNT(*) FROM $table" ) );
	}
}
