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

        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);

        $ref2 = new \ReflectionClass(DashboardController::class);
        $prop2 = $ref2->getProperty('role_widgets');
        $prop2->setAccessible(true);
        $prop2->setValue([
            'member' => ['alpha'],
            'artist' => ['beta'],
        ]);

        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', static function () {}, ['roles' => ['member']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', static function () {}, ['roles' => ['artist']]);
    }

    public function test_role_layout_registers_placeholders_for_missing_widgets(): void {
        MockStorage::$options['ap_dashboard_widget_config'] = [
            'member' => [
                ['id' => 'alpha'],
                ['id' => 'ghost'],
            ],
        ];

        $result = UserLayoutManager::get_role_layout('member');
        $this->assertSame([
            ['id' => 'alpha', 'visible' => true],
            ['id' => 'ghost', 'visible' => true],
        ], $result['layout']);
        $this->assertSame(['ghost'], $result['logs']);
        $def = DashboardWidgetRegistry::getById('ghost');
        $this->assertSame(ApPlaceholderWidget::class, $def['class']);
    }

    public function test_role_layout_falls_back_to_default_widgets(): void {
        $result = UserLayoutManager::get_role_layout('artist');
        $this->assertSame([
            ['id' => 'beta', 'visible' => true],
        ], $result['layout']);
    }
}
}
