<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Monetization\TipManager;


/**
 * @group REST
 */
class TipManagerTest extends \WP_UnitTestCase {

	private int $artist_id;
	private int $fan_id;

	public function set_up() {
		parent::set_up();
		\ArtPulse\Monetization\TipManager::maybe_install_table();
		do_action( 'init' );
		$this->artist_id = self::factory()->user->create();
		$this->fan_id    = self::factory()->user->create();
		TipManager::register();
		do_action( 'rest_api_init' );
		wp_set_current_user( $this->fan_id );
	}

	public function test_record_tip_inserts_row(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_tips';
		$this->assertSame( '0', $wpdb->get_var( "SELECT COUNT(*) FROM $table" ) );

		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/artist/' . $this->artist_id . '/tip' );
		$req->set_param( 'amount', 5 );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );

		$this->assertSame( '1', $wpdb->get_var( "SELECT COUNT(*) FROM $table" ) );
	}
}
