<?php
namespace ArtPulse\Core\Tests {

	use PHPUnit\Framework\TestCase;
	use ArtPulse\Core\DashboardController;

	/**

	 * @group CORE

	 */

	class DashboardControllerTest extends TestCase {

		public function test_default_presets_include_new_templates(): void {
			$presets = DashboardController::get_default_presets();
			$this->assertArrayHasKey( 'new_member_intro', $presets );
			$this->assertSame( 'member', $presets['new_member_intro']['role'] );
			$this->assertNotEmpty( $presets['new_member_intro']['layout'] );

			$this->assertArrayHasKey( 'artist_tools', $presets );
			$this->assertSame( 'artist', $presets['artist_tools']['role'] );

			$this->assertArrayHasKey( 'org_admin_start', $presets );
			$this->assertSame( 'organization', $presets['org_admin_start']['role'] );
		}

		public function test_get_widgets_for_member_role(): void {
			$expected = array(
				'widget_news',
				'widget_membership',
				'upgrade',
				'account-tools',
				'recommended_for_you',
				'my_rsvps',
				'favorites',
				'local-events',
				'my-follows',
				'notifications',
				'messages',
				'dashboard_feedback',
				'cat_fact',
			);

			$this->assertSame( $expected, DashboardController::get_widgets_for_role( 'member' ) );
		}

		public function test_get_widgets_for_artist_role(): void {
			$expected = array(
				'artist_feed_publisher',
				'artist_audience_insights',
				'artist_spotlight',
				'artist_revenue_summary',
				'my_events',
				'messages',
				'notifications',
				'dashboard_feedback',
				'cat_fact',
			);

			$this->assertSame( $expected, DashboardController::get_widgets_for_role( 'artist' ) );
		}

		public function test_artist_widgets_match_manifest(): void {
			$manifest_file = __DIR__ . '/../../widget-manifest.json';
			$json          = file_get_contents( $manifest_file );
			$manifest      = json_decode( $json, true );

			$manifest_ids = array();
			foreach ( $manifest as $id => $data ) {
				$roles = $data['roles'] ?? array();
				if ( in_array( 'artist', $roles, true ) ) {
					$manifest_ids[] = $id;
				}
			}

			$widgets = DashboardController::get_widgets_for_role( 'artist' );

			$missing = array_diff( $manifest_ids, $widgets );
			$extra   = array_diff( $widgets, $manifest_ids );

			$this->assertSame( array(), $missing, 'Missing widget(s): ' . implode( ', ', $missing ) );
			$this->assertSame( array(), $extra, 'Unexpected widget(s): ' . implode( ', ', $extra ) );
		}

		public function test_get_widgets_for_organization_roles(): void {
			$expected = array(
				'org_event_overview',
				'artpulse_analytics_widget',
				'widget_org_ticket_insights',
				'widget_my_events',
				'org_team_roster',
				'widget_audience_crm',
				'org_broadcast_box',
				'org_approval_center',
				'widget_webhooks',
				'support-history',
			);

			$this->assertSame( $expected, DashboardController::get_widgets_for_role( 'organization' ) );
		}

               public function test_get_widgets_for_administrator_role_returns_empty(): void {
                       $this->assertSame( array(), DashboardController::get_widgets_for_role( 'administrator' ) );
               }

               public function test_get_widgets_for_unknown_role_returns_empty(): void {
                       $this->assertSame( array(), DashboardController::get_widgets_for_role( 'unknown' ) );
               }
       }
}
