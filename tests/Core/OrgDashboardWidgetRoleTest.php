<?php
namespace ArtPulse\Core\Tests {
	use PHPUnit\Framework\TestCase;
	use ArtPulse\Core\DashboardController;


	require_once __DIR__ . '/../../src/Core/DashboardController.php';
	require_once __DIR__ . '/../../src/Dashboard/WidgetVisibilityManager.php';
	require_once __DIR__ . '/../../includes/widget-loader.php';
	require_once __DIR__ . '/../../src/Core/DashboardWidgetRegistry.php';

	use ArtPulse\Tests\Stubs\MockStorage;

	/**

	 * @group CORE

	 */

	class OrgDashboardWidgetRoleTest extends TestCase {

		protected function setUp(): void {
			MockStorage::$current_roles = array();
			MockStorage::$removed       = array();
			MockStorage::$notice        = array();
			MockStorage::$user_meta     = array();
			MockStorage::$screen        = 'dashboard';
			MockStorage::$options       = array();
		}

		public function test_org_role_widgets(): void {
			$widgets = DashboardController::get_widgets_for_role( 'organization' );
			$this->assertNotEmpty( $widgets );
			$admin = DashboardController::get_widgets_for_role( 'administrator' );
			$this->assertSame( array(), $admin );
		}

		public function test_analytics_widget_visible_for_org_user(): void {
			MockStorage::$current_roles = array( 'organization' );
			\ArtPulse\Dashboard\WidgetVisibilityManager::filter_visible_widgets();
			$this->assertEmpty( MockStorage::$removed );
		}

		public function test_member_has_no_analytics_capability(): void {
			MockStorage::$current_roles = array( 'member' );
			$this->assertFalse( \current_user_can( 'view_analytics' ) );
			\ArtPulse\Dashboard\WidgetVisibilityManager::filter_visible_widgets();
			$this->assertSame( 'artpulse_analytics_widget', MockStorage::$removed[0][0] );
			$this->assertEmpty( MockStorage::$notice );
		}

		public function test_no_widgets_outputs_fallback_message(): void {
			global $wp_meta_boxes;
			MockStorage::$current_roles = array( 'member' );
			$wp_meta_boxes              = array( 'dashboard' => array() );
			ob_start();
			\ArtPulse\Dashboard\WidgetVisibilityManager::render_empty_state_notice();
			$html = ob_get_clean();
			$this->assertStringContainsString( 'No dashboard content available', $html );
		}

		public function test_filter_allows_custom_visibility_rule(): void {
			tests_add_filter(
				'ap_dashboard_widget_visibility_rules',
				function ( $rules ) {
					$rules['custom_widget'] = array(
						'exclude_roles' => array( 'member' ),
					);
					return $rules;
				}
			);
			MockStorage::$current_roles = array( 'member' );
			\ArtPulse\Dashboard\WidgetVisibilityManager::filter_visible_widgets();
			$ids = array_column( MockStorage::$removed, 0 );
			$this->assertContains( 'custom_widget', $ids );
		}

		public function test_multiple_roles_evaluated(): void {
			MockStorage::$current_roles = array( 'organization', 'custom' );
			\ArtPulse\Dashboard\WidgetVisibilityManager::filter_visible_widgets();
			$ids = array_column( MockStorage::$removed, 0 );
			$this->assertNotContains( 'artpulse_analytics_widget', $ids );
		}

		public function test_unknown_role_leaves_widgets(): void {
			MockStorage::$current_roles = array( 'stranger' );
			\ArtPulse\Dashboard\WidgetVisibilityManager::filter_visible_widgets();
			$this->assertEmpty( MockStorage::$removed );
		}

		public function test_empty_help_url_filter_outputs_link(): void {
			tests_add_filter( 'ap_dashboard_empty_help_url', fn() => 'https://example.com/help' );
			global $wp_meta_boxes;
			$wp_meta_boxes = array( 'dashboard' => array() );
			ob_start();
			\ArtPulse\Dashboard\WidgetVisibilityManager::render_empty_state_notice();
			$html = ob_get_clean();
			$this->assertStringContainsString( 'https://example.com/help', $html );
		}

		public function test_filter_visible_widgets_accepts_user_param(): void {
			$user = (object) array(
				'ID'    => 5,
				'roles' => array( 'organization' ),
			);
			\ArtPulse\Dashboard\WidgetVisibilityManager::filter_visible_widgets( $user );
			$this->assertEmpty( MockStorage::$removed );
		}

		public function test_filter_visible_widgets_handles_invalid_user(): void {
			MockStorage::$current_roles = array( 'organization' );
			\ArtPulse\Dashboard\WidgetVisibilityManager::filter_visible_widgets( 'bad' );
			$this->assertEmpty( self::$removed );
		}


		public function test_donor_widget_visible_for_org_role(): void {
			MockStorage::$current_roles = array( 'organization' );
			\ArtPulse\Dashboard\WidgetVisibilityManager::filter_visible_widgets();
			$this->assertNotContains( 'ap_donor_activity', array_column( MockStorage::$removed, 0 ) );
		}

		public function test_saved_rules_exclude_roles(): void {
			MockStorage::$options['artpulse_widget_roles'] = array(
				'ap_donor_activity' => array(
					'exclude_roles' => array( 'member' ),
				),
			);
			MockStorage::$current_roles                    = array( 'member' );
			\ArtPulse\Dashboard\WidgetVisibilityManager::filter_visible_widgets();
			$ids = array_column( MockStorage::$removed, 0 );
			$this->assertContains( 'ap_donor_activity', $ids );
		}

		public function test_saved_rules_capability(): void {
			MockStorage::$options['artpulse_widget_roles'] = array(
				'ap_donor_activity' => array(
					'capability' => 'special_view',
				),
			);
			MockStorage::$current_roles                    = array( 'member' );
			\ArtPulse\Dashboard\WidgetVisibilityManager::filter_visible_widgets();
			$ids = array_column( MockStorage::$removed, 0 );
			$this->assertContains( 'ap_donor_activity', $ids );
		}

		public function test_saved_rules_allowed_roles_only(): void {
			MockStorage::$options['artpulse_widget_roles'] = array(
				'custom_widget' => array(
					'allowed_roles' => array( 'organization' ),
				),
			);
			MockStorage::$current_roles                    = array( 'member' );
			\ArtPulse\Dashboard\WidgetVisibilityManager::filter_visible_widgets();
			$ids = array_column( MockStorage::$removed, 0 );
			$this->assertContains( 'custom_widget', $ids );
		}
	}
}
