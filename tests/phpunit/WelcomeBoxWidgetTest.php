<?php
namespace ArtPulse\Core {
    if (!class_exists('ArtPulse\\Core\\DashboardWidgetRegistry', false)) {
        class DashboardWidgetRegistry { public static function register(...$args): void {} }
    }
}

namespace ArtPulse\Widgets\Member {
    function wp_get_current_user() {
        return \WelcomeBoxWidgetTest::$user;
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
    if (!function_exists('sanitize_title')) {
        function sanitize_title($title) { return preg_replace('/[^a-z0-9_\-]+/i', '-', strtolower($title)); }
    }

    use ArtPulse\Widgets\Member\WelcomeBoxWidget;
    use PHPUnit\Framework\TestCase;

    class WelcomeBoxWidgetTest extends TestCase {
        public static $user;

        protected function setUp(): void {
            self::$user = (object) ['display_name' => 'Tester', 'user_login' => 'tester'];
        }

        public function test_render_includes_display_name(): void {
            $html = WelcomeBoxWidget::render();
            $this->assertStringContainsString('Welcome back, Tester!', $html);
        }

        public function test_render_uses_login_when_display_name_missing(): void {
            self::$user->display_name = '';
            self::$user->user_login = 'loginname';
            $html = WelcomeBoxWidget::render();
            $this->assertStringContainsString('Welcome back, loginname!', $html);
        }
    }
}
