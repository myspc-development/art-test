<?php
namespace {
    require_once __DIR__ . '/../TestStubs.php';
    if (!defined('ARTPULSE_PLUGIN_FILE')) {
        define('ARTPULSE_PLUGIN_FILE', dirname(__DIR__, 2) . '/artpulse.php');
    }
}

namespace ArtPulse\Core {
    /** Simple role object for capability checks. */
    class RoleStub {
        private array $caps;
        public function __construct(array $caps) { $this->caps = $caps; }
        public function has_cap($cap): bool { return in_array($cap, $this->caps, true); }
    }
    function get_role(string $role) {
        return new RoleStub([]); // roles have no capabilities in tests
    }
    function do_action($tag, ...$args) {
        \ArtPulse\Core\Tests\DashboardLayoutTest::$actions[] = [$tag, $args];
    }
}

namespace ArtPulse\Core\Tests {
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

class DashboardLayoutTest extends TestCase {
    public static array $actions = [];

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
        Functions\when('plugin_dir_path')->alias(fn($file) => dirname($file) . '/');
        MockStorage::$user_meta = [];
        MockStorage::$options = [];
        MockStorage::$users = [];
        MockStorage::$current_roles = [];
        self::$actions = [];

        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);

        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue(null, []);
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_default_presets_loaded_per_role(): void {
        DashboardWidgetRegistry::register('widget_news', 'News', '', '', static function () {}, ['roles' => ['member']]);
        DashboardWidgetRegistry::register('artist_inbox_preview', 'Inbox Preview', '', '', static function () {}, ['roles' => ['artist']]);

        $presets = DashboardController::get_default_presets();
        $this->assertSame('member', $presets['member_default']['role']);
        $this->assertSame([
            ['id' => 'widget_news', 'visible' => true],
        ], $presets['member_default']['layout']);
        $this->assertSame('artist', $presets['artist_default']['role']);
        $this->assertSame([
            ['id' => 'artist_inbox_preview', 'visible' => true],
        ], $presets['artist_default']['layout']);
    }

    public function test_fallback_layout_and_filtering(): void {
        DashboardWidgetRegistry::register('widget_alpha', 'Alpha', '', '', static function () {}, ['roles' => ['member']]);
        DashboardWidgetRegistry::register('widget_beta', 'Beta', '', '', static function () {}, ['roles' => ['member'], 'capability' => 'edit_posts']);
        DashboardWidgetRegistry::register('widget_gamma', 'Gamma', '', '', static function () {}, ['roles' => ['artist']]);

        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue(null, [
            'member' => ['widget_alpha', 'widget_beta', 'widget_gamma'],
        ]);

        MockStorage::$users[1] = (object)['roles' => ['member']];
        $layout = DashboardController::get_user_dashboard_layout(1);
        $this->assertSame([
            ['id' => 'widget_alpha', 'visible' => true],
        ], $layout);
    }

    public function test_saved_layout_overrides_fallback(): void {
        DashboardWidgetRegistry::register('widget_alpha', 'Alpha', '', '', static function () {}, ['roles' => ['member']]);
        DashboardWidgetRegistry::register('widget_beta', 'Beta', '', '', static function () {}, ['roles' => ['member']]);

        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue(null, ['member' => ['widget_beta']]);

        MockStorage::$users[2] = (object)['roles' => ['member']];
        MockStorage::$user_meta[2]['ap_dashboard_layout'] = [ ['id' => 'widget_alpha', 'visible' => true] ];
        $layout = DashboardController::get_user_dashboard_layout(2);
        $this->assertSame([
            ['id' => 'widget_alpha', 'visible' => true],
        ], $layout);
    }

    public function test_emits_action_when_layout_empty(): void {
        DashboardWidgetRegistry::register('widget_beta', 'Beta', '', '', static function () {}, ['roles' => ['artist']]);
        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue(null, ['member' => ['widget_beta']]);
        MockStorage::$users[3] = (object)['roles' => ['member']];
        $layout = DashboardController::get_user_dashboard_layout(3);
        $this->assertSame([
            ['id' => 'empty_dashboard', 'visible' => true],
        ], $layout);
        $this->assertSame([['ap_dashboard_empty_layout', [3, 'member']]], self::$actions);
    }

    public function test_preview_role_renders_layout(): void {
        DashboardWidgetRegistry::register('widget_alpha', 'Alpha', '', '', static function () {}, ['roles' => ['member']]);
        DashboardWidgetRegistry::register('widget_beta', 'Beta', '', '', static function () {}, ['roles' => ['artist']]);
        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue(null, [
            'member' => ['widget_alpha'],
            'artist' => ['widget_beta'],
        ]);
        MockStorage::$users[4] = (object)['roles' => ['administrator']];
        MockStorage::$current_roles = ['manage_options'];
        $_GET['ap_preview_role'] = 'artist';
        $layout = DashboardController::get_user_dashboard_layout(4);
        unset($_GET['ap_preview_role']);
        $this->assertSame([
            ['id' => 'widget_beta', 'visible' => true],
        ], $layout);
    }

    public function test_filter_accessible_layout_excludes_by_capability_and_role(): void {
        DashboardWidgetRegistry::register('widget_alpha', 'Alpha', '', '', static function () {}, ['roles' => ['member']]);
        DashboardWidgetRegistry::register('widget_beta', 'Beta', '', '', static function () {}, ['roles' => ['member'], 'capability' => 'edit_posts']);
        DashboardWidgetRegistry::register('widget_gamma', 'Gamma', '', '', static function () {}, ['roles' => ['artist']]);
        $layout = [
            ['id' => 'widget_alpha', 'visible' => true],
            ['id' => 'widget_beta', 'visible' => true],
            ['id' => 'widget_gamma', 'visible' => true],
            ['id' => 'missing', 'visible' => true],
        ];
        $ref = new \ReflectionClass(DashboardController::class);
        $m = $ref->getMethod('filter_accessible_layout');
        $m->setAccessible(true);
        $filtered = $m->invoke(null, $layout, 'member');
        $this->assertSame([
            ['id' => 'widget_alpha', 'visible' => true],
        ], $filtered);
    }
}
}
