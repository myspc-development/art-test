<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Core\UserDashboardManager;

/**
 * @group restapi
 */
class UserDashboardDataTest extends \WP_UnitTestCase
{
    private int $user_id;

    public function set_up(): void
    {
        parent::set_up();
        $this->user_id = self::factory()->user->create();
        wp_set_current_user($this->user_id);
        update_user_meta($this->user_id, 'user_badges', ['gold']);
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
    }
}
