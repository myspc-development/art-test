<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Core\VisitTracker;
use ArtPulse\Rest\VisitRestController;

/**
 * @group REST
 */
class VisitRestControllerTest extends \WP_UnitTestCase {

	private int $event_id;

	public function set_up() {
		parent::set_up();
		VisitTracker::install_table();
		add_action( 'rest_api_init', array( VisitRestController::class, 'register' ) );
		do_action( 'rest_api_init' );

		$this->event_id = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		wp_set_current_user( self::factory()->user->create() );
	}

	public function test_public_checkin_stores_visit(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/checkin' );
		$req->set_param( 'event_id', $this->event_id );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );

		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_checkins';
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE event_id=%d", $this->event_id ) );
		$this->assertSame( 1, $count );
	}

	public function test_export_csv(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_event_checkins';
		$wpdb->insert(
			$table,
			array(
				'event_id'    => $this->event_id,
				'user_id'     => 0,
				'institution' => 'School',
				'group_size'  => 20,
				'visit_date'  => current_time( 'mysql' ),
			)
		);

		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/event/' . $this->event_id . '/visits/export' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$csv = $res->get_data();
		$this->assertStringContainsString( 'institution,group_size,user_id,visit_date', $csv );
	}
}
