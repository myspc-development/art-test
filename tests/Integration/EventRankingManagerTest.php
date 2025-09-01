<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Discovery\EventRankingManager;
use ArtPulse\Core\EventMetrics;

/**

 * @group INTEGRATION

 */

class EventRankingManagerTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		EventRankingManager::register();
		EventMetrics::maybe_install_table();
		do_action( 'init' );
	}

	public function test_ranking_calculates_score(): void {
		$event = wp_insert_post(
			array(
				'post_title'  => 'Ranked Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		EventMetrics::log_metric( $event, 'view', 5 );
		EventMetrics::log_metric( $event, 'favorite', 2 );

		EventRankingManager::calculate_scores();
		$score = EventRankingManager::get_score( $event );
		$this->assertGreaterThan( 0, $score );
	}
}
