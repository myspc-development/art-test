<?php
namespace ArtPulse\Audit\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Widgets\Placeholder\ApPlaceholderWidget;
use ArtPulse\Audit\WidgetSources;
use ArtPulse\Audit\Parity;

require_once __DIR__ . '/../TestStubs.php';

/**

 * @group phpunit

 */

class ParityPlaceholderTest extends TestCase {

	protected function setUp(): void {
		$ref = new \ReflectionClass( DashboardWidgetRegistry::class );
		foreach ( array( 'widgets', 'builder_widgets', 'id_map', 'issues', 'logged_duplicates', 'aliases' ) as $prop ) {
			if ( $ref->hasProperty( $prop ) ) {
				$p = $ref->getProperty( $prop );
				$p->setAccessible( true );
				$p->setValue( null, array() );
			}
		}
	}

	public function test_placeholder_flagged_as_problem(): void {
		DashboardWidgetRegistry::register( 'widget_test', 'Test', '', '', array( ApPlaceholderWidget::class, 'render' ) );
		$reg = WidgetSources::get_registry();
		$this->assertTrue( $reg['widget_test']['is_placeholder'] );
		$problems = Parity::problems();
		$this->assertSame( 'placeholder_renderer', $problems['widget_test'] );
	}
}
