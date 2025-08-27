<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\WidgetRegistry;

class WidgetAliasTest extends \WP_UnitTestCase {
    public function set_up(): void {
        parent::set_up();
        $this->resetRegistries();
        WidgetRegistry::register( 'widget_my_follows', static fn() => '<section data-slug="widget_my_follows"></section>' );
        DashboardWidgetRegistry::register( 'widget_my_follows', [
            'title'          => 'widget_my_follows',
            'render_callback'=> static function() { echo '<section data-slug="widget_my_follows"></section>'; },
            'roles'          => [],
        ] );
        wp_set_current_user( self::factory()->user->create( ['role' => 'member'] ) );
        set_query_var( 'ap_role', 'member' );
    }

    private function resetRegistries(): void {
        $ref = new \ReflectionClass( DashboardWidgetRegistry::class );
        foreach ( ['widgets','builder_widgets'] as $prop ) {
            $p = $ref->getProperty( $prop );
            $p->setAccessible( true );
            $p->setValue( null, [] );
        }
        $ref2 = new \ReflectionClass( WidgetRegistry::class );
        $p2   = $ref2->getProperty( 'widgets' );
        $p2->setAccessible( true );
        $p2->setValue( null, [] );
    }

    public function test_aliases_render_canonical_widget(): void {
        $html1 = DashboardWidgetRegistry::render( 'widget_followed_artists', 'member' );
        $this->assertStringContainsString( 'widget_my_follows', $html1 );
        $html2 = DashboardWidgetRegistry::render( 'followed_artists', 'member' );
        $this->assertStringContainsString( 'widget_my_follows', $html2 );
    }
}

