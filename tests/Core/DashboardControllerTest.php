<?php
namespace {
    if (!defined('ARTPULSE_PLUGIN_FILE')) {
        define('ARTPULSE_PLUGIN_FILE', __DIR__ . '/../../artpulse.php');
    }
    if (!function_exists('plugin_dir_path')) {
        function plugin_dir_path($file) { return dirname($file) . '/'; }
    }
}

namespace ArtPulse\Core\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;

class DashboardControllerTest extends TestCase
{
    public function test_default_presets_include_new_templates(): void
    {
        $presets = DashboardController::get_default_presets();
        $this->assertArrayHasKey('new_member_intro', $presets);
        $this->assertSame('member', $presets['new_member_intro']['role']);
        $this->assertNotEmpty($presets['new_member_intro']['layout']);

        $this->assertArrayHasKey('artist_tools', $presets);
        $this->assertSame('artist', $presets['artist_tools']['role']);

        $this->assertArrayHasKey('org_admin_start', $presets);
        $this->assertSame('organization', $presets['org_admin_start']['role']);
    }

    public function test_get_widgets_for_member_role(): void
    {
        $expected = [
            'widget_news',
            'widget_favorites',
            'widget_events',
            'notifications',
            'my_rsvps',
            'recommended_for_you',
        ];

        $this->assertSame($expected, DashboardController::get_widgets_for_role('member'));
    }

    public function test_get_widgets_for_artist_role(): void
    {
        $expected = [
            'widget_spotlights',
            'artist_inbox_preview',
            'artist_revenue_summary',
            'widget_followed_artists',
            'notifications',
        ];

        $this->assertSame($expected, DashboardController::get_widgets_for_role('artist'));
    }

    public function test_get_widgets_for_organization_roles(): void
    {
        $expected = [
            'site_stats',
            'webhooks',
            'rsvp_stats',
            'artpulse_analytics_widget',
            'ap_donor_activity',
            'notifications',
        ];

        $this->assertSame($expected, DashboardController::get_widgets_for_role('organization'));
    }

    public function test_get_widgets_for_unknown_role_returns_empty(): void
    {
        $this->assertSame([], DashboardController::get_widgets_for_role('administrator'));
        $this->assertSame([], DashboardController::get_widgets_for_role('unknown'));
    }
}
}
