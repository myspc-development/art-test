<?php

namespace ArtPulse\Widgets\Organization;

if ( ! function_exists( __NAMESPACE__ . '\\admin_url' ) ) {
        function admin_url( $path = '', $scheme = 'admin' ) {
                return '#';
        }
}

namespace ArtPulse\Tests;

require_once __DIR__ . '/../TestStubs.php';
        use PHPUnit\Framework\TestCase;
        use ArtPulse\Core\DashboardWidgetRegistry;
        use ArtPulse\Widgets\Organization\LeadCaptureWidget;
        use ArtPulse\Widgets\Organization\RsvpStatsWidget;
        use ArtPulse\Widgets\Organization\WebhooksWidget;
        use ArtPulse\Widgets\Organization\MyEventsWidget;
        use ArtPulse\Widgets\Common\SiteStatsWidget;

class OrgWidgetsTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();
                if ( ! defined( 'ABSPATH' ) ) {
                        define( 'ABSPATH', __DIR__ );
                }
                $ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
                $prop = $ref->getProperty( 'widgets' );
                $prop->setAccessible( true );
                $prop->setValue( null, array() );

		LeadCaptureWidget::register();
		RsvpStatsWidget::register();
		WebhooksWidget::register();
		MyEventsWidget::register();
		SiteStatsWidget::register();
	}

	public function widgetIds(): array {
		return array(
			array( 'widget_audience_crm' ),
			array( 'widget_org_ticket_insights' ),
			array( 'widget_webhooks' ),
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
}
