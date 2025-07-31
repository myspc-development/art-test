<?php
namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardWidgetManager;

// Stub functions
function artpulse_dashicon($icon, $args = []) { return '<span></span>'; }
function esc_attr($str) { return $str; }
function update_option($k, $v) { PreviewStyleTest::$options[$k] = $v; }
function get_option($k, $d = []) { return PreviewStyleTest::$options[$k] ?? $d; }
function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }

class PreviewStyleTest extends TestCase
{
    public static array $options = [];
    protected function setUp(): void
    {
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
    }

    public function test_preview_injects_style_tag(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', '__return_null');
        UserLayoutManager::save_role_layout('subscriber', [['id' => 'alpha']]);
        UserLayoutManager::save_role_style('subscriber', ['background_color' => '#000']);

        ob_start();
        DashboardWidgetTools::render_role_dashboard_preview('subscriber');
        $html = ob_get_clean();

        $this->assertStringContainsString('<style id="ap-preview-style">', $html);
        $this->assertStringContainsString('background:#000', $html);
    }
}
