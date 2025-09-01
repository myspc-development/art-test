<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Monetization\EventPromotionManager;


/**
 * @group REST
 */
class EventPromotionManagerTest extends \WP_UnitTestCase {

	private int $event_id;
	private int $user_id;

	public function set_up() {
		parent::set_up();
		do_action( 'init' );
		$this->user_id  = self::factory()->user->create();
		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'draft',
				'post_author' => $this->user_id,
			)
		);
		EventPromotionManager::register();
		do_action( 'rest_api_init' );
		wp_set_current_user( $this->user_id );
	}

	public function test_feature_event_sets_meta(): void {
		$req = new \WP_REST_Request( 'POST', "/artpulse/v1/event/{$this->event_id}/feature" );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$this->assertSame( '1', get_post_meta( $this->event_id, 'ap_featured', true ) );
	}
}
