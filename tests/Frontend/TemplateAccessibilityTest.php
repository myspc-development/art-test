<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;

/**
 * @group FRONTEND
 */
class TemplateAccessibilityTest extends WP_UnitTestCase {
        public function test_my_events_widget_has_accessible_attributes(): void {
                wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
                $template = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/widgets/my-events.php';
                ob_start();
                include $template;
                $html = ob_get_clean();

                $dom = new \DOMDocument();
                libxml_use_internal_errors( true );
                $dom->loadHTML( $html );
                libxml_clear_errors();
                $div = $dom->getElementById( 'my-events' );
                $this->assertSame( 'region', $div->getAttribute( 'role' ) );
                $this->assertSame( 'my-events-title', $div->getAttribute( 'aria-labelledby' ) );
        }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         */
        public function test_dashboard_role_template_has_accessible_attributes(): void {
                wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
                if ( ! defined( 'AP_DASHBOARD_RENDERING' ) ) {
                        define( 'AP_DASHBOARD_RENDERING', true );
                }
                $user_role = 'member';
                $template  = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/dashboard-role.php';
                ob_start();
                include $template;
                $html = ob_get_clean();

                $dom = new \DOMDocument();
                libxml_use_internal_errors( true );
                $dom->loadHTML( $html );
                libxml_clear_errors();
                $section = $dom->getElementById( 'ap-panel-member' );
                $this->assertSame( 'tabpanel', $section->getAttribute( 'role' ) );
                $this->assertSame( 'ap-tab-member', $section->getAttribute( 'aria-labelledby' ) );
                $this->assertSame( 'member', $section->getAttribute( 'data-role' ) );
        }

        public function test_dashboard_empty_state_has_status_and_aria_live(): void {
                $template = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/partials/dashboard-empty-state.php';
                ob_start();
                include $template;
                $html = ob_get_clean();

                $dom = new \DOMDocument();
                libxml_use_internal_errors( true );
                $dom->loadHTML( $html );
                libxml_clear_errors();
                $div = $dom->getElementsByTagName( 'div' )->item( 0 );
                $this->assertSame( 'status', $div->getAttribute( 'role' ) );
                $this->assertSame( 'polite', $div->getAttribute( 'aria-live' ) );
        }
}
