<?php
namespace ArtPulse\Integration\Tests;

use function ArtPulse\Util\ap_fetch_calendar_events;

/**

 * @group INTEGRATION
 */

class EventFeedTest extends \WP_UnitTestCase {

	private int $event_id;

	public function set_up() {
		parent::set_up();
		$this->event_id = wp_insert_post(
			array(
				'post_title'  => 'Calendar Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $this->event_id, 'event_start_date', '2030-01-01' );
		update_post_meta( $this->event_id, 'event_end_date', '2030-01-02' );
		update_post_meta( $this->event_id, 'event_lat', '10.0' );
		update_post_meta( $this->event_id, 'event_lng', '20.0' );
		update_post_meta( $this->event_id, 'venue_name', 'Main Hall' );
		update_post_meta( $this->event_id, 'event_street_address', '123 Main' );
	}

	public function test_fetch_returns_event(): void {
		$events = ap_fetch_calendar_events();
		$this->assertCount( 1, $events );
		$this->assertSame( $this->event_id, $events[0]['id'] );
		$this->assertSame( 'Main Hall', $events[0]['venue'] );
	}

	public function test_fetch_filters_by_location(): void {
		$events = ap_fetch_calendar_events( 10.1, 20.1, 50 );
		$this->assertCount( 1, $events );

		$events = ap_fetch_calendar_events( 50, 50, 50 );
		$this->assertCount( 0, $events );
	}
}
