<?php
namespace {
    require_once __DIR__ . '/../TestStubs.php';
    if (!function_exists('sanitize_title')) {
        function sanitize_title($title) { return preg_replace('/[^a-z0-9_\-]+/i', '-', strtolower($title)); }
    }
    if (!function_exists('wp_script_is')) {
        function wp_script_is($handle, $list = 'enqueued') { return false; }
    }
}

namespace ArtPulse\Tests {
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
            $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
            $prop = $ref->getProperty('widgets');
            $prop->setAccessible(true);
            $prop->setValue(null, []);

            LeadCaptureWidget::register();
            RsvpStatsWidget::register();
            WebhooksWidget::register();
            MyEventsWidget::register();
            SiteStatsWidget::register();
        }

        public function widgetIds(): array {
            return [
                ['lead_capture'],
                ['rsvp_stats'],
                ['webhooks'],
                ['my-events'],
                ['site_stats'],
            ];
        }

        /**
         * @dataProvider widgetIds
         */
        public function test_widgets_registered(string $id): void {
            $this->assertTrue(DashboardWidgetRegistry::exists($id));
        }

        /**
         * @dataProvider widgetIds
         */
        public function test_widgets_render(string $id): void {
            $def = DashboardWidgetRegistry::getById($id);
            $this->assertIsArray($def);
            $callback = $def['callback'];
            $html = call_user_func($callback, 1);
            $this->assertIsString($html);
            $this->assertStringContainsString('<section', $html);
        }
    }
}
