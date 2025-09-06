<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\WidgetRoleSync;
use ArtPulse\Core\DashboardController;

/**

 * @group CORE
 */

class WidgetRoleSyncTest extends TestCase {

	protected function setUp(): void {
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );

		$ref2  = new \ReflectionClass( DashboardController::class );
		$prop2 = $ref2->getProperty( 'role_widgets' );
		$prop2->setAccessible( true );
		$prop2->setValue( null, array() );
	}

	public function test_roles_inferred_for_missing_widget(): void {
		DashboardWidgetRegistry::register_widget(
			'artist_portfolio_widget',
			array(
				'label'    => 'Portfolio',
				'callback' => '__return_null',
			)
		);

		WidgetRoleSync::sync();

		$defs = DashboardWidgetRegistry::get_all();
		$this->assertSame( array( 'artist' ), $defs['artist_portfolio_widget']['roles'] );
	}
}
