<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Admin\LayoutSnapshotManager;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

require_once __DIR__ . '/../../includes/role-upgrade-handler.php';

class RoleUpgradeHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        MockStorage::$users = [];
        MockStorage::$user_meta = [];
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
    }

    public function test_widgets_merge_and_layout_is_snapshotted(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', '__return_null');
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', '__return_null');
        DashboardWidgetRegistry::register('gamma', 'Gamma', '', '', '__return_null');

        UserLayoutManager::save_role_layout('member', [
            ['id' => 'alpha', 'visible' => true],
            ['id' => 'beta', 'visible' => false],
        ]);
        UserLayoutManager::save_role_layout('artist', [
            ['id' => 'gamma', 'visible' => true],
            ['id' => 'beta', 'visible' => true],
        ]);

        MockStorage::$users[1] = (object)['roles' => ['member', 'artist']];
        MockStorage::$user_meta[1]['ap_dashboard_layout'] = [
            ['id' => 'alpha', 'visible' => true],
            ['id' => 'beta', 'visible' => false],
        ];

        ap_merge_dashboard_on_role_upgrade(1, 'artist', ['member']);

        $expected = [
            ['id' => 'alpha', 'visible' => true],
            ['id' => 'beta', 'visible' => false],
            ['id' => 'gamma', 'visible' => true],
        ];
        $this->assertSame($expected, MockStorage::$user_meta[1]['ap_dashboard_layout']);

        $snaps = MockStorage::$user_meta[1][LayoutSnapshotManager::META_KEY] ?? [];
        $this->assertCount(1, $snaps);
        $this->assertSame('member', $snaps[0]['role']);
        $this->assertSame([
            ['id' => 'alpha', 'visible' => true],
            ['id' => 'beta', 'visible' => false],
        ], $snaps[0]['layout']);
    }

    public function test_layout_defaults_when_none_saved(): void
    {
        DashboardWidgetRegistry::register('a', 'A', '', '', '__return_null');
        DashboardWidgetRegistry::register('b', 'B', '', '', '__return_null');

        UserLayoutManager::save_role_layout('member', [ ['id' => 'a', 'visible' => true] ]);
        UserLayoutManager::save_role_layout('artist', [ ['id' => 'b', 'visible' => true] ]);

        MockStorage::$users[2] = (object)['roles' => ['member', 'artist']];

        ap_merge_dashboard_on_role_upgrade(2, 'artist', ['member']);

        $expected = [
            ['id' => 'a', 'visible' => true],
            ['id' => 'b', 'visible' => true],
        ];
        $this->assertSame($expected, MockStorage::$user_meta[2]['ap_dashboard_layout']);
    }
}
