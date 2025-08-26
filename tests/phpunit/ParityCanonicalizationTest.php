<?php
namespace ArtPulse\Audit\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Audit\Parity;
use ArtPulse\Audit\AuditBus;
use ArtPulse\Core\DashboardRenderer;

require_once __DIR__ . '/../TestStubs.php';

class ParityCanonicalizationTest extends \WP_UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        foreach (['widgets','builder_widgets','id_map','issues','logged_duplicates','aliases'] as $prop) {
            if ($ref->hasProperty($prop)) {
                $p = $ref->getProperty($prop);
                $p->setAccessible(true);
                $p->setValue(null, []);
            }
        }
        AuditBus::reset();
        update_option('artpulse_widget_roles', []);
        update_option('artpulse_hidden_widgets', []);
        update_option('artpulse_dashboard_layouts', []);
    }

    public function test_member_parity_clean(): void
    {
        DashboardWidgetRegistry::register('widget_favorites', 'Fav', '', '', '__return_null', ['roles'=>['member']]);
        update_option('artpulse_widget_roles', ['member'=>['favorites']]);
        update_option('artpulse_dashboard_layouts', ['member'=>['widget_widget_favorites']]);
        DashboardRenderer::render('widget_widget_favorites', 0);
        $report = Parity::compare_with_actual('member');
        $this->assertSame([], $report['missing']);
        $this->assertSame([], $report['extra']);
    }
}
