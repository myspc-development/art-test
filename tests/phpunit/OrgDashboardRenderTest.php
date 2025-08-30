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


}
