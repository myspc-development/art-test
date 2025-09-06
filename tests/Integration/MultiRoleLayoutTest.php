<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\UserDashboardManager;

/**

 * @group INTEGRATION
 */

class MultiRoleLayoutTest extends \WP_UnitTestCase {

	private int $userId;

	public function set_up() {
		parent::set_up();
		// Ensure custom roles exist
		if ( ! get_role( 'member' ) ) {
			add_role( 'member', 'Member' );
		}
		if ( ! get_role( 'artist' ) ) {
			add_role( 'artist', 'Artist' );
		}

				DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', '__return_null', array( 'roles' => array( 'member' ) ) );
				DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', '__return_null', array( 'roles' => array( 'artist' ) ) );
				DashboardWidgetRegistry::register( 'widget_shared', 'Shared', '', '', '__return_null', array( 'roles' => array( 'member', 'artist' ) ) );

		UserLayoutManager::save_role_layout(
			'member',
			array(
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
				array(
					'id'      => 'widget_shared',
					'visible' => true,
				),
			)
		);
		UserLayoutManager::save_role_layout(
			'artist',
			array(
				array(
					'id'      => 'widget_beta',
					'visible' => false,
				),
				array(
					'id'      => 'widget_shared',
					'visible' => true,
				),
			)
		);

		$this->userId = self::factory()->user->create( array( 'role' => 'member' ) );
		$user         = get_user_by( 'id', $this->userId );
		$user->add_role( 'artist' );

		UserDashboardManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_layout_merges_multiple_roles(): void {
		wp_set_current_user( $this->userId );
		$resp = UserDashboardManager::getDashboardLayout();
		$data = $resp->get_data();
				$this->assertSame( array( 'widget_alpha', 'widget_shared', 'widget_beta' ), $data['layout'] );
				$this->assertSame(
					array(
						'widget_alpha'  => true,
						'widget_shared' => true,
						'widget_beta'   => false,
					),
					$data['visibility']
				);
	}
}
