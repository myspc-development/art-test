<?php
namespace ArtPulse\Rest\Tests;

use ArtPulse\Rest\DashboardWidgetController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Support\WidgetIds;

/**
 * @group REST
 */
class DashboardWidgetControllerTest extends \WP_UnitTestCase {

	private int $uid;

	public function set_up() {
		parent::set_up();
		// Reset registry
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
		if ( $ref->hasProperty( 'builder_widgets' ) ) {
			$b = $ref->getProperty( 'builder_widgets' );
			$b->setAccessible( true );
			$b->setValue( null, array() );
		}

		$this->uid = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->uid );

               DashboardWidgetRegistry::register(
                       'widget_foo',
                       array(
                               'title'           => 'Foo',
                               'render_callback' => '__return_null',
                               'roles'           => array( 'administrator' ),
                       )
               );
		DashboardWidgetRegistry::register(
			'bar',
			array(
				'title'           => 'Bar',
				'render_callback' => '__return_null',
				'roles'           => array( 'editor' ),
			)
		);
                DashboardWidgetRegistry::register(
                        'baz',
                        array(
                                'title'           => 'Baz',
                                'render_callback' => '__return_null',
                        )
                );

               DashboardWidgetRegistry::register_widget(
                       'widget_foo',
                       array(
                               'label'    => 'Foo',
                               'callback' => '__return_null',
                               'roles'    => array( 'administrator' ),
                       )
               );
               DashboardWidgetRegistry::register_widget(
                       'widget_bar',
                       array(
                               'label'    => 'Bar',
                               'callback' => '__return_null',
                               'roles'    => array( 'editor' ),
                       )
               );
               DashboardWidgetRegistry::register_widget(
                       'widget_baz',
                       array(
                               'label'    => 'Baz',
                               'callback' => '__return_null',
                       )
               );

		DashboardWidgetController::register();
		do_action( 'rest_api_init' );
	}

	public function test_get_widgets_for_role_only(): void {
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/dashboard-widgets' );
		$req->set_param( 'role', 'administrator' );
		$req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$res = rest_get_server()->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$ids  = array_column( $data['available'], 'id' );
                sort( $ids );
                $this->assertSame( array( 'widget_baz', 'widget_foo' ), $ids );
		$this->assertArrayNotHasKey( 'all', $data );
	}

       public function test_get_widgets_with_all_list(): void {
               $req = new \WP_REST_Request( 'GET', '/artpulse/v1/dashboard-widgets' );
               $req->set_param( 'role', 'administrator' );
               $req->set_param( 'include_all', 'true' );
               $req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
               $res = rest_get_server()->dispatch( $req );

               $this->assertSame( 200, $res->get_status() );
               $data = $res->get_data();
               $this->assertArrayHasKey( 'all', $data );
               $all_ids = array_map( array( WidgetIds::class, 'canonicalize' ), array_column( $data['all'], 'id' ) );
               sort( $all_ids );
               $this->assertSame( array( 'widget_bar', 'widget_baz', 'widget_foo' ), $all_ids );
       }

       public function test_logged_in_user_can_list_all_widgets(): void {
               $sub = self::factory()->user->create( array( 'role' => 'subscriber' ) );
               wp_set_current_user( $sub );
               $req = new \WP_REST_Request( 'GET', '/artpulse/v1/dashboard-widgets' );
               $req->set_param( 'role', 'subscriber' );
               $req->set_param( 'include_all', 'true' );
               $req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
               $res = rest_get_server()->dispatch( $req );

               $this->assertSame( 200, $res->get_status() );
               $this->assertArrayHasKey( 'all', $res->get_data() );
       }

       public function test_get_widgets_requires_logged_in_user(): void {
               wp_set_current_user( 0 );
               $req = new \WP_REST_Request( 'GET', '/artpulse/v1/dashboard-widgets' );
               $req->set_param( 'role', 'administrator' );
               $req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
               $res = rest_get_server()->dispatch( $req );

               $this->assertSame( 401, $res->get_status() );
       }

        public function test_save_layout_with_extra_widgets(): void {
                $req = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-widgets/save' );
                $req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
                $req->set_body_params(
                        array(
                                'role'   => 'administrator',
                                'layout' => array(
                                        array(
                                                'id'      => 'widget_foo',
                                                'visible' => true,
                                        ),
                                        array(
                                                'id'      => 'widget_bar',
                                                'visible' => false,
                                        ),
                                ),
                        )
                );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 200, $res->get_status() );

                $saved = UserLayoutManager::get_role_layout( 'administrator' );
                $this->assertSame(
                        array(
                                array(
                                        'id'      => 'widget_foo',
                                        'visible' => true,
                                ),
                        ),
                        $saved['layout']
                );
        }

        public function test_save_layout_requires_nonce(): void {
                UserLayoutManager::save_role_layout(
                        'administrator',
                        array(
                                array(
                                        'id'      => 'widget_foo',
                                        'visible' => true,
                                ),
                        )
                );

                $req = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-widgets/save' );
                // Intentionally omit nonce header.
                $req->set_body_params(
                        array(
                                'role'   => 'administrator',
                                'layout' => array(
                                        array(
                                                'id'      => 'baz',
                                                'visible' => true,
                                        ),
                                ),
                        )
                );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 401, $res->get_status() );
        }

        public function test_save_layout_rejects_invalid_nonce(): void {
                UserLayoutManager::save_role_layout(
                        'administrator',
                        array(
                                array(
                                        'id'      => 'widget_foo',
                                        'visible' => true,
                                ),
                        )
                );

                $req = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-widgets/save' );
                $req->set_header( 'X-WP-Nonce', 'badnonce' );
                $req->set_body_params(
                        array(
                                'role'   => 'administrator',
                                'layout' => array(
                                        array(
                                                'id'      => 'baz',
                                                'visible' => true,
                                        ),
                                ),
                        )
                );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 401, $res->get_status() );
        }

       public function test_save_layout_requires_edit_posts_cap(): void {
		UserLayoutManager::save_role_layout(
			'administrator',
			array(
				array(
					'id'      => 'widget_foo',
					'visible' => true,
				),
			)
		);
		$subscriber = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-widgets/save' );
		$req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$req->set_body_params(
			array(
				'role'   => 'administrator',
				'layout' => array(
					array(
						'id'      => 'baz',
						'visible' => true,
					),
				),
			)
		);
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 401, $res->get_status() );

	}

	public function test_export_layout_endpoint(): void {
		UserLayoutManager::save_role_layout(
			'administrator',
			array(
				array(
					'id'      => 'widget_foo',
					'visible' => true,
				),
			)
		);
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/dashboard-widgets/export' );
		$req->set_param( 'role', 'administrator' );
		$req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
                $data = $res->get_data();
                $this->assertSame(
                        array(
                                array(
                                        'id'      => 'widget_foo',
                                        'visible' => true,
                                ),
                        ),
                        $data['layout']
                );
	}

	public function test_export_layout_requires_nonce(): void {
		UserLayoutManager::save_role_layout(
			'administrator',
			array(
				array(
					'id'      => 'widget_foo',
					'visible' => true,
				),
			)
		);
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/dashboard-widgets/export' );
		$req->set_param( 'role', 'administrator' );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 401, $res->get_status() );
	}

	public function test_export_layout_rejects_invalid_nonce(): void {
		UserLayoutManager::save_role_layout(
			'administrator',
			array(
				array(
					'id'      => 'widget_foo',
					'visible' => true,
				),
			)
		);
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/dashboard-widgets/export' );
		$req->set_param( 'role', 'administrator' );
		$req->set_header( 'X-WP-Nonce', 'badnonce' );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 401, $res->get_status() );
	}

       public function test_export_layout_requires_edit_posts_cap(): void {
		UserLayoutManager::save_role_layout(
			'administrator',
			array(
				array(
					'id'      => 'widget_foo',
					'visible' => true,
				),
			)
		);
		$subscriber = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );
		$req = new \WP_REST_Request( 'GET', '/artpulse/v1/dashboard-widgets/export' );
                $req->set_param( 'role', 'administrator' );
                $req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 401, $res->get_status() );
        }

	public function test_import_layout_endpoint(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-widgets/import' );
		$req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$req->set_body_params(
			array(
				'role'   => 'administrator',
				'layout' => array(
					array(
						'id'      => 'Widget-BaZ',
						'visible' => true,
					),
				),
			)
		);
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
                $saved = UserLayoutManager::get_role_layout( 'administrator' );
                $this->assertSame(
                        array(
                                array(
                                        'id'      => 'widget_baz',
                                        'visible' => true,
                                ),
                        ),
                        $saved['layout']
                );
                $this->assertSame( array(), $saved['logs'] );
	}

	public function test_import_layout_requires_nonce(): void {
		UserLayoutManager::save_role_layout(
			'administrator',
			array(
				array(
					'id'      => 'widget_foo',
					'visible' => true,
				),
			)
		);
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-widgets/import' );
		$req->set_body_params(
			array(
				'role'   => 'administrator',
				'layout' => array(
					array(
						'id'      => 'baz',
						'visible' => true,
					),
				),
			)
		);
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 401, $res->get_status() );

	}

	public function test_import_layout_rejects_invalid_nonce(): void {
		UserLayoutManager::save_role_layout(
			'administrator',
			array(
				array(
					'id'      => 'widget_foo',
					'visible' => true,
				),
			)
		);
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-widgets/import' );
		$req->set_header( 'X-WP-Nonce', 'badnonce' );
		$req->set_body_params(
			array(
				'role'   => 'administrator',
				'layout' => array(
					array(
						'id'      => 'baz',
						'visible' => true,
					),
				),
			)
		);
                $res = rest_get_server()->dispatch( $req );
                $this->assertSame( 401, $res->get_status() );

	}

       public function test_import_layout_requires_edit_posts_cap(): void {
		UserLayoutManager::save_role_layout(
			'administrator',
			array(
				array(
					'id'      => 'widget_foo',
					'visible' => true,
				),
			)
		);
		$subscriber = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-widgets/import' );
		$req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$req->set_body_params(
			array(
				'role'   => 'administrator',
				'layout' => array(
					array(
						'id'      => 'baz',
						'visible' => true,
					),
				),
			)
		);
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 403, $res->get_status() );

	}
}
