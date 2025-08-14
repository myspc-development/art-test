<?php
namespace {
    global $ap_test_roles;
    $ap_test_roles = [];
    if (!function_exists('add_role')) {
        function add_role($role, $display_name = '', $caps = []): void {
            global $ap_test_roles; $ap_test_roles[$role] = $caps;
        }
    }
    if (!function_exists('remove_role')) {
        function remove_role($role): void {
            global $ap_test_roles; unset($ap_test_roles[$role]);
        }
    }
    if (!function_exists('get_role')) {
        function get_role($role) {
            global $ap_test_roles; $caps = $ap_test_roles[$role] ?? [];
            return new class($caps) {
                private array $caps; public function __construct(array $caps){$this->caps=$caps;}
                public function has_cap($cap){return !empty($this->caps[$cap]);}
            };
        }
    }
    if (!function_exists('sanitize_key')) {
        function sanitize_key($key) {
            $key = strtolower((string)$key);
            return preg_replace('/[^a-z0-9_]/', '', $key);
        }
    }
    if (!function_exists('__return_null')) {
        function __return_null() { return null; }
    }
    if (!function_exists('plugin_dir_path')) {
        function plugin_dir_path($file) {
            return rtrim(dirname($file), '/') . '/';
        }
    }
    if (!function_exists('get_option')) {
        function get_option($name, $default = false) {
            return $default;
        }
    }
    if (!function_exists('get_current_user_id')) {
        function get_current_user_id() { return 0; }
    }
    if (!function_exists('user_can')) {
        function user_can($user_id, $cap) { return false; }
    }
    if (!function_exists('current_user_can')) {
        function current_user_can($cap) { return false; }
    }
    if (!function_exists('get_userdata')) {
        function get_userdata($user_id) { return (object)['roles' => ['member']]; }
    }
    if (!function_exists('apply_filters')) {
        function apply_filters($tag, $value) { return $value; }
    }
    if (!function_exists('do_action')) {
        function do_action($tag, ...$args) {}
    }
}

namespace ArtPulse\Core\Tests {

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardPresetIntegrityTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            define('ARTPULSE_PLUGIN_FILE', __DIR__ . '/../../artpulse.php');
        }

        // Ensure roles exist for capability checks.
        add_role('member', 'Member');
        add_role('artist', 'Artist');
        add_role('organization', 'Organization');

        // Register member widgets
        DashboardWidgetRegistry::register_widget('widget_news', [
            'label' => 'News',
            'roles' => ['member'],
            'callback' => '__return_null',
        ]);
        DashboardWidgetRegistry::register_widget('widget_favorites', [
            'label' => 'Favorites',
            'roles' => ['member', 'artist'],
            'callback' => '__return_null',
        ]);
        DashboardWidgetRegistry::register_widget('widget_events', [
            'label' => 'Events',
            'roles' => ['member'],
            'callback' => '__return_null',
        ]);
        DashboardWidgetRegistry::register_widget('instagram_widget', [
            'label' => 'Instagram',
            'roles' => ['member'],
            'callback' => '__return_null',
        ]);

        // Register artist widgets
        DashboardWidgetRegistry::register_widget('activity_feed', [
            'label' => 'Activity',
            'roles' => ['artist'],
            'callback' => '__return_null',
        ]);
        DashboardWidgetRegistry::register_widget('artist_inbox_preview', [
            'label' => 'Inbox Preview',
            'roles' => ['artist'],
            'callback' => '__return_null',
        ]);
        DashboardWidgetRegistry::register_widget('artist_revenue_summary', [
            'label' => 'Revenue',
            'roles' => ['artist'],
            'capability' => 'manage_options',
            'callback' => '__return_null',
        ]);
        DashboardWidgetRegistry::register_widget('artist_spotlight', [
            'label' => 'Spotlight',
            'roles' => ['artist'],
            'callback' => '__return_null',
        ]);
        DashboardWidgetRegistry::register_widget('qa_checklist', [
            'label' => 'QA Checklist',
            'roles' => ['member'],
            'callback' => '__return_null',
        ]);

        // Register organization widgets
        DashboardWidgetRegistry::register_widget('webhooks', [
            'label' => 'Webhooks',
            'roles' => ['organization'],
            'callback' => '__return_null',
        ]);
        DashboardWidgetRegistry::register_widget('rsvp_stats', [
            'label' => 'RSVP Stats',
            'roles' => ['organization'],
            'callback' => '__return_null',
        ]);
        DashboardWidgetRegistry::register_widget('site_stats', [
            'label' => 'Site Stats',
            'roles' => ['organization'],
            'capability' => 'manage_options',
            'callback' => '__return_null',
        ]);
        // lead_capture intentionally not registered
    }

    protected function tearDown(): void {
        // Clean up registry and roles
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);

        remove_role('member');
        remove_role('artist');
        remove_role('organization');
    }

    public function test_presets_filtered_by_role(): void {
        $presets = DashboardController::get_default_presets();
        $member_ids = array_column($presets['member_default']['layout'], 'id');
        $artist_ids = array_column($presets['artist_default']['layout'], 'id');
        $org_ids    = array_column($presets['org_admin_start']['layout'], 'id');

        $this->assertSame(
            ['widget_news', 'widget_favorites', 'widget_events', 'instagram_widget'],
            $member_ids
        );
        $this->assertSame(
            ['activity_feed', 'artist_inbox_preview', 'artist_spotlight', 'widget_favorites'],
            $artist_ids
        );
        $this->assertSame(
            ['webhooks', 'rsvp_stats'],
            $org_ids
        );
    }

    public function test_presets_reference_registered_widgets(): void {
        $presets = DashboardController::get_default_presets();
        foreach ($presets as $preset) {
            foreach ($preset['layout'] as $widget) {
                $this->assertTrue(
                    DashboardWidgetRegistry::exists($widget['id']),
                    'Widget '.$widget['id'].' not registered'
                );
            }
        }
    }
}

}
