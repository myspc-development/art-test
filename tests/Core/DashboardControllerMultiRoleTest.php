<?php
namespace ArtPulse\Core\Tests {

	use PHPUnit\Framework\TestCase;
	use ArtPulse\Core\DashboardController;
	use ArtPulse\Core\DashboardWidgetRegistry;
	use ArtPulse\Tests\Stubs\MockStorage;

	/**

	 * @group core

	 */

	class DashboardControllerMultiRoleTest extends TestCase {
		protected function setUp(): void {
			MockStorage::$users = array();

			$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
			$prop = $ref->getProperty( 'widgets' );
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
			$_GET = array();
		}

		protected function tearDown(): void {
			$_GET               = array();
			MockStorage::$users = array();
			$ref                = new \ReflectionClass( DashboardWidgetRegistry::class );
			$prop               = $ref->getProperty( 'widgets' );
			$prop->setAccessible( true );
			$prop->setValue( null, array() );
			parent::tearDown();
		}

		public function test_member_priority_over_artist(): void {
			MockStorage::$users[1] = (object) array( 'roles' => array( 'artist', 'member' ) );
                        $layout                = DashboardController::get_user_dashboard_layout( 1 );
                        $this->assertSame( array( array( 'id' => 'widget_alpha' ) ), $layout );
		}

		public function test_artist_priority_over_organization(): void {
			MockStorage::$users[2] = (object) array( 'roles' => array( 'organization', 'artist' ) );
                        $layout                = DashboardController::get_user_dashboard_layout( 2 );
                        $this->assertSame( array( array( 'id' => 'widget_beta' ) ), $layout );
		}

		public function test_preview_role_override(): void {
			$_GET['ap_preview_role']    = 'organization';
			MockStorage::$current_roles = array( 'manage_options' );
			MockStorage::$users[3]      = (object) array( 'roles' => array( 'member', 'artist' ) );
                        $layout                     = DashboardController::get_user_dashboard_layout( 3 );
                        $this->assertSame( array( array( 'id' => 'widget_gamma' ) ), $layout );
		}
	}
}
