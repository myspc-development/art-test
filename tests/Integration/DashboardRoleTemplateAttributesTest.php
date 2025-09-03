<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardPresets;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\WidgetRegistry;

/**

 * @group INTEGRATION

 */

class DashboardRoleTemplateAttributesTest extends \WP_UnitTestCase {
        private const SLUGS = array(
                'widget_news',
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
	public function test_attributes_match_role( string $role ): void {
		set_query_var( 'ap_role', $role );
		$template = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/simple-dashboard.php';
		ob_start();
		include $template;
		$html = ob_get_clean();
		$this->assertStringContainsString( 'data-role="' . $role . '"', $html );
		$this->assertStringContainsString( 'id="ap-panel-' . $role . '"', $html );
		$this->assertStringContainsString( 'aria-labelledby="ap-tab-' . $role . '"', $html );

		foreach ( DashboardPresets::forRole( $role ) as $slug ) {
			$this->assertStringContainsString( 'data-slug="' . $slug . '"', $html );
		}
	}
}
