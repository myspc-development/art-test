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

    public function test_render_for_each_role(): void {
        $member = self::factory()->user->create(['role'=>'member']);
        wp_set_current_user($member);
        ob_start();
        DashboardWidgetRegistry::render_for_role($member);
        $html = ob_get_clean();
        $this->assertStringContainsString('alpha',$html);
        $this->assertStringNotContainsString('beta',$html);
        $this->assertStringNotContainsString('gamma',$html);

        $artist = self::factory()->user->create(['role'=>'artist']);
        wp_set_current_user($artist);
        ob_start();
        DashboardWidgetRegistry::render_for_role($artist);
        $html = ob_get_clean();
        $this->assertStringContainsString('beta',$html);
        $this->assertStringNotContainsString('alpha',$html);

        $org = self::factory()->user->create(['role'=>'organization']);
        wp_set_current_user($org);
        ob_start();
        DashboardWidgetRegistry::render_for_role($org);
        $html = ob_get_clean();
        $this->assertStringContainsString('gamma',$html);
        $this->assertStringNotContainsString('alpha',$html);
    }
}
