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
}
}
