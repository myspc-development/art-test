<?php
use ArtPulse\Widgets\MyUpcomingEventsWidget;

/**

 * @group WIDGETS
 */

class MyUpcomingEventsWidgetTest extends \WP_UnitTestCase {
	private int $user_id;

	protected function setUp(): void {
		parent::setUp();
		$this->user_id = self::factory()->user->create();
		wp_set_current_user( $this->user_id );
	}

	public function test_render_shows_user_events(): void {
		$authored = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_event',
				'post_title'  => 'Authored Event',
				'post_status' => 'publish',
				'post_author' => $this->user_id,
				'meta_input'  => array(
					'_ap_event_date' => date( 'Y-m-d', strtotime( '+1 day' ) ),
				),
			)
		);

		$rsvp = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_event',
				'post_title'  => 'RSVP Event',
				'post_status' => 'publish',
				'meta_input'  => array(
					'_ap_event_date' => date( 'Y-m-d', strtotime( '+2 days' ) ),
				),
			)
		);

		update_user_meta( $this->user_id, 'ap_rsvp_events', array( $rsvp ) );

		$html = MyUpcomingEventsWidget::render( $this->user_id );
		$this->assertStringContainsString( 'Authored Event', $html );
		$this->assertStringContainsString( 'RSVP Event', $html );
	}

	public function test_render_shows_empty_state_when_no_events(): void {
		$html = MyUpcomingEventsWidget::render( $this->user_id );
		$this->assertStringContainsString( 'No upcoming events', $html );
	}
}
