<?php
namespace {
    if (!defined('ARTPULSE_PLUGIN_FILE')) {
        define('ARTPULSE_PLUGIN_FILE', __DIR__ . '/../../artpulse.php');
    }
    if (!defined('MINUTE_IN_SECONDS')) {
        define('MINUTE_IN_SECONDS', 60);
    }
    function wp_get_current_user() {
        return (object)['roles' => \ArtPulse\Core\Tests\OrgDashboardWidgetRoleTest::$current_roles];
    }
    function current_user_can($cap) {
        if ($cap === 'view_analytics') {
            return in_array('org_editor', \ArtPulse\Core\Tests\OrgDashboardWidgetRoleTest::$current_roles, true)
                || in_array('org_manager', \ArtPulse\Core\Tests\OrgDashboardWidgetRoleTest::$current_roles, true);
        }
        return true;
    }
    function remove_meta_box($id, $screen, $context) {
        \ArtPulse\Core\Tests\OrgDashboardWidgetRoleTest::$removed[] = [$id, $screen, $context];
    }
    function wp_kses_post($msg) { return $msg; }
    function set_transient($k, $v, $e) { \ArtPulse\Core\Tests\OrgDashboardWidgetRoleTest::$notice = $v; }
    function get_transient($k) { return null; }
    function delete_transient($k) {}
    function esc_attr($t) { return $t; }
    function __($t, $d=null) { return $t; }
}

namespace ArtPulse\Core\Tests {
use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;

require_once __DIR__ . '/../../src/Core/DashboardController.php';
require_once __DIR__ . '/../../includes/roles.php';

class OrgDashboardWidgetRoleTest extends TestCase {
    public static array $current_roles = [];
    public static array $removed = [];
    public static array $notice = [];

    protected function setUp(): void {
        self::$current_roles = [];
        self::$removed = [];
        self::$notice = [];
    }

    public function test_org_roles_inherit_widgets(): void {
        $org   = DashboardController::get_widgets_for_role('organization');
        $editor = DashboardController::get_widgets_for_role('org_editor');
        $viewer = DashboardController::get_widgets_for_role('org_viewer');
        $admin  = DashboardController::get_widgets_for_role('administrator');

        $this->assertSame($org, $editor);
        $this->assertSame($org, $viewer);
        $this->assertSame([], $admin);
    }

    public function test_analytics_widget_removed_for_editor(): void {
        self::$current_roles = ['org_editor'];
        \ap_dashboard_widget_visibility_filter();
        $this->assertSame('artpulse_analytics_widget', self::$removed[0][0]);
        $this->assertStringContainsString('Analytics are available', self::$notice[0]['message']);
    }

    public function test_org_viewer_has_no_analytics_capability(): void {
        self::$current_roles = ['org_viewer'];
        $this->assertFalse(\current_user_can('view_analytics'));
        \ap_dashboard_widget_visibility_filter();
        $this->assertSame('artpulse_analytics_widget', self::$removed[0][0]);
        $this->assertEmpty(self::$notice);
    }
}
}
