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
    function get_current_screen() { return (object)['id' => \ArtPulse\Core\Tests\OrgDashboardWidgetRoleTest::$screen]; }
    function get_current_user_id() { return 1; }
    function get_user_meta($uid, $key, $single = false) { return \ArtPulse\Core\Tests\OrgDashboardWidgetRoleTest::$meta[$key] ?? ''; }
    function update_user_meta($uid, $key, $value) { \ArtPulse\Core\Tests\OrgDashboardWidgetRoleTest::$meta[$key] = $value; }
    function delete_user_meta($uid, $key) { unset(\ArtPulse\Core\Tests\OrgDashboardWidgetRoleTest::$meta[$key]); }
    function add_query_arg(...$args) { return '#'; }
    function remove_query_arg($k) { return ''; }
    function wp_safe_redirect($url) {}
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
    public static array $meta = [];
    public static string $screen = 'dashboard';

    protected function setUp(): void {
        self::$current_roles = [];
        self::$removed = [];
        self::$notice = [];
        self::$meta = [];
        self::$screen = 'dashboard';
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
        $this->assertSame('Analytics are available to organization managers only.', self::$meta['ap_org_editor_notice_pending']);
    }

    public function test_org_viewer_has_no_analytics_capability(): void {
        self::$current_roles = ['org_viewer'];
        $this->assertFalse(\current_user_can('view_analytics'));
        \ap_dashboard_widget_visibility_filter();
        $this->assertSame('artpulse_analytics_widget', self::$removed[0][0]);
        $this->assertEmpty(self::$notice);
    }

    public function test_no_widgets_outputs_fallback_message(): void {
        global $wp_meta_boxes;
        self::$current_roles = ['org_viewer'];
        $wp_meta_boxes = ['dashboard' => []];
        ob_start();
        \ap_dashboard_empty_state_notice();
        $html = ob_get_clean();
        $this->assertStringContainsString('No dashboard content available', $html);
    }

    public function test_filter_allows_custom_visibility_rule(): void {
        tests_add_filter('ap_dashboard_widget_visibility_rules', function ($rules) {
            $rules['custom_widget'] = [
                'exclude_roles' => ['org_viewer'],
            ];
            return $rules;
        });
        self::$current_roles = ['org_viewer'];
        \ap_dashboard_widget_visibility_filter();
        $ids = array_column(self::$removed, 0);
        $this->assertContains('custom_widget', $ids);
    }
}
}
