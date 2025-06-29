<?php
namespace ArtPulse\Personalization\Tests;

use WP_UnitTestCase;
use ArtPulse\Personalization\RecommendationEngine;

class RecommendationEngineTest extends WP_UnitTestCase
{
    private int $user_id;
    private int $event_id;

    public function set_up(): void
    {
        parent::set_up();
        RecommendationEngine::install_table();
        $this->user_id  = self::factory()->user->create();
        $this->event_id = wp_insert_post([
            'post_title'  => 'Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
    }

    public function test_log_and_fetch_viewed_objects(): void
    {
        RecommendationEngine::log($this->user_id, 'event', $this->event_id, 'view');
        $ids = RecommendationEngine::get_viewed_objects($this->user_id, 'event');
        $this->assertContains($this->event_id, $ids);
    }
}
