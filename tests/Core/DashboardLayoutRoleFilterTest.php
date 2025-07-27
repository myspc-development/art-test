<?php
namespace {
    if (!defined('ARTPULSE_PLUGIN_FILE')) {
        define('ARTPULSE_PLUGIN_FILE', __DIR__ . '/../../artpulse.php');
    }
    function get_user_meta($uid, $key, $single = false) {
        return \ArtPulse\Core\Tests\DashboardLayoutRoleFilterTest::\$meta[$uid][$key] ?? '';
    }
    function get_option($key, $default = []) {
        return \ArtPulse\Core\Tests\DashboardLayoutRoleFilterTest::\$options[$key] ?? $default;
    }
    function get_userdata($uid) {
        return \ArtPulse\Core\Tests\DashboardLayoutRoleFilterTest::\$users[$uid] ?? null;
    }
    function current_user_can($cap) { return false; }
}

namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardLayoutRoleFilterTest extends TestCase
{
    public static array $meta = [];
    public static array $options = [];
    public static array $users = [];

    protected function setUp(): void
    {
        self::$meta = [];
        self::$options = [];
        self::$users = [];

        // Reset registry widgets
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);

        // Reset role widgets map
        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue([]);
    }

    public function test_user_meta_layout_is_filtered_by_role(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', '__return_null', ['roles' => ['member']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', '__return_null', ['roles' => ['artist']]);

        self::$users[1] = (object)['roles' => ['member']];
        self::$meta[1]['ap_dashboard_layout'] = [
            ['id' => 'alpha'],
            ['id' => 'beta'],
            ['id' => 'unknown'],
        ];

        $layout = DashboardController::get_user_dashboard_layout(1);
        $this->assertSame([['id' => 'alpha']], $layout);
    }

    public function test_option_layout_is_filtered_by_role(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', '__return_null', ['roles' => ['member']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', '__return_null', ['roles' => ['artist']]);

        self::$users[2] = (object)['roles' => ['member']];
        self::$options['artpulse_dashboard_layouts'] = [
            'member' => [
                ['id' => 'alpha'],
                ['id' => 'beta'],
            ],
        ];

        $layout = DashboardController::get_user_dashboard_layout(2);
        $this->assertSame([['id' => 'alpha']], $layout);
    }

    public function test_default_layout_is_filtered_by_role_widgets(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', '__return_null', ['roles' => ['member']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', '__return_null', ['roles' => ['artist']]);

        $ref = new \ReflectionClass(DashboardController::class);
        $prop = $ref->getProperty('role_widgets');
        $prop->setAccessible(true);
        $prop->setValue([
            'member' => ['alpha', 'beta'],
        ]);

        self::$users[3] = (object)['roles' => ['member']];

        $layout = DashboardController::get_user_dashboard_layout(3);
        $this->assertSame([['id' => 'alpha']], $layout);
    }
}
