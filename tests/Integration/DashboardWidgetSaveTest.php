<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\UserDashboardManager;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Admin\UserLayoutManager;

/**

 * @group INTEGRATION
 */

class DashboardWidgetSaveTest extends \WP_UnitTestCase {

	private int $user_id;

	public function set_up() {
		parent::set_up();
		$this->user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $this->user_id );

				DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', '__return_null' );
				DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', '__return_null' );

		UserDashboardManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_role_layout_changes_reflected_via_rest(): void {
				UserLayoutManager::save_role_layout( 'subscriber', array( array( 'id' => 'widget_alpha' ), array( 'id' => 'widget_beta' ) ) );

		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/ap_dashboard_layout' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
				$this->assertSame( array( 'widget_alpha', 'widget_beta' ), $res->get_data()['layout'] );

				UserLayoutManager::save_role_layout( 'subscriber', array( array( 'id' => 'widget_beta' ), array( 'id' => 'widget_alpha' ) ) );

		$req2 = new \WP_REST_Request( 'GET', '/artpulse/v1/ap_dashboard_layout' );
		$res2 = rest_get_server()->dispatch( $req2 );
				$this->assertSame( array( 'widget_beta', 'widget_alpha' ), $res2->get_data()['layout'] );
	}
}
