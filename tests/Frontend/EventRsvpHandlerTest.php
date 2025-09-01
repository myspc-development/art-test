<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use ArtPulse\Frontend\EventRsvpHandler;

/**

 * @group frontend

 */

class EventRsvpHandlerTest extends WP_UnitTestCase {

	private int $user_id;
	private int $future_event;
	private int $past_event;

	public function set_up() {
		parent::set_up();
		$this->user_id = self::factory()->user->create();

		$this->future_event = wp_insert_post(
			array(
				'post_title'  => 'Future Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $this->future_event, '_ap_event_date', date( 'Y-m-d', strtotime( '+1 day' ) ) );

		$d1 = date( 'Y-m-d', strtotime( '-2 days' ) );
		$d2 = date( 'Y-m-d', strtotime( '-1 day' ) );
		update_post_meta(
			$this->future_event,
			'event_rsvp_history',
			array(
				$d1 => 1,
				$d2 => 2,
			)
		);

		$this->past_event = wp_insert_post(
			array(
				'post_title'  => 'Past Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $this->past_event, '_ap_event_date', date( 'Y-m-d', strtotime( '-1 day' ) ) );

		$d3 = date( 'Y-m-d' );
		update_post_meta(
			$this->past_event,
			'event_rsvp_history',
			array(
				$d2 => 1,
				$d3 => 2,
			)
		);

		update_user_meta(
			$this->user_id,
			'ap_rsvp_events',
			array(
				$this->future_event,
				$this->past_event,
			)
		);
	}

	public function test_get_rsvp_summary_counts_events(): void {
		$summary = EventRsvpHandler::get_rsvp_summary_for_user( $this->user_id );

		$this->assertSame( 1, $summary['going'] );
		$this->assertSame( 1, $summary['interested'] );

		$d1       = date( 'Y-m-d', strtotime( '-2 days' ) );
		$d2       = date( 'Y-m-d', strtotime( '-1 day' ) );
		$d3       = date( 'Y-m-d' );
		$expected = array(
			array(
				'date'  => $d1,
				'count' => 1,
			),
			array(
				'date'  => $d2,
				'count' => 3,
			),
			array(
				'date'  => $d3,
				'count' => 2,
			),
		);
		$this->assertSame( $expected, $summary['trend'] );
	}
}
