<?php
namespace ArtPulse\Core\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardWidgetRegistryCallbacksTest extends WP_UnitTestCase
{
    public function set_up()
    {
        parent::set_up();
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
        if (!get_role('member')) {
            add_role('member', 'Member');
        }
        $uid = self::factory()->user->create(['role' => 'member']);
        wp_set_current_user($uid);
    }

    public function test_member_widgets_return_callable_callbacks(): void
    {
        DashboardWidgetRegistry::register('alpha', 'Alpha', '', '', '__return_null', ['roles' => ['member']]);
        DashboardWidgetRegistry::register('beta', 'Beta', '', '', static function () {}, ['roles' => ['member']]);

        $widgets = DashboardWidgetRegistry::get_widgets('member');

        $this->assertNotEmpty($widgets);
        foreach ($widgets as $cb) {
            $this->assertTrue(is_callable($cb));
        }
    }
}
