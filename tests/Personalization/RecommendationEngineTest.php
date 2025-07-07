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

    public function test_recommendations_sorted_by_metrics_when_no_activity(): void
    {
        $e1 = wp_insert_post([
            'post_title'  => 'A',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
        $e2 = wp_insert_post([
            'post_title'  => 'B',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
        $e3 = wp_insert_post([
            'post_title'  => 'C',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);

        update_post_meta($e1, 'ap_favorite_count', 10);
        update_post_meta($e1, 'event_rsvp_list', [1, 2]);
        update_post_meta($e1, 'view_count', 20);

        update_post_meta($e2, 'ap_favorite_count', 5);
        update_post_meta($e2, 'event_rsvp_list', [1, 2, 3]);
        update_post_meta($e2, 'view_count', 30);

        update_post_meta($e3, 'ap_favorite_count', 5);
        update_post_meta($e3, 'event_rsvp_list', [1]);
        update_post_meta($e3, 'view_count', 100);

        delete_transient('ap_rec_event_' . $this->user_id);
        $recs = RecommendationEngine::get_recommendations($this->user_id, 'event', 3);

        $this->assertCount(3, $recs);
        $this->assertSame([$e1, $e2, $e3], array_column($recs, 'id'));
    }
}
