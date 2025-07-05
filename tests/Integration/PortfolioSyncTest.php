<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Integration\PortfolioSync;

class PortfolioSyncTest extends \WP_UnitTestCase
{
    public function set_up(): void
    {
        parent::set_up();
        // Ensure post types and hooks are registered
        do_action('init');
    }

    public function test_portfolio_meta_set_on_insert(): void
    {
        $user_id = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($user_id);

        $event_id = wp_insert_post([
            'post_title'  => 'Sync Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
            'post_author' => $user_id,
            'meta_input'  => [
                'event_city' => 'TestCity',
            ],
        ]);

        $portfolio = get_posts([
            'post_type'   => 'portfolio',
            'meta_key'    => '_ap_source_post',
            'meta_value'  => $event_id,
            'post_status' => 'any',
            'fields'      => 'ids',
            'numberposts' => 1,
        ]);

        $this->assertCount(1, $portfolio);
        $portfolio_id = $portfolio[0];
        $this->assertSame(
            $event_id,
            (int) get_post_meta($portfolio_id, '_ap_source_post', true)
        );
    }

    public function test_event_meta_copied_to_portfolio(): void
    {
        $user_id = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($user_id);

        $event_id = wp_insert_post([
            'post_title'  => 'Meta Sync Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
            'post_author' => $user_id,
        ]);

        update_post_meta($event_id, '_ap_event_date', '2030-01-01');
        update_post_meta($event_id, '_ap_event_venue', 'The Venue');
        update_post_meta($event_id, '_ap_event_start_time', '20:00');

        wp_update_post(['ID' => $event_id]);

        $portfolio = get_posts([
            'post_type'   => 'portfolio',
            'meta_key'    => '_ap_source_post',
            'meta_value'  => $event_id,
            'post_status' => 'any',
            'fields'      => 'ids',
            'numberposts' => 1,
        ]);

        $this->assertCount(1, $portfolio);
        $portfolio_id = $portfolio[0];
        $this->assertSame('2030-01-01', get_post_meta($portfolio_id, '_ap_event_date', true));
        $this->assertSame('The Venue', get_post_meta($portfolio_id, '_ap_event_venue', true));
        $this->assertSame('20:00', get_post_meta($portfolio_id, '_ap_event_start_time', true));
    }

    public function test_gallery_meta_copied_to_portfolio(): void
    {
        $user_id = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($user_id);

        $event_id = wp_insert_post([
            'post_title'  => 'Gallery Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
            'post_author' => $user_id,
            'meta_input'  => [
                '_ap_submission_images' => [11, 22],
            ],
        ]);

        $portfolio = get_posts([
            'post_type'   => 'portfolio',
            'meta_key'    => '_ap_source_post',
            'meta_value'  => $event_id,
            'post_status' => 'any',
            'fields'      => 'ids',
            'numberposts' => 1,
        ]);

        $this->assertCount(1, $portfolio);
        $portfolio_id = $portfolio[0];

        $this->assertSame([
            11,
            22,
        ], get_post_meta($portfolio_id, '_ap_submission_images', true));
        $this->assertSame(11, (int) get_post_thumbnail_id($portfolio_id));
    }
}
