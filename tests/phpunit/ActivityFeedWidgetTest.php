<?php
namespace ArtPulse\Core {
    if (!class_exists('ArtPulse\\Core\\DashboardWidgetRegistry', false)) {
        class DashboardWidgetRegistry { public static function register(...$args): void {} }
    }
    if (!class_exists('ArtPulse\\Core\\DashboardController', false)) {
        class DashboardController {
            public static string $role = 'member';
            public static function get_role($user_id): string { return self::$role; }
        }
    }
    if (!class_exists('ArtPulse\\Core\\ActivityLogger', false)) {
        class ActivityLogger {
            public static array $logs = [];
            public static function get_logs($org_id, $user_id, int $limit = 10): array {
                return array_slice(self::$logs, 0, $limit);
            }
        }
    }
}

namespace {
    require_once __DIR__ . '/../TestStubs.php';

    if (!defined('ABSPATH')) {
        define('ABSPATH', __DIR__ . '/');
    }

    if (!function_exists('esc_html__')) {
        function esc_html__($text, $domain = null) { return $text; }
    }
    if (!function_exists('esc_html_e')) {
        function esc_html_e($text, $domain = null) { echo $text; }
    }
    if (!function_exists('sanitize_title')) {
        function sanitize_title($title) { return preg_replace('/[^a-z0-9_\-]+/i', '-', strtolower($title)); }
    }
    if (!function_exists('date_i18n')) {
        function date_i18n($format, $timestamp) { return date($format, $timestamp); }
    }

    use ArtPulse\Widgets\Member\ActivityFeedWidget;
    use ArtPulse\Core\ActivityLogger;
    use ArtPulse\Core\DashboardController;
    use ArtPulse\Tests\Stubs\MockStorage;
    use PHPUnit\Framework\TestCase;

    class ActivityFeedWidgetTest extends TestCase {
        protected function setUp(): void {
            ActivityLogger::$logs = [];
            DashboardController::$role = 'member';
            MockStorage::$options['date_format'] = 'Y-m-d';
        }

        public function test_render_displays_no_activity_message(): void {
            $html = ActivityFeedWidget::render(1);
            $this->assertStringContainsString('No recent activity.', $html);
        }

        public function test_render_lists_activity_logs(): void {
            ActivityLogger::$logs = [
                (object) ['description' => 'Did something', 'logged_at' => '2024-01-01 10:00:00'],
            ];
            $html = ActivityFeedWidget::render(1);
            $this->assertStringContainsString('<li>Did something <em>2024-01-01 10:00</em></li>', $html);
        }

        public function test_render_shows_error_for_unauthorized_user(): void {
            DashboardController::$role = 'guest';
            $html = ActivityFeedWidget::render(1);
            $this->assertStringContainsString('You do not have access.', $html);
        }
    }
}
