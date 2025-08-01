<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardWidgetRenderTest extends \WP_UnitTestCase {
    public function set_up(): void {
        parent::set_up();
        $ref = new \ReflectionClass(DashboardWidgetRegistry::class);
        $prop = $ref->getProperty('widgets');
        $prop->setAccessible(true);
        $prop->setValue([]);

        DashboardWidgetRegistry::register('alpha','Alpha','', '', function(){ echo 'alpha'; }, ['roles'=>['member']]);
        DashboardWidgetRegistry::register('beta','Beta','', '', function(){ echo 'beta'; }, ['roles'=>['artist']]);
        DashboardWidgetRegistry::register('gamma','Gamma','', '', function(){ echo 'gamma'; }, ['roles'=>['organization']]);
    }

    public static function roleProvider(): iterable {
        yield 'member' => ['member', ['alpha']];
        yield 'artist' => ['artist', ['beta']];
        yield 'organization' => ['organization', ['gamma']];
    }

    /**
     * @dataProvider roleProvider
     */
    public function test_render_for_role(string $role, array $expected): void {
        $uid = self::factory()->user->create(['role'=>$role]);
        wp_set_current_user($uid);
        ob_start();
        DashboardWidgetRegistry::render_for_role($uid);
        $html = ob_get_clean();
        foreach ($expected as $id) {
            $this->assertStringContainsString($id, $html);
        }
        foreach (array_diff(['alpha', 'beta', 'gamma'], $expected) as $other) {
            $this->assertStringNotContainsString($other, $html);
        }
    }
}
