<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\ArtistEventsController;

/**
 * @group restapi
 */
class ArtistEventsControllerTest extends \WP_UnitTestCase {

	private int $user_id;
	private int $other_id;
	private int $publish_event;
	private int $draft_event;

	public function set_up() {
		parent::set_up();
		$this->user_id  = self::factory()->user->create();
		$this->other_id = self::factory()->user->create();
		wp_set_current_user( $this->user_id );

		$this->publish_event = wp_insert_post(
			array(
				'post_title'  => 'Pub',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $this->user_id,
				'meta_input'  => array(
					'_ap_event_date' => '2025-01-01',
					'event_end_date' => '2025-01-02',
				),
			)
		);
		$this->draft_event   = wp_insert_post(
			array(
				'post_title'  => 'Draft',
				'post_type'   => 'artpulse_event',
				'post_status' => 'draft',
				'post_author' => $this->user_id,
				'meta_input'  => array(
					'_ap_event_date' => '2025-02-01',
					'event_end_date' => '2025-02-02',
				),
			)
		);
		wp_insert_post(
			array(
				'post_title'  => 'Other',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $this->other_id,
				'meta_input'  => array(
					'_ap_event_date' => '2025-03-01',
					'event_end_date' => '2025-03-02',
				),
			)
		);

		ArtistEventsController::register();
		do_action( 'rest_api_init' );
	}

	public function test_requires_authentication(): void {
		wp_set_current_user( 0 );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/artist-events' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 401, $res->get_status() );
	}

	public function test_get_events_returns_current_user_posts(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/artist-events' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$ids  = wp_list_pluck( $data, 'id' );
		$this->assertContains( $this->publish_event, $ids );
		$this->assertContains( $this->draft_event, $ids );
		$this->assertCount( 2, $data );
		$events = wp_list_pluck( $data, 'color', 'status' );
		$this->assertSame( '#3b82f6', $events['publish'] );
		$this->assertSame( '#9ca3af', $events['draft'] );
	}
}
