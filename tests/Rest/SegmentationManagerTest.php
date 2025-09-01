<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Admin\SegmentationManager;


/**
 * @group REST
 */
class SegmentationManagerTest extends \WP_UnitTestCase {

	private int $user1;
	private int $user2;

	public function set_up() {
		parent::set_up();
		$this->user1 = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->user2 = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		update_user_meta( $this->user1, 'ap_membership_level', 'Gold' );
		update_user_meta( $this->user2, 'ap_membership_level', 'Silver' );
		SegmentationManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_filter_by_level(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/admin/users' );
		$req->set_param( 'level', 'Gold' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$ids = wp_list_pluck( $res->get_data(), 'ID' );
		$this->assertContains( $this->user1, $ids );
		$this->assertNotContains( $this->user2, $ids );
	}
}
