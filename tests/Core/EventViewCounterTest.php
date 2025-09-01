<?php
namespace ArtPulse\Core\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\EventViewCounter;
use ArtPulse\Core\EventMetrics;

/**

 * @group core

 */

class EventViewCounterTest extends WP_UnitTestCase {

	private int $event_id;
	private array $logged = array();

	public function set_up() {
		parent::set_up();
		EventMetrics::install_table();
		$this->event_id = self::factory()->post->create(
			array(
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		add_action(
			'ap_event_metric_logged',
			function ( $event_id, $metric, $amount ) {
				$this->logged[] = array( $event_id, $metric, $amount );
			},
			10,
			3
		);
	}

	public function tear_down() {
		remove_all_actions( 'ap_event_metric_logged' );
		parent::tear_down();
	}

	public function test_logging_metrics_for_view_favorite_and_share(): void {
		global $post;
		$post = get_post( $this->event_id );
		add_filter( 'is_singular', '__return_true' );

		EventViewCounter::track();

		remove_filter( 'is_singular', '__return_true' );
		EventViewCounter::track_favorite( 1, $this->event_id, 'artpulse_event' );
		EventViewCounter::track_share( $this->event_id );

		$expected = array(
			array( $this->event_id, 'view', 1 ),
			array( $this->event_id, 'favorite', 1 ),
			array( $this->event_id, 'share', 1 ),
		);
		$this->assertSame( $expected, $this->logged );
	}
}
