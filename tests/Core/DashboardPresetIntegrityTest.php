<?php
namespace {
	if ( ! function_exists( 'apply_filters' ) ) {
		function apply_filters( $tag, $value, ...$args ) {
			return $value; }
	}
}

namespace ArtPulse\Core\Tests {

	use PHPUnit\Framework\TestCase;
	use ArtPulse\Core\WidgetRegistry;
	use ArtPulse\Core\DashboardPresets;

	/**

	 * @group CORE

	 */

	class DashboardPresetIntegrityTest extends TestCase {

		protected function setUp(): void {
			if ( ! defined( 'WP_DEBUG' ) ) {
				define( 'WP_DEBUG', true );
			}
			$slugs = array(
				'widget_membership',
				'widget_my_follows',
				'widget_local_events',
				'widget_recommended_for_you',
				'widget_my_events',
				'widget_account_tools',
				'widget_site_stats',
				'widget_artist_revenue_summary',
				'widget_artist_artwork_manager',
				'widget_artist_audience_insights',
				'widget_artist_feed_publisher',
				'widget_audience_crm',
				'widget_org_ticket_insights',
				'widget_webhooks',
			);
			foreach ( $slugs as $slug ) {
				WidgetRegistry::register( $slug, static fn() => '<div>' . $slug . '</div>' );
			}
		}

		protected function tearDown(): void {
			$ref  = new \ReflectionClass( WidgetRegistry::class );
			$prop = $ref->getProperty( 'widgets' );
			$prop->setAccessible( true );
			$prop->setValue( null, array() );
			$prop = $ref->getProperty( 'logged_missing' );
			$prop->setAccessible( true );
			$prop->setValue( null, array() );
		}

		public function test_presets_reference_registered_widgets(): void {
			foreach ( array( 'member', 'artist', 'organization' ) as $role ) {
				$slugs = DashboardPresets::forRole( $role );
				foreach ( $slugs as $slug ) {
					$this->assertTrue(
						WidgetRegistry::exists( $slug ),
						"Preset for {$role} includes unknown slug {$slug}"
					);
				}
			}
		}
	}

}
