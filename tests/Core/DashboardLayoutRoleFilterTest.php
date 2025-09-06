<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

/**

 * @group CORE
 */

class DashboardLayoutRoleFilterTest extends TestCase {

	protected function setUp(): void {
		MockStorage::$user_meta = array();
		MockStorage::$options   = array();
		MockStorage::$users     = array();

		// Reset registry widgets
		$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );

		// Reset role widgets map
		$ref2  = new \ReflectionClass( DashboardController::class );
		$prop2 = $ref2->getProperty( 'role_widgets' );
		$prop2->setAccessible( true );
		$prop2->setValue( null, array() );
	}

	public function test_user_meta_layout_is_filtered_by_role(): void {
				DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', null, array( 'roles' => array( 'member' ) ) );
				DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', null, array( 'roles' => array( 'artist' ) ) );

				MockStorage::$users[1]                            = (object) array( 'roles' => array( 'member' ) );
				MockStorage::$user_meta[1]['ap_dashboard_layout'] = array(
					array( 'id' => 'widget_alpha' ),
					array( 'id' => 'widget_beta' ),
					array( 'id' => 'unknown' ),
				);

				$layout = DashboardController::get_user_dashboard_layout( 1 );
				$this->assertSame( array( array( 'id' => 'widget_alpha' ) ), $layout );
	}

	public function test_option_layout_is_filtered_by_role(): void {
				DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', null, array( 'roles' => array( 'member' ) ) );
				DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', null, array( 'roles' => array( 'artist' ) ) );

				MockStorage::$users[2]                              = (object) array( 'roles' => array( 'member' ) );
				MockStorage::$options['ap_dashboard_widget_config'] = array(
					'member' => array(
						array( 'id' => 'widget_alpha' ),
						array( 'id' => 'widget_beta' ),
					),
				);

				$layout = DashboardController::get_user_dashboard_layout( 2 );
				$this->assertSame( array( array( 'id' => 'widget_alpha' ) ), $layout );
	}

	public function test_default_layout_is_filtered_by_role_widgets(): void {
				DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', null, array( 'roles' => array( 'member' ) ) );
				DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', null, array( 'roles' => array( 'artist' ) ) );

		$ref  = new \ReflectionClass( DashboardController::class );
		$prop = $ref->getProperty( 'role_widgets' );
		$prop->setAccessible( true );
		$prop->setValue(
			null,
			array(
				'member' => array( 'widget_alpha', 'widget_beta' ),
			)
		);

		MockStorage::$users[3] = (object) array( 'roles' => array( 'member' ) );

				$layout = DashboardController::get_user_dashboard_layout( 3 );
				$this->assertSame( array( array( 'id' => 'widget_alpha' ) ), $layout );
	}

	public function test_user_meta_layout_includes_widgets_without_roles(): void {
				DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', null, array( 'roles' => array( 'artist' ) ) );
				DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', null );

				MockStorage::$users[4]                            = (object) array( 'roles' => array( 'member' ) );
				MockStorage::$user_meta[4]['ap_dashboard_layout'] = array(
					array( 'id' => 'widget_alpha' ),
					array( 'id' => 'widget_beta' ),
				);

				$layout = DashboardController::get_user_dashboard_layout( 4 );
				$this->assertSame( array( array( 'id' => 'widget_beta' ) ), $layout );
	}

	public function test_option_layout_includes_widgets_without_roles(): void {
				DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', null, array( 'roles' => array( 'artist' ) ) );
				DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', null );

				MockStorage::$users[5]                              = (object) array( 'roles' => array( 'member' ) );
				MockStorage::$options['ap_dashboard_widget_config'] = array(
					'member' => array(
						array( 'id' => 'widget_alpha' ),
						array( 'id' => 'widget_beta' ),
					),
				);

				$layout = DashboardController::get_user_dashboard_layout( 5 );
				$this->assertSame( array( array( 'id' => 'widget_beta' ) ), $layout );
	}

	public function test_default_layout_includes_widgets_without_roles(): void {
				DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', null, array( 'roles' => array( 'artist' ) ) );
				DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', null );

		$ref  = new \ReflectionClass( DashboardController::class );
		$prop = $ref->getProperty( 'role_widgets' );
		$prop->setAccessible( true );
		$prop->setValue(
			null,
			array(
				'member' => array( 'widget_alpha', 'widget_beta' ),
			)
		);

		MockStorage::$users[6] = (object) array( 'roles' => array( 'member' ) );

				$layout = DashboardController::get_user_dashboard_layout( 6 );
				$this->assertSame( array( array( 'id' => 'widget_beta' ) ), $layout );
	}
}
