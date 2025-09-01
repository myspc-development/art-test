<?php
namespace ArtPulse\Personalization\Tests;

use ArtPulse\Personalization\WeeklyRecommendations;
use ArtPulse\Personalization\RecommendationEngine;

/**
 * @group PERSONALIZATION
 */
class WeeklyRecommendationsTest extends \WP_UnitTestCase {

	private int $user;
	private int $event;

	public function set_up() {
		parent::set_up();
		RecommendationEngine::install_table();
		$this->user = self::factory()->user->create();
		wp_set_current_user( $this->user );
		$this->event = wp_insert_post(
			array(
				'post_title'  => 'Rec Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $this->event, 'ap_favorite_count', 10 );
		WeeklyRecommendations::register();
	}

	public function test_generate_and_schedule(): void {
		WeeklyRecommendations::schedule_cron();
		$this->assertNotFalse( wp_next_scheduled( 'ap_generate_recommendations' ) );
		WeeklyRecommendations::generate();
		$recs = get_user_meta( $this->user, 'ap_weekly_recommendations', true );
		$this->assertContains( $this->event, $recs );
	}
}
