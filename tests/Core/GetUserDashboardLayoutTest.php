<?php
namespace ArtPulse\Core\Tests {

	use PHPUnit\Framework\TestCase;
	use ArtPulse\Core\DashboardController;
	use ArtPulse\Core\DashboardWidgetRegistry;
	use ArtPulse\Tests\Stubs\MockStorage;

	/**

	 * @group core

	 */

	class GetUserDashboardLayoutTest extends TestCase {

		protected function setUp(): void {
			MockStorage::$users = array();
			$ref                = new \ReflectionClass( DashboardWidgetRegistry::class );
			$prop               = $ref->getProperty( 'widgets' );
			$prop->setAccessible( true );
			$prop->setValue( null, array() );

			$ref2  = new \ReflectionClass( DashboardController::class );
			$prop2 = $ref2->getProperty( 'role_widgets' );
			$prop2->setAccessible( true );
			$prop2->setValue(
				null,
                                array(
                                        'member'       => array( 'widget_alpha' ),
                                        'artist'       => array( 'widget_beta' ),
                                        'organization' => array( 'widget_gamma' ),
                                )
			);

                        DashboardWidgetRegistry::register_widget(
                                'widget_alpha',
                                array(
                                        'label'    => 'Alpha',
                                        'callback' => '__return_null',
                                        'roles'    => array( 'member' ),
                                )
                        );
                        DashboardWidgetRegistry::register_widget(
                                'widget_beta',
                                array(
                                        'label'    => 'Beta',
                                        'callback' => '__return_null',
                                        'roles'    => array( 'artist' ),
                                )
                        );
                        DashboardWidgetRegistry::register_widget(
                                'widget_gamma',
                                array(
                                        'label'    => 'Gamma',
                                        'callback' => '__return_null',
                                        'roles'    => array( 'organization' ),
                                )
                        );
		}

		public static function layoutProvider(): iterable {
                       yield 'member' => array( 'member', array( array( 'id' => 'widget_alpha' ) ) );
                       yield 'artist' => array( 'artist', array( array( 'id' => 'widget_beta' ) ) );
                       yield 'organization' => array( 'organization', array( array( 'id' => 'widget_gamma' ) ) );
			yield 'invalid role' => array( 'invalid', array() );
		}

		/**
		 * @dataProvider layoutProvider
		 */
		public function test_get_user_dashboard_layout( string $role, array $expected ): void {
			MockStorage::$users[1] = (object) array( 'roles' => array( $role ) );
			$layout                = DashboardController::get_user_dashboard_layout( 1 );
			$this->assertSame( $expected, $layout );
		}
	}
}
