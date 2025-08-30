<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\DashboardConfigController;
use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * @group restapi
 */
class DashboardConfigControllerTest extends \WP_UnitTestCase {

	private int $admin_id;
	private int $user_id;

	public function set_up() {
		parent::set_up();
		$this->admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->user_id  = self::factory()->user->create( array( 'role' => 'subscriber' ) );
                DashboardWidgetRegistry::register( 'one', 'One', '', '', '__return_null' );
                DashboardWidgetRegistry::register( 'two', 'Two', '', '', '__return_null' );
		DashboardConfigController::register();
		do_action( 'rest_api_init' );
	}

	public function test_get_requires_read_capability(): void {
		wp_set_current_user( 0 );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/dashboard-config' );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 403, $res->get_status() );

		wp_set_current_user( $this->user_id );
                update_option( 'artpulse_widget_roles', array( 'subscriber' => array( 'one' ) ) );
                update_option( 'artpulse_dashboard_layouts', array( 'subscriber' => array( 'one', 'two' ) ) );
                update_option( 'artpulse_locked_widgets', array( 'two' ) );

		$req2 = new \WP_REST_Request( 'GET', '/artpulse/v1/dashboard-config' );
		$res2 = rest_get_server()->dispatch( $req2 );
		$this->assertSame( 200, $res2->get_status() );
		$data = $res2->get_data();
                $this->assertSame( array( 'subscriber' => array( 'widget_one' ) ), $data['widget_roles'] );
                $this->assertSame( array( 'subscriber' => array( 'widget_one', 'widget_two' ) ), $data['role_widgets'] );
                $this->assertSame( array( 'widget_two' ), $data['locked'] );
	}

	public function test_post_requires_manage_options_and_valid_nonce(): void {
		wp_set_current_user( $this->user_id );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-config' );
		$req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$req->set_body_params( array() );
		$req->set_header( 'Content-Type', 'application/json' );
		$req->set_body(
			json_encode(
                                array(
                                        'widget_roles' => array( 'subscriber' => array( 'one' ) ),
                                )
			)
		);
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 403, $res->get_status() );

		wp_set_current_user( $this->admin_id );
		$bad = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-config' );
		$bad->set_body_params( array() );
		$bad->set_header( 'Content-Type', 'application/json' );
		$bad->set_header( 'X-WP-Nonce', 'badnonce' );
		$bad->set_body(
			json_encode(
                                array(
                                        'widget_roles' => array( 'administrator' => array( 'one' ) ),
                                        'role_widgets' => array( 'administrator' => array( 'one', 'two' ) ),
                                        'locked'       => array( 'two' ),
                                )
			)
		);
                $res_bad = rest_get_server()->dispatch( $bad );
                $this->assertSame( 401, $res_bad->get_status() );

		$good = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-config' );
		$good->set_body_params( array() );
		$good->set_header( 'Content-Type', 'application/json' );
		$good->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$good->set_body(
			json_encode(
                                array(
                                        'widget_roles' => array( 'administrator' => array( 'one' ) ),
                                        'role_widgets' => array( 'administrator' => array( 'one', 'two' ) ),
                                        'locked'       => array( 'two' ),
                                )
			)
		);
		$res_good = rest_get_server()->dispatch( $good );
		$this->assertSame( 200, $res_good->get_status() );
                $this->assertSame( array( 'administrator' => array( 'widget_one' ) ), get_option( 'artpulse_widget_roles' ) );
                $this->assertSame( array( 'administrator' => array( 'widget_one', 'widget_two' ) ), get_option( 'artpulse_dashboard_layouts' ) );
                $this->assertSame( array( 'widget_two' ), get_option( 'artpulse_locked_widgets' ) );
	}
}
