<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Rest\DashboardWidgetController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\UserDashboardManager;

/**

 * @group INTEGRATION
 */

class DashboardBuilderMappingTest extends \WP_UnitTestCase {
	private int $admin;

	public function set_up() {
		parent::set_up();
		// reset registry
		$ref2  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop2 = $ref2->getProperty( 'widgets' );
		$prop2->setAccessible( true );
		$prop2->setValue( null, array() );
		if ( $ref2->hasProperty( 'builder_widgets' ) ) {
			$bw = $ref2->getProperty( 'builder_widgets' );
			$bw->setAccessible( true );
			$bw->setValue( null, array() );
		}

		$this->admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->admin );

		// register builder widgets
		DashboardWidgetRegistry::register(
			'news_feed',
			array(
				'title'           => 'News Feed',
				'render_callback' => '__return_null',
				'roles'           => array( 'member' ),
			)
		);
		DashboardWidgetRegistry::register(
			'my_favorites',
			array(
				'title'           => 'Favorites',
				'render_callback' => '__return_null',
				'roles'           => array( 'member' ),
			)
		);

		// register core widgets
		DashboardWidgetRegistry::register_widget(
			'widget_news',
			array(
				'label'    => 'News',
				'callback' => '__return_null',
				'roles'    => array( 'member' ),
			)
		);
		DashboardWidgetRegistry::register_widget(
			'widget_my_favorites',
			array(
				'label'    => 'Favorites',
				'callback' => '__return_null',
				'roles'    => array( 'member' ),
			)
		);

		DashboardWidgetController::register();
		UserDashboardManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_builder_layout_maps_to_core_ids(): void {
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/dashboard-widgets/save' );
		$req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$req->set_body_params(
			array(
				'role'   => 'member',
				'layout' => array(
					array(
						'id'      => 'news_feed',
						'visible' => true,
					),
					array(
						'id'      => 'my_favorites',
						'visible' => false,
					),
				),
			)
		);
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );

		$config = get_option( 'ap_dashboard_widget_config' );
		$this->assertSame(
			array(
				'layout' => array(
					array(
						'id'      => 'widget_news',
						'visible' => true,
					),
					array(
						'id'      => 'widget_my_favorites',
						'visible' => false,
					),
				),
			),
			$config['member']
		);

		$uid = self::factory()->user->create( array( 'role' => 'member' ) );
		wp_set_current_user( $uid );

		$resp = UserDashboardManager::getDashboardLayout();
		$data = $resp->get_data();
		$this->assertSame( array( 'widget_news', 'widget_my_favorites' ), $data['layout'] );
		$this->assertSame(
			array(
				'widget_news'         => true,
				'widget_my_favorites' => false,
			),
			$data['visibility']
		);
	}

	public function test_map_to_core_id_helper(): void {
		$expected = array(
			'revenue_summary'         => 'artist_revenue_summary',
			'audience_crm'            => 'audience_crm',
			'branding_settings_panel' => 'branding_settings_panel',
		);

		foreach ( $expected as $builder => $core ) {
			$this->assertSame(
				$core,
				DashboardWidgetRegistry::map_to_core_id( $builder )
			);
		}
	}
}
