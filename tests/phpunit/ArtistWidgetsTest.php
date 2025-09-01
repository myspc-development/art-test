<?php
namespace ArtPulse\Tests;

require_once __DIR__ . '/../TestStubs.php';
	use PHPUnit\Framework\TestCase;
	use ArtPulse\Core\DashboardWidgetRegistry;
	use ArtPulse\Widgets\Artist\ArtistRevenueSummaryWidget;
	use ArtPulse\Widgets\Artist\ArtistArtworkManagerWidget;
	use ArtPulse\Widgets\Artist\ArtistAudienceInsightsWidget;
	use ArtPulse\Widgets\Artist\ArtistFeedPublisherWidget;
	use ArtPulse\Widgets\Artist\MyEventsWidget;
	use ArtPulse\Widgets\Common\SiteStatsWidget;

/**

 * @group phpunit

 */

class ArtistWidgetsTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );

		ArtistRevenueSummaryWidget::register();
		ArtistArtworkManagerWidget::register();
		ArtistAudienceInsightsWidget::register();
		ArtistFeedPublisherWidget::register();
		MyEventsWidget::register();
		SiteStatsWidget::register();
	}

	public function widgetIds(): array {
		return array(
			array( 'widget_artist_revenue_summary' ),
			array( 'widget_artist_artwork_manager' ),
			array( 'widget_artist_audience_insights' ),
			array( 'widget_artist_feed_publisher' ),
			array( 'widget_my_events' ),
			array( 'widget_site_stats' ),
		);
	}

	/**
	 * @dataProvider widgetIds
	 */
	public function test_widgets_registered( string $id ): void {
		$this->assertTrue( DashboardWidgetRegistry::exists( $id ) );
	}

	/**
	 * @dataProvider widgetIds
	 */
	public function test_widgets_render( string $id ): void {
		$def = DashboardWidgetRegistry::getById( $id );
		$this->assertIsArray( $def );
		$callback = $def['callback'];
		$html     = call_user_func( $callback, 1 );
		$this->assertIsString( $html );
		$this->assertStringContainsString( '<section', $html );
	}

	public function test_my_events_alias_resolves(): void {
		$this->assertTrue( DashboardWidgetRegistry::exists( 'my-events' ) );
		$this->assertTrue( DashboardWidgetRegistry::exists( 'myevents' ) );
		$this->assertSame(
			DashboardWidgetRegistry::get( 'widget_my_events' ),
			DashboardWidgetRegistry::get( 'my-events' )
		);
	}
}
