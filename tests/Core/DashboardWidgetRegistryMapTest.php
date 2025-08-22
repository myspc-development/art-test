<?php
namespace ArtPulse\Core\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardWidgetRegistryMapTest extends WP_UnitTestCase
{
    public function set_up(): void
    {
        parent::set_up();
        $ref  = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);
        foreach (['member', 'artist', 'organization'] as $role) {
            if (!get_role($role)) {
                add_role($role, ucfirst($role));
            }
        }
    }

    public function test_get_role_widget_map_groups_widgets(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', '__return_null', ['roles' => ['member']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', '__return_null', ['roles' => ['artist']]);
        DashboardWidgetRegistry::register('gamma', 'Gamma', '', '', '__return_null');

        $map = DashboardWidgetRegistry::get_role_widget_map();
        $member_ids = wp_list_pluck($map['member'], 'id');
        $artist_ids = wp_list_pluck($map['artist'], 'id');

        $this->assertContains('alpha', $member_ids);
        $this->assertNotContains('beta', $member_ids);
        $this->assertContains('beta', $artist_ids);
        $this->assertContains('gamma', $member_ids);
        $this->assertContains('gamma', $artist_ids);
    }
}
