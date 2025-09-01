<?php
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardPresets;

/**

 * @group INTEGRATION

 */

class RoleLayoutRenderTest extends \WP_UnitTestCase {
    public function set_up(): void {
        parent::set_up();
        if ( ! get_role( 'member' ) ) {
            add_role( 'member', 'Member' );
        }
        $uid = self::factory()->user->create( array( 'role' => 'member' ) );
        wp_set_current_user( $uid );

        // reset builder widgets
        $ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
        $prop = $ref->getProperty( 'builder_widgets' );
        $prop->setAccessible( true );
        $prop->setValue( null, array() );

        // register placeholder builder widget
        DashboardWidgetRegistry::register(
            'widget_placeholder',
            array(
                'title'           => 'Placeholder Widget',
                'render_callback' => static function () {
                    echo 'placeholder';
                },
                'roles'           => array( 'member' ),
            )
        );
    }

    public function tear_down(): void {
        DashboardPresets::resetCache();
        parent::tear_down();
    }

    public function test_stops_when_presets_empty(): void {
        $ref  = new \ReflectionClass( DashboardPresets::class );
        $prop = $ref->getProperty( 'cache' );
        $prop->setAccessible( true );
        $prop->setValue( null, array( 'member' => array() ) );

        $this->assertSame( '', DashboardWidgetRegistry::render_role_layout( 'member' ) );
    }

    public function test_renders_placeholder_when_widgets_missing(): void {
        $ref  = new \ReflectionClass( DashboardPresets::class );
        $prop = $ref->getProperty( 'cache' );
        $prop->setAccessible( true );
        $prop->setValue( null, array( 'member' => array( 'widget_missing' ) ) );

        $html = DashboardWidgetRegistry::render_role_layout( 'member' );
        $this->assertStringContainsString( 'placeholder', $html );
    }
}
