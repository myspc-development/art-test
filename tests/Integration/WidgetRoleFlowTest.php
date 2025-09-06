<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardWidgetManager;
use ArtPulse\Core\UserDashboardManager;

/**

 * @group INTEGRATION
 */

class WidgetRoleFlowTest extends \WP_UnitTestCase {

	private int $admin;
	private int $userOne;
	private int $userTwo;

	public function set_up() {
		parent::set_up();
		$this->admin   = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->userOne = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->userTwo = self::factory()->user->create( array( 'role' => 'subscriber' ) );

				DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', '__return_null' );
				DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', '__return_null' );
				DashboardWidgetRegistry::register( 'widget_gamma', 'Gamma', '', '', '__return_null' );

		UserDashboardManager::register();
		do_action( 'rest_api_init' );
	}

	public function test_full_widget_role_flow(): void {
		// Admin sets initial role layout with visibility rules.
		wp_set_current_user( $this->admin );
		UserLayoutManager::save_role_layout(
			'subscriber',
			array(
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
				array(
					'id'      => 'widget_beta',
					'visible' => false,
				),
			)
		);

		// User one loads layout – should reflect admin config.
		wp_set_current_user( $this->userOne );
				$respOne = UserDashboardManager::getDashboardLayout();
				$dataOne = $respOne->get_data();
				$this->assertSame( array( 'widget_alpha', 'widget_beta' ), $dataOne['layout'] );
				$this->assertSame(
					array(
						'widget_alpha' => true,
						'widget_beta'  => false,
					),
					$dataOne['visibility']
				);

		// Admin updates layout and locks a widget.
		wp_set_current_user( $this->admin );
		UserLayoutManager::save_role_layout(
			'subscriber',
			array(
				array(
					'id'      => 'widget_gamma',
					'visible' => true,
				),
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
			)
		);
				update_option( 'artpulse_locked_widgets', array( 'widget_alpha' ) );

		// New user loads layout after update.
		wp_set_current_user( $this->userTwo );
				$respTwo = UserDashboardManager::getDashboardLayout();
				$dataTwo = $respTwo->get_data();
				$this->assertSame( array( 'widget_gamma', 'widget_alpha' ), $dataTwo['layout'] );
				$this->assertSame(
					array(
						'widget_gamma' => true,
						'widget_alpha' => true,
					),
					$dataTwo['visibility']
				);

		// Existing user should see updated defaults after reset.
		DashboardWidgetManager::resetUserLayout( $this->userOne );
		wp_set_current_user( $this->userOne );
				$respReset = UserDashboardManager::getDashboardLayout();
				$dataReset = $respReset->get_data();
				$this->assertSame( array( 'widget_gamma', 'widget_alpha' ), $dataReset['layout'] );
				$this->assertSame(
					array(
						'widget_gamma' => true,
						'widget_alpha' => true,
					),
					$dataReset['visibility']
				);

		// Locked widgets remain recorded.
				$this->assertContains( 'widget_alpha', get_option( 'artpulse_locked_widgets', array() ) );
	}
}
