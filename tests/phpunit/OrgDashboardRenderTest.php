<?php
require_once __DIR__ . '/../TestStubs.php';

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;

final class OrgDashboardRenderTest extends TestCase {
        protected function setUp(): void {
                WidgetRegistry::register( 'widget_audience_crm', [self::class, 'renderSection'] );
                WidgetRegistry::register( 'widget_org_ticket_insights', [self::class, 'renderSection'] );
                WidgetRegistry::register( 'widget_webhooks', [self::class, 'renderSection'] );
        }

        public function test_org_core_widgets_render_sections(): void {
		foreach ( array(
			'widget_audience_crm',
			'widget_org_ticket_insights',
			'widget_webhooks',
		) as $slug ) {
			$html = WidgetRegistry::render( $slug, array( 'user_id' => 1 ) );
			$this->assertStringContainsString( '<section', $html, $slug . ' should render a <section>' );
        }

        public static function renderSection(): string { return '<section></section>'; }
}
}
