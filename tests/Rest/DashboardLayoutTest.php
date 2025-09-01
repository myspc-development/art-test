<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Core\UserDashboardManager;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardWidgetManager;
use ArtPulse\Admin\LayoutSnapshotManager;

/**
 * @group REST
 */
class DashboardLayoutTest extends \WP_UnitTestCase {

	private int $user_id;

	public function set_up() {
		parent::set_up();
		DashboardWidgetManager::register();
		$this->user_id = self::factory()->user->create();
		wp_set_current_user( $this->user_id );
		// Remove the auto-assigned layout so tests can define their own.
		delete_user_meta( $this->user_id, 'ap_dashboard_layout' );

		UserDashboardManager::register();
		DashboardWidgetRegistry::register( 'one', 'one', '', '', '__return_null' );
		DashboardWidgetRegistry::register( 'two', 'two', '', '', '__return_null' );
		DashboardWidgetRegistry::register( 'a', 'a', '', '', '__return_null' );
		DashboardWidgetRegistry::register( 'b', 'b', '', '', '__return_null' );
		DashboardWidgetRegistry::register( 'c', 'c', '', '', '__return_null' );
		DashboardWidgetRegistry::register( 'a-', 'a-', '', '', '__return_null' );
		DashboardWidgetRegistry::register( 'bc', 'bc', '', '', '__return_null' );
		DashboardWidgetRegistry::register( 'invalidslug', 'invalid', '', '', '__return_null' );
		do_action( 'rest_api_init' );
	}

	public function test_get_returns_layout_and_visibility(): void {
                update_user_meta(
                        $this->user_id,
                        'ap_dashboard_layout',
                        array(
                                array(
                                        'id'      => 'one',
                                        'visible' => true,
                                ),
                                array(
                                        'id'      => 'two',
                                        'visible' => true,
                                ),
                        )
                );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/ap_dashboard_layout' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
                $this->assertSame( array( 'widget_one', 'widget_two' ), $data['layout'] );
                $expected = array(
                        array(
                                'id'      => 'widget_one',
                                'visible' => true,
                        ),
                        array(
                                'id'      => 'widget_two',
                                'visible' => true,
                        ),
                );
                $this->assertSame( $expected, $data['visibility'] );
        }

	public function test_post_saves_layout_and_visibility(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/ap_dashboard_layout' );
		$req->set_body_params(
			array(
				'layout' => array(
					array(
						'id'      => 'a',
						'visible' => false,
					),
					array(
						'id'      => 'b',
						'visible' => true,
					),
					array(
						'id'      => 'c',
						'visible' => true,
					),
				),
			)
		);
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
                $expected = array(
                        array(
                                'id'      => 'widget_a',
                                'visible' => false,
                        ),
                        array(
                                'id'      => 'widget_b',
                                'visible' => true,
                        ),
                        array(
                                'id'      => 'widget_c',
                                'visible' => true,
                        ),
                );
                $this->assertSame( $expected, get_user_meta( $this->user_id, 'ap_dashboard_layout', true ) );
        }

	public function test_post_sanitizes_layout_values(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/ap_dashboard_layout' );
		$req->set_body_params(
			array(
				'layout' => array(
					array( 'id' => 'A-' ),
					array( 'id' => 'B C' ),
					array( 'id' => 'in valid/slug' ),
				),
			)
		);
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
                $expected = array(
                        array(
                                'id'      => 'widget_a',
                                'visible' => true,
                        ),
                        array(
                                'id'      => 'widget_bc',
                                'visible' => true,
                        ),
                        array(
                                'id'      => 'widget_invalidslug',
                                'visible' => true,
                        ),
                );
                $this->assertSame( $expected, get_user_meta( $this->user_id, 'ap_dashboard_layout', true ) );
        }

	public function test_post_ignores_duplicates_and_invalid_ids(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/ap_dashboard_layout' );
		$req->set_body_params(
			array(
				'layout' => array(
					array( 'id' => 'a' ),
					array( 'id' => 'b' ),
					array( 'id' => 'a' ),
					array( 'id' => 'invalid' ),
				),
			)
		);
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
                $expected = array(
                        array(
                                'id'      => 'widget_a',
                                'visible' => true,
                        ),
                        array(
                                'id'      => 'widget_b',
                                'visible' => true,
                        ),
                );
                $this->assertSame( $expected, get_user_meta( $this->user_id, 'ap_dashboard_layout', true ) );
        }

       public function test_get_uses_role_default_when_no_user_meta(): void {
               $uid = self::factory()->user->create( array( 'role' => 'member' ) );
               // Remove layout assigned during registration to simulate missing meta.
               delete_user_meta( $uid, 'ap_dashboard_layout' );
               wp_set_current_user( $uid );

               tests_add_filter(
                       'ap_dashboard_default_widgets_for_role',
                       function ( $defaults, $role ) {
                               return 'member' === $role ? array( 'widget_membership', 'widget_upgrade' ) : $defaults;
                       }
               );

               $req = new \WP_REST_Request( 'GET', '/artpulse/v1/ap_dashboard_layout' );
               $res = rest_get_server()->dispatch( $req );
               $this->assertSame( 200, $res->get_status() );

               $data = $res->get_data();
               $this->assertSame( array( 'widget_membership', 'widget_upgrade' ), $data['layout'] );
               $this->assertSame(
                       array(
                               array(
                                       'id'      => 'widget_membership',
                                       'visible' => true,
                               ),
                               array(
                                       'id'      => 'widget_upgrade',
                                       'visible' => true,
                               ),
                       ),
                       $data['visibility']
               );

               remove_all_filters( 'ap_dashboard_default_widgets_for_role' );
       }

	public function test_user_register_populates_default_layout(): void {
		DashboardWidgetRegistry::register( 'widget_my_events', 'my-events', '', '', '__return_null' );
		update_option( 'ap_dashboard_widget_config', array( 'organization' => array( 'widget_my_events' ) ) );
		$uid      = self::factory()->user->create( array( 'role' => 'organization' ) );
		$expected = array(
			array(
				'id'      => 'widget_my_events',
				'visible' => true,
			),
		);
		$this->assertSame( $expected, get_user_meta( $uid, 'ap_dashboard_layout', true ) );
	}

	public function test_get_returns_layout_for_specified_role(): void {
		tests_add_filter(
			'ap_dashboard_default_widgets_for_role',
			function ( $defaults, $role ) {
				return 'member' === $role ? array( 'widget_membership' ) : $defaults;
			}
		);
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/ap_dashboard_layout' );
		$req->set_param( 'role', 'member' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( array( 'widget_membership' ), $data['layout'] );
		$this->assertSame(
			array(
				array(
					'id'      => 'widget_membership',
					'visible' => true,
				),
			),
			$data['visibility']
		);
		remove_all_filters( 'ap_dashboard_default_widgets_for_role' );
	}

	public function test_get_sanitizes_layout_values(): void {
		update_user_meta(
			$this->user_id,
			'ap_dashboard_layout',
			array(
				array( 'id' => 'A-' ),
				array( 'id' => 'B C' ),
				array( 'id' => 'in valid/slug' ),
			)
		);
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/ap_dashboard_layout' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
                $expected = array( 'widget_a', 'widget_bc', 'widget_invalidslug' );
                $this->assertSame( $expected, $res->get_data()['layout'] );
        }
    public function test_reset_route_clears_layout_and_visibility(): void {
        update_user_meta(
            $this->user_id,
            'ap_dashboard_layout',
            array(
                array(
                    'id'      => 'a',
                    'visible' => false,
                ),
                array(
                    'id'      => 'b',
                    'visible' => true,
                ),
            )
        );
        update_user_meta(
            $this->user_id,
            'ap_widget_visibility',
            array(
                'a' => false,
                'b' => true,
            )
        );

       tests_add_filter(
           'ap_dashboard_default_widgets_for_role',
           function ( $defaults, $role ) {
               return 'subscriber' === $role ? array( 'one', 'two' ) : $defaults;
           }
       );

       $req = new \WP_REST_Request( 'POST', '/artpulse/v1/ap/layout/reset' );
       $res = rest_get_server()->dispatch( $req );
       $this->assertSame( 200, $res->get_status() );
       $data = $res->get_data();
       $this->assertTrue( $data['reset'] );
       $this->assertSame( array( 'widget_one', 'widget_two' ), $data['layout'] );
       $this->assertSame(
           array(
               array(
                   'id'      => 'widget_one',
                   'visible' => true,
               ),
               array(
                   'id'      => 'widget_two',
                   'visible' => true,
               ),
           ),
           $data['visibility']
       );
       $this->assertEmpty( get_user_meta( $this->user_id, 'ap_dashboard_layout', true ) );
       $this->assertEmpty( get_user_meta( $this->user_id, 'ap_widget_visibility', true ) );
       remove_all_filters( 'ap_dashboard_default_widgets_for_role' );
}

    public function test_revert_route_restores_last_snapshot(): void {
        update_user_meta(
            $this->user_id,
            'ap_dashboard_layout',
            array(
                array(
                    'id'      => 'a',
                    'visible' => true,
                ),
                array(
                    'id'      => 'b',
                    'visible' => true,
                ),
            )
        );
        LayoutSnapshotManager::snapshot( $this->user_id, 'subscriber' );
        update_user_meta(
            $this->user_id,
            'ap_dashboard_layout',
            array(
                array(
                    'id'      => 'c',
                    'visible' => true,
                ),
            )
        );
        $req = new \WP_REST_Request( 'POST', '/artpulse/v1/ap/layout/revert' );
        $res = rest_get_server()->dispatch( $req );
        $this->assertSame( 200, $res->get_status() );
        $data = $res->get_data();
        $this->assertTrue( $data['reverted'] );
        $this->assertSame(
            array(
                array(
                    'id'      => 'widget_a',
                    'visible' => true,
                ),
                array(
                    'id'      => 'widget_b',
                    'visible' => true,
                ),
            ),
            get_user_meta( $this->user_id, 'ap_dashboard_layout', true )
        );
    }
}
