<?php
require_once __DIR__ . '/../TestStubs.php';

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Core\DashboardPresets;

/** @coversNothing */
/**
 * @group PHPUNIT
 */
final class OrgDashboardRenderTest extends TestCase {
	public static function renderSection(): string {
			return '';
	}
	protected function setUp(): void {
			WidgetRegistry::register( 'widget_audience_crm', array( self::class, 'renderSection' ) );
			WidgetRegistry::register( 'widget_org_ticket_insights', array( self::class, 'renderSection' ) );
			WidgetRegistry::register( 'widget_webhooks', array( self::class, 'renderSection' ) );
			WidgetRegistry::register( 'widget_my_events', array( self::class, 'renderSection' ) );
			WidgetRegistry::register( 'widget_site_stats', array( self::class, 'renderSection' ) );
	}

	public function test_org_preset_widgets_are_registered(): void {
			$slugs = DashboardPresets::forRole( 'organization' );
		foreach ( $slugs as $slug ) {
				$this->assertTrue(
					WidgetRegistry::exists( $slug ),
					"Organization preset references unknown slug {$slug}"
				);
		}
	}
}
