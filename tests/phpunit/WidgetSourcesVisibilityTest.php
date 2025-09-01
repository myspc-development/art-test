<?php
namespace ArtPulse\Audit\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Cli\WidgetAudit;
use ArtPulse\Core\DashboardWidgetRegistry;

require_once __DIR__ . '/../TestStubs.php';

/**

 * @group PHPUNIT

 */

class WidgetSourcesVisibilityTest extends TestCase {

        protected function setUp(): void {
		$ref = new \ReflectionClass( DashboardWidgetRegistry::class );
		foreach ( array( 'widgets', 'builder_widgets', 'id_map', 'issues', 'logged_duplicates', 'aliases' ) as $prop ) {
			if ( $ref->hasProperty( $prop ) ) {
				$p = $ref->getProperty( $prop );
				$p->setAccessible( true );
				$p->setValue( null, array() );
			}
		}
		update_option( 'artpulse_widget_roles', array() );
		update_option( 'artpulse_hidden_widgets', array() );
	}

        public function test_roles_from_visibility_option(): void {
                DashboardWidgetRegistry::register( 'widget_demo', 'Demo', '', '', [self::class, 'blank'] );
                update_option( 'artpulse_widget_roles', array( 'member' => array( 'widget_demo' ) ) );

                $cmd  = new WidgetAudit();
                $rows = $cmd->widgets( array(), array( 'format' => 'table' ) );

                $this->assertNotEmpty( $rows );
                $this->assertSame( 'member', $rows[0]['roles_from_visibility'] );
        }

        public static function blank(): string { return ''; }
}
