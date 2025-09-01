<?php
namespace ArtPulse\Cli\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Cli\WidgetAudit;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Widgets\Placeholder\ApPlaceholderWidget;
use ArtPulse\Widgets\TestWidget;

require_once __DIR__ . '/../TestStubs.php';
require_once __DIR__ . '/fixtures/TestWidget.php';

/**

 * @group PHPUNIT

 */

class WidgetAuditFixTest extends TestCase {

	protected function setUp(): void {
		$ref = new \ReflectionClass( DashboardWidgetRegistry::class );
		foreach ( array( 'widgets', 'builder_widgets', 'id_map', 'issues', 'logged_duplicates', 'aliases' ) as $prop ) {
			if ( $ref->hasProperty( $prop ) ) {
				$p = $ref->getProperty( $prop );
				$p->setAccessible( true );
				$p->setValue( null, array() );
			}
		}
		\ArtPulse\Tests\Stubs\MockStorage::$options = array();
	}

	public function test_fix_unhide_activate_and_bind(): void {
		update_option( 'artpulse_hidden_widgets', array( 'member' => array( 'widget_test' ) ) );
		update_option( 'artpulse_widget_flags', array( 'widget_test' => array( 'status' => 'inactive' ) ) );

		DashboardWidgetRegistry::register( 'widget_test', 'Test', '', '', array( ApPlaceholderWidget::class, 'render' ) );

		$cmd = new WidgetAudit();
		$cmd->fix(
			array(),
			array(
				'role'         => 'member',
				'unhide'       => true,
				'activate-all' => true,
			)
		);

		$hidden = get_option( 'artpulse_hidden_widgets' );
		$this->assertSame( array(), $hidden['member'] );
		$flags = get_option( 'artpulse_widget_flags' );
		$this->assertSame( 'active', $flags['widget_test']['status'] );
		$def = DashboardWidgetRegistry::get( 'widget_test' );
		$this->assertSame( TestWidget::class, $def['class'] );
	}
}
