<?php
namespace {
    require_once __DIR__ . '/../TestStubs.php';
}

namespace ArtPulse\Admin\Tests {
use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;
use ArtPulse\Widgets\Placeholder\ApPlaceholderWidget;

class UserLayoutManagerRoleLayoutTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        MockStorage::$options = [];
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

        DashboardWidgetRegistry::register('widget_alpha', 'Alpha', '', '', static function () {}, ['roles' => ['member']]);
        DashboardWidgetRegistry::register('widget_beta', 'Beta', '', '', static function () {}, ['roles' => ['artist']]);
    }

    public function test_role_layout_registers_placeholders_for_missing_widgets(): void {
        MockStorage::$options['ap_dashboard_widget_config'] = [
            'member' => [
                ['id' => 'widget_alpha', 'visible' => true],
                ['id' => 'ghost', 'visible' => true],
            ],
        ];

        $result = UserLayoutManager::get_role_layout('member');
        $this->assertSame([
            ['id' => 'widget_alpha', 'visible' => true],
            ['id' => 'ghost', 'visible' => true],
        ], $result['layout']);
        $this->assertSame(['ghost', 'widget_alpha'], $result['logs']);
        $def = DashboardWidgetRegistry::getById('ghost');
        $this->assertSame(ApPlaceholderWidget::class, $def['class']);
    }

    public function test_role_layout_falls_back_to_default_widgets(): void {
        $result = UserLayoutManager::get_role_layout('artist');
        $this->assertSame([
            ['id' => 'widget_beta', 'visible' => true],
        ], $result['layout']);
    }
}
}
