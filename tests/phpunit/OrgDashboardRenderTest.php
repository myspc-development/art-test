<?php
require_once __DIR__ . '/../TestStubs.php';

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\WidgetRegistry;

final class OrgDashboardRenderTest extends TestCase {
    protected function setUp(): void {
        WidgetRegistry::register('widget_audience_crm', static fn() => '<section></section>');
        WidgetRegistry::register('widget_org_ticket_insights', static fn() => '<section></section>');
        WidgetRegistry::register('widget_webhooks', static fn() => '<section></section>');
    }

    public function test_org_core_widgets_render_sections(): void {
        foreach ([
            'widget_audience_crm',
            'widget_org_ticket_insights',
            'widget_webhooks',
        ] as $slug) {
            $html = WidgetRegistry::render($slug, ['user_id' => 1]);
            $this->assertStringContainsString('<section', $html, $slug . ' should render a <section>');
        }
    }
}
