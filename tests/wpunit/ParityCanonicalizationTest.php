<?php
namespace ArtPulse\Audit\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Audit\Parity;
use ArtPulse\Audit\AuditBus;
use ArtPulse\Core\DashboardRenderer;
use ArtPulse\Admin\UserLayoutManager;

require_once __DIR__ . '/../TestStubs.php';

/**

 * @group wpunit

 */

class ParityCanonicalizationTest extends \WP_UnitTestCase {

	protected function setUp(): void {
		parent::setUp();
		$ref = new \ReflectionClass( DashboardWidgetRegistry::class );
		foreach ( array( 'widgets', 'builder_widgets', 'id_map', 'issues', 'logged_duplicates', 'aliases' ) as $prop ) {
			if ( $ref->hasProperty( $prop ) ) {
				$p = $ref->getProperty( $prop );
				$p->setAccessible( true );
				$p->setValue( null, array() );
			}
		}
                AuditBus::reset();
                update_option( 'artpulse_widget_roles', array() );
                update_option( 'artpulse_hidden_widgets', array() );
                update_option( 'artpulse_dashboard_layouts', array() );
                delete_option( 'ap_dashboard_widget_config' );
        }

        public function test_member_parity_clean(): void {
                DashboardWidgetRegistry::register( 'widget_events', 'Events', '', '', '__return_null', array( 'roles' => array( 'member' ) ) );
                DashboardWidgetRegistry::register( 'widget_favorites', 'Fav', '', '', '__return_null', array( 'roles' => array( 'member' ) ) );
                update_option( 'artpulse_widget_roles', array( 'member' => array( 'widget_events', 'widget_favorites' ) ) );
                UserLayoutManager::save_role_layout( 'member', array(
                        array( 'id' => 'widget_events' ),
                        array( 'id' => 'widget_favorites' ),
                ) );
                DashboardRenderer::render( 'widget_events', 0 );
                DashboardRenderer::render( 'widget_favorites', 0 );
                $report   = Parity::compare_with_actual( 'member' );
                $expected = array( 'widget_events', 'widget_favorites' );
                $this->assertSame( $expected, $report['would_render'] );
                $this->assertSame( $expected, $report['did_render'] );
                $this->assertSame( array(), $report['missing'] );
                $this->assertSame( array(), $report['extra'] );
        }
}
