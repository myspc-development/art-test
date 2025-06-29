<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Core\UserDashboardManager;
use ArtPulse\Community\FavoritesManager;

/**
 * @group restapi
 */
class UserDashboardDataTest extends \WP_UnitTestCase
{
    private int $user_id;
    private int $event_id;

    public function set_up(): void
    {
        parent::set_up();
        FavoritesManager::install_favorites_table();

        $this->user_id = self::factory()->user->create();
        wp_set_current_user($this->user_id);
        update_user_meta($this->user_id, 'user_badges', ['gold']);

        $this->event_id = wp_insert_post([
            'post_title'  => 'Sample Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
        update_post_meta($this->event_id, '_ap_event_date', date('Y-m-d', strtotime('+1 day')));
        update_user_meta($this->user_id, 'ap_rsvp_events', [$this->event_id]);
        FavoritesManager::add_favorite($this->user_id, $this->event_id, 'artpulse_event');
        UserDashboardManager::register();
        do_action('rest_api_init');
    }

    public function test_dashboard_data_returns_badges(): void
    {
        $request = new WP_REST_Request('GET', '/artpulse/v1/user/dashboard');
        $response = rest_get_server()->dispatch($request);
        $this->assertSame(200, $response->get_status());
        $data = $response->get_data();
        $this->assertSame(['gold'], $data['user_badges']);
        $this->assertSame(1, $data['favorite_count']);
        $this->assertSame(1, $data['rsvp_count']);
    }
}
