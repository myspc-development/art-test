<?php
namespace ArtPulse\Core {
    interface DashboardWidgetInterface {}

    class DashboardWidgetRegistry {
        public static function register(...$args): void {}
        public static function get_widget($id) { return null; }
        public static function get_widget_callback($id) { return null; }
    }

    class DashboardController {
        public static function get_role($user_id): string { return 'member'; }
    }

    class ActivityLogger {
        public static function get_logs($org_id, $user_id, int $limit = 10): array {
            return [(object) ['description' => 'log', 'logged_at' => date('Y-m-d H:i:s')]];
        }
    }
}

namespace {
    require_once __DIR__ . '/../TestStubs.php';
    require_once __DIR__ . '/../../widgets/member/ActivityFeedWidget.php';

    if (!defined('ABSPATH')) {
        define('ABSPATH', __DIR__ . '/');
    }

    if (!class_exists('WP_UnitTestCase')) {
        class WP_UnitTestCase extends \PHPUnit\Framework\TestCase {}
    }

    if (!function_exists('__')) {
        function __($text, $domain = null) { return $text; }
    }

    if (!function_exists('esc_html__')) {
        function esc_html__($text, $domain = null) { return $text; }
    }

    if (!function_exists('esc_url_raw')) {
        function esc_url_raw($url) { return $url; }
    }

    if (!function_exists('rest_url')) {
        function rest_url($path = '') { return $path; }
    }

    if (!function_exists('wp_create_nonce')) {
        function wp_create_nonce($action) { return 'nonce'; }
    }

    if (!function_exists('is_user_logged_in')) {
        function is_user_logged_in() { return true; }
    }

    if (!function_exists('user_can')) {
        function user_can($uid, $cap) { return true; }
    }

    if (!function_exists('date_i18n')) {
        function date_i18n($format, $timestamp) { return date($format, $timestamp); }
    }

    if (!function_exists('wp_get_current_user')) {
        function wp_get_current_user() {
            return (object) ['display_name' => 'Test User', 'user_login' => 'test'];
        }
    }

    if (!function_exists('wp_kses_post')) {
        function wp_kses_post($content) { return $content; }
    }

    if (class_exists('ArtPulse\\Widgets\\Member\\ActivityFeedWidget') && !class_exists('ActivityFeedWidget')) {
        class_alias('ArtPulse\\Widgets\\Member\\ActivityFeedWidget', 'ActivityFeedWidget');
    }
}

