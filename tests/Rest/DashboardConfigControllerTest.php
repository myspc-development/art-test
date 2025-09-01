<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\DashboardConfigController;
use ArtPulse\Core\DashboardWidgetRegistry;
use function ArtPulse\Rest\Tests\as_role;
use function ArtPulse\Rest\Tests\nonce;
use function ArtPulse\Rest\Tests\call;
use function ArtPulse\Rest\Tests\assertStatus;
use function ArtPulse\Rest\Tests\body;

/**
 * @group REST
 */
class DashboardConfigControllerTest extends \WP_UnitTestCase {

        public function set_up() {
                parent::set_up();
                DashboardWidgetRegistry::register( 'one', 'One', '', '', '__return_null' );
                DashboardWidgetRegistry::register( 'two', 'Two', '', '', '__return_null' );
                DashboardConfigController::register();
                do_action( 'rest_api_init' );
        }

	public function test_get_requires_read_capability(): void {
                wp_set_current_user( 0 );
                $res = call( 'GET', '/artpulse/v1/dashboard-config' );
                assertStatus( $res, 403 );

                as_role( 'subscriber' );
                update_option( 'artpulse_widget_roles', array( 'subscriber' => array( 'one' ) ) );
                update_option( 'artpulse_dashboard_layouts', array( 'subscriber' => array( 'one', 'two' ) ) );
                update_option( 'artpulse_locked_widgets', array( 'two' ) );

                $res2 = call( 'GET', '/artpulse/v1/dashboard-config' );
                assertStatus( $res2, 200 );
                $data = body( $res2 );
                $this->assertArrayHasKey( 'widget_roles', $data );
                $this->assertArrayHasKey( 'role_widgets', $data );
                $this->assertArrayHasKey( 'locked', $data );
                $this->assertArrayHasKey( 'subscriber', $data['widget_roles'] );
                $this->assertContains( 'widget_one', $data['widget_roles']['subscriber'] );
                $this->assertArrayHasKey( 'subscriber', $data['role_widgets'] );
                $this->assertContains( 'widget_one', $data['role_widgets']['subscriber'] );
                $this->assertContains( 'widget_two', $data['role_widgets']['subscriber'] );
                $this->assertContains( 'widget_two', $data['locked'] );
        }

	public function test_post_requires_manage_options_and_valid_nonce(): void {
                as_role( 'subscriber' );
                $res = call(
                        'POST',
                        '/artpulse/v1/dashboard-config',
                        array(
                                'widget_roles' => array( 'subscriber' => array( 'one' ) ),
                        ),
                        array(
                                'X-WP-Nonce' => nonce( 'wp_rest' ),
                                'X-AP-Nonce' => nonce( 'ap_dashboard_config' ),
                                'Content-Type' => 'application/json',
                        )
                );
                assertStatus( $res, 403 );

                as_role( 'administrator' );

                $res_missing = call(
                        'POST',
                        '/artpulse/v1/dashboard-config',
                        array(
                                'widget_roles' => array( 'administrator' => array( 'one' ) ),
                        ),
                        array(
                                'X-WP-Nonce'   => nonce( 'wp_rest' ),
                                'Content-Type' => 'application/json',
                        )
                );
                assertStatus( $res_missing, 401 );

                $payload = array(
                        'widget_roles' => array( 'administrator' => array( 'one' ) ),
                        'role_widgets' => array( 'administrator' => array( 'one', 'two' ) ),
                        'locked'       => array( 'two' ),
                );

                $res_bad = call(
                        'POST',
                        '/artpulse/v1/dashboard-config',
                        $payload,
                        array(
                                'X-WP-Nonce' => nonce( 'wp_rest' ),
                                'X-AP-Nonce' => 'badnonce',
                                'Content-Type' => 'application/json',
                        )
                );
                assertStatus( $res_bad, 401 );

                $res_good = call(
                        'POST',
                        '/artpulse/v1/dashboard-config',
                        $payload,
                        array(
                                'X-WP-Nonce' => nonce( 'wp_rest' ),
                                'X-AP-Nonce' => nonce( 'ap_dashboard_config' ),
                                'Content-Type' => 'application/json',
                        )
                );
                assertStatus( $res_good, 200 );
                $roles = get_option( 'artpulse_widget_roles' );
                $this->assertArrayHasKey( 'administrator', $roles );
                $this->assertContains( 'widget_one', $roles['administrator'] );
                $layouts = get_option( 'artpulse_dashboard_layouts' );
                $this->assertArrayHasKey( 'administrator', $layouts );
                $this->assertContains( 'widget_one', $layouts['administrator'] );
                $this->assertContains( 'widget_two', $layouts['administrator'] );
                $locked = get_option( 'artpulse_locked_widgets' );
                $this->assertContains( 'widget_two', $locked );
        }
}
