<?php
namespace ArtPulse\Core\Tests;

require_once __DIR__ . '/../TestStubs.php';

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

class DashboardPreviewUserTest extends TestCase {
    protected function setUp(): void {
        MockStorage::$users = [];
        MockStorage::$current_roles = ['manage_options'];

        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);

        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue(null, [
            'member' => ['widget_alpha'],
            'artist' => ['widget_beta'],
        ]);

        DashboardWidgetRegistry::register_widget('widget_alpha', [
            'label'    => 'Alpha',
            'callback' => '__return_null',
            'roles'    => ['member'],
        ]);
        DashboardWidgetRegistry::register_widget('widget_beta', [
            'label'    => 'Beta',
            'callback' => '__return_null',
            'roles'    => ['artist'],
        ]);

        $_GET = [];
    }

    protected function tearDown(): void {
        $_GET = [];
        MockStorage::$users = [];
        MockStorage::$current_roles = [];
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
        parent::tearDown();
    }

    public function test_preview_user_parameter_overrides_user(): void {
        MockStorage::$users[1] = (object)['roles' => ['member']];
        MockStorage::$users[2] = (object)['roles' => ['artist']];

        $layoutDefault = DashboardController::get_user_dashboard_layout(1);
        $this->assertSame([
            ['id' => 'empty_dashboard', 'visible' => true],
        ], $layoutDefault);

        $_GET['ap_preview_user']  = '2';
        $_GET['ap_preview_nonce'] = wp_create_nonce('ap_preview');
        $layoutPreview = DashboardController::get_user_dashboard_layout(1);
        $this->assertSame([
            ['id' => 'widget_beta', 'visible' => true],
        ], $layoutPreview);
        unset($_GET['ap_preview_user'], $_GET['ap_preview_nonce']);
    }

    public function test_preview_user_requires_nonce(): void {
        MockStorage::$users[1] = (object)['roles' => ['member']];
        MockStorage::$users[2] = (object)['roles' => ['artist']];

        $_GET['ap_preview_user'] = '2';
        $layout = DashboardController::get_user_dashboard_layout(1);
        $this->assertSame([
            ['id' => 'empty_dashboard', 'visible' => true],
        ], $layout);
        unset($_GET['ap_preview_user']);
    }

    public function test_non_admin_cannot_preview_user(): void {
        MockStorage::$users[1] = (object)['roles' => ['member']];
        MockStorage::$users[2] = (object)['roles' => ['artist']];
        MockStorage::$current_roles = [];

        $_GET['ap_preview_user']  = '2';
        $_GET['ap_preview_nonce'] = wp_create_nonce('ap_preview');
        $layout = DashboardController::get_user_dashboard_layout(1);
        $this->assertSame([
            ['id' => 'empty_dashboard', 'visible' => true],
        ], $layout);
        unset($_GET['ap_preview_user'], $_GET['ap_preview_nonce']);
    }

    public function test_preview_user_does_not_persist_layout(): void {
        MockStorage::$users[1] = (object)['roles' => ['administrator']];
        MockStorage::$users[2] = (object)['roles' => ['artist']];
        MockStorage::$user_meta[1]['ap_dashboard_layout'] = [ ['id' => 'widget_alpha', 'visible' => true] ];

        $_GET['ap_preview_user']  = '2';
        $_GET['ap_preview_nonce'] = wp_create_nonce('ap_preview');
        DashboardController::get_user_dashboard_layout(1);
        unset($_GET['ap_preview_user'], $_GET['ap_preview_nonce']);

        $this->assertSame([
            ['id' => 'widget_alpha', 'visible' => true],
        ], MockStorage::$user_meta[1]['ap_dashboard_layout']);
    }
}
