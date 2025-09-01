<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Monetization\PromoManager;


/**
 * @group REST
 */
class PromoManagerTest extends \WP_UnitTestCase {

	private int $event_id;

	public function set_up() {
		parent::set_up();
		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'draft',
			)
		);
		update_post_meta( $this->event_id, 'ap_promo_codes', array( 'TEST' => 5 ) );
		PromoManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_apply_code_returns_discount(): void {
		$req = new \WP_REST_Request( 'POST', "/artpulse/v1/event/{$this->event_id}/promo-code/apply" );
		$req->set_param( 'code', 'TEST' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( 5.0, $res->get_data()['discount'] );
	}
}
