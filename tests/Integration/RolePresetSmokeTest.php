<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardPresets;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\WidgetRegistry;

/**

 * @group INTEGRATION

 */

class RolePresetSmokeTest extends \WP_UnitTestCase {
	private const SLUGS = array(
		'widget_membership',
		'widget_account_tools',
		'widget_my_follows',
		'widget_recommended_for_you',
		'widget_local_events',
		'widget_my_events',
		'widget_site_stats',
		'widget_artist_revenue_summary',
		'widget_artist_artwork_manager',
		'widget_artist_audience_insights',
		'widget_artist_feed_publisher',
		'widget_audience_crm',
		'widget_org_ticket_insights',
		'widget_webhooks',
	);

	public function set_up() {
		parent::set_up();
		$this->resetRegistries();
		foreach ( self::SLUGS as $slug ) {
			WidgetRegistry::register( $slug, static fn() => '<section data-slug="' . $slug . '"></section>' );
			DashboardWidgetRegistry::register(
				$slug,
				array(
					'title'           => $slug,
					'render_callback' => static function () use ( $slug ) {
						echo '<section data-slug="' . $slug . '"></section>'; },
					'roles'           => array(),
				)
			);
		}
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	private function resetRegistries(): void {
		$ref = new \ReflectionClass( DashboardWidgetRegistry::class );
		foreach ( array( 'widgets', 'builder_widgets' ) as $prop ) {
			$p = $ref->getProperty( $prop );
			$p->setAccessible( true );
			$p->setValue( null, array() );
		}
		$ref2 = new \ReflectionClass( WidgetRegistry::class );
		$p2   = $ref2->getProperty( 'widgets' );
		$p2->setAccessible( true );
		$p2->setValue( null, array() );
	}

	public static function roleProvider(): array {
		return array( array( 'member' ), array( 'artist' ), array( 'organization' ) );
	}

	/**
	 * @dataProvider roleProvider
	 */
	public function test_presets_render_at_least_one_widget( string $role ): void {
		$slugs = DashboardPresets::forRole( $role );
		$this->assertNotEmpty( $slugs );
		set_query_var( 'ap_role', $role );
		$html = DashboardWidgetRegistry::render( $slugs[0], array( 'preview_role' => $role ) );
		$this->assertStringContainsString( '<section', $html );
	}
}
