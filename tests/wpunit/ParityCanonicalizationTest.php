<?php
namespace ArtPulse\Audit\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Audit\Parity;
use ArtPulse\Audit\AuditBus;
use ArtPulse\Core\DashboardRenderer;

require_once __DIR__ . '/../TestStubs.php';

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
	}

	public function test_member_parity_clean(): void {
		DashboardWidgetRegistry::register( 'widget_favorites', 'Fav', '', '', '__return_null', array( 'roles' => array( 'member' ) ) );
		update_option( 'artpulse_widget_roles', array( 'member' => array( 'favorites' ) ) );
		update_option( 'artpulse_dashboard_layouts', array( 'member' => array( 'widget_widget_favorites' ) ) );
		DashboardRenderer::render( 'widget_widget_favorites', 0 );
		$report = Parity::compare_with_actual( 'member' );
		$this->assertSame( array(), $report['missing'] );
		$this->assertSame( array(), $report['extra'] );
	}
}
