<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Marketplace\PromotionManager;


/**
 * @group REST
 */
class PromotionManagerTest extends \WP_UnitTestCase {

	private int $artwork_id;

	public function set_up() {
		parent::set_up();
		\ArtPulse\DB\create_monetization_tables();
		wp_set_current_user( self::factory()->user->create() );
		do_action( 'init' );
		$this->artwork_id = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_artwork',
				'post_title'  => 'Artwork',
				'post_status' => 'publish',
			)
		);
		PromotionManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_list_promoted(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_promotions';
		$wpdb->insert(
			$table,
			array(
				'artwork_id'     => $this->artwork_id,
				'start_date'     => date( 'Y-m-d', strtotime( '-1 day' ) ),
				'end_date'       => date( 'Y-m-d', strtotime( '+1 day' ) ),
				'type'           => 'featured',
				'priority_level' => 1,
			)
		);

		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/promoted' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertCount( 1, $data );
		$this->assertSame( $this->artwork_id, (int) $data[0]['artwork_id'] );
	}
}
