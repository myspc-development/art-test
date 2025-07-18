<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\UserDashboardManager;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Admin\UserLayoutManager;
use WP_REST_Request;

class DashboardWidgetSaveTest extends \WP_UnitTestCase
{
    private int $user_id;

    public function set_up(): void
    {
        parent::set_up();
        $this->user_id = self::factory()->user->create([ 'role' => 'subscriber' ]);
        wp_set_current_user($this->user_id);

        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', '__return_null');
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', '__return_null');

        UserDashboardManager::register();
        do_action('rest_api_init');
    }

    public function test_role_layout_changes_reflected_via_rest(): void
    {
        UserLayoutManager::save_role_layout('subscriber', [ ['id' => 'alpha'], ['id' => 'beta'] ]);

        $req = new WP_REST_Request('GET', '/artpulse/v1/ap/layout');
        $res = rest_get_server()->dispatch($req);
        $this->assertSame(200, $res->get_status());
        $this->assertSame(['alpha', 'beta'], $res->get_data()['layout']);

        UserLayoutManager::save_role_layout('subscriber', [ ['id' => 'beta'], ['id' => 'alpha'] ]);

        $req2 = new WP_REST_Request('GET', '/artpulse/v1/ap/layout');
        $res2 = rest_get_server()->dispatch($req2);
        $this->assertSame(['beta', 'alpha'], $res2->get_data()['layout']);
    }
}
