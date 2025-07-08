<?php
namespace ArtPulse\Admin;

// WordPress stubs
function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
function get_option($key, $default = []) { return \ArtPulse\Admin\Tests\DashboardWidgetToolsRenderTest::$options[$key] ?? $default; }

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardWidgetToolsRenderTest extends TestCase
{
    public static array $options = [];

    protected function setUp(): void
    {
        self::$options = [];
    }

    public function test_role_layout_renders_in_order(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', function () { return 'alpha'; });
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', function () { return 'beta'; });

        self::$options['ap_dashboard_widget_config'] = [
            'subscriber' => [
                ['id' => 'beta'],
                ['id' => 'alpha']
            ],
        ];

        ob_start();
        DashboardWidgetTools::render_dashboard_widgets('subscriber');
        $html = ob_get_clean();

        $beta_pos = strpos($html, 'beta');
        $alpha_pos = strpos($html, 'alpha');

        $this->assertNotFalse($beta_pos);
        $this->assertNotFalse($alpha_pos);
        $this->assertLessThan($alpha_pos, $beta_pos);
        $this->assertEquals(2, substr_count($html, 'class="ap-widget"'));
    }
}
