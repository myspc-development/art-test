<?php
namespace ArtPulse\Community\Tests;

use WP_UnitTestCase;
use ArtPulse\Community\ReviewManager;

class ReviewManagerTest extends WP_UnitTestCase
{
    private int $user_id;
    private int $event_id;

    public function set_up(): void
    {
        parent::set_up();
        ReviewManager::install_reviews_table();
        $this->user_id = self::factory()->user->create();
        $this->event_id = wp_insert_post([
            'post_title'  => 'Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
            'post_author' => $this->user_id,
        ]);
    }

    public function test_add_and_average_rating(): void
    {
        ReviewManager::add_review($this->user_id, $this->event_id, 'artpulse_event', 5, 'Great');
        $avg = ReviewManager::get_average_rating($this->event_id, 'artpulse_event');
        $this->assertSame(5.0, $avg);
        $reviews = ReviewManager::get_reviews($this->event_id, 'artpulse_event');
        $this->assertCount(1, $reviews);
        $this->assertSame('Great', $reviews[0]['review_text']);
    }
}
