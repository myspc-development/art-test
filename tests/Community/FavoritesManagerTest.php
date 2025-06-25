<?php
namespace ArtPulse\Community\Tests;

use WP_UnitTestCase;
use ArtPulse\Community\FavoritesManager;
use ArtPulse\Community\NotificationManager;

class FavoritesManagerTest extends WP_UnitTestCase
{
    private int $user_id;
    private int $owner_id;
    private int $event_id;

    public function set_up(): void
    {
        parent::set_up();
        FavoritesManager::install_favorites_table();
        NotificationManager::install_notifications_table();

        $this->owner_id = self::factory()->user->create(['display_name' => 'Owner']);
        $this->user_id  = self::factory()->user->create();

        $this->event_id = wp_insert_post([
            'post_title'  => 'Test Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
            'post_author' => $this->owner_id,
        ]);
    }

    public function test_notifications_created_for_owner_and_user(): void
    {
        FavoritesManager::add_favorite($this->user_id, $this->event_id, 'artpulse_event', true);

        global $wpdb;
        $table = $wpdb->prefix . 'ap_notifications';
        $rows  = $wpdb->get_results("SELECT user_id, type FROM $table ORDER BY id", ARRAY_A);

        $this->assertCount(2, $rows);
        $this->assertSame($this->owner_id, (int) $rows[0]['user_id']);
        $this->assertSame('favorite', $rows[0]['type']);
        $this->assertSame($this->user_id, (int) $rows[1]['user_id']);
        $this->assertSame('favorite_added', $rows[1]['type']);
    }
}
