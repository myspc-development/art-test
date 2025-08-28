<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\UserDashboardManager;

class MultiRoleLayoutTest extends \WP_UnitTestCase
{
    private int $userId;

    public function set_up()
    {
        parent::set_up();
        // Ensure custom roles exist
        if (!get_role('member')) {
            add_role('member', 'Member');
        }
        if (!get_role('artist')) {
            add_role('artist', 'Artist');
        }

        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', '__return_null', ['roles' => ['member']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', '__return_null', ['roles' => ['artist']]);
        DashboardWidgetRegistry::register('shared', 'Shared', '', '', '__return_null', ['roles' => ['member', 'artist']]);

        UserLayoutManager::save_role_layout('member', [
            ['id' => 'alpha', 'visible' => true],
            ['id' => 'shared', 'visible' => true],
        ]);
        UserLayoutManager::save_role_layout('artist', [
            ['id' => 'beta', 'visible' => false],
            ['id' => 'shared', 'visible' => true],
        ]);

        $this->userId = self::factory()->user->create(['role' => 'member']);
        $user = get_user_by('id', $this->userId);
        $user->add_role('artist');

        UserDashboardManager::register();
        do_action('rest_api_init');
    }

    public function test_layout_merges_multiple_roles(): void
    {
        wp_set_current_user($this->userId);
        $resp = UserDashboardManager::getDashboardLayout();
        $data = $resp->get_data();
        $this->assertSame(['alpha', 'shared', 'beta'], $data['layout']);
        $this->assertSame([
            'alpha' => true,
            'shared' => true,
            'beta'  => false,
        ], $data['visibility']);
    }
}
