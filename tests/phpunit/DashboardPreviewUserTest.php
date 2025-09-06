<?php
namespace ArtPulse\Core\Tests;

require_once __DIR__ . '/../TestStubs.php';

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;

/**

 * @group PHPUNIT
 */

class DashboardPreviewUserTest extends TestCase {
	protected function setUp(): void {
		MockStorage::$users         = array();
		MockStorage::$current_roles = array( 'manage_options' );

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
				'member' => array( 'widget_alpha' ),
				'artist' => array( 'widget_beta' ),
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

		$_GET = array();
	}

	protected function tearDown(): void {
		$_GET                       = array();
		MockStorage::$users         = array();
		MockStorage::$current_roles = array();
		$ref                        = new \ReflectionClass( DashboardWidgetRegistry::class );
		$prop                       = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
		parent::tearDown();
	}

	public function test_preview_user_parameter_overrides_user_with_valid_nonce(): void {
		MockStorage::$users[1] = (object) array( 'roles' => array( 'member' ) );
		MockStorage::$users[2] = (object) array( 'roles' => array( 'artist' ) );

		$layoutDefault = DashboardController::get_user_dashboard_layout( 1 );
		$this->assertSame(
			array(
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
			),
			$layoutDefault
		);

		$_GET['ap_preview_user']      = '2';
			$_GET['ap_preview_nonce'] = 'test-nonce';
		$layoutPreview                = DashboardController::get_user_dashboard_layout( 1 );
		$this->assertSame(
			array(
				array(
					'id'      => 'widget_beta',
					'visible' => true,
				),
			),
			$layoutPreview
		);
		unset( $_GET['ap_preview_user'], $_GET['ap_preview_nonce'] );
	}

	public function test_preview_user_missing_nonce_is_ignored(): void {
		MockStorage::$users[1] = (object) array( 'roles' => array( 'member' ) );
		MockStorage::$users[2] = (object) array( 'roles' => array( 'artist' ) );

		$_GET['ap_preview_user'] = '2';
		$layout                  = DashboardController::get_user_dashboard_layout( 1 );
		$this->assertSame(
			array(
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
			),
			$layout
		);
		unset( $_GET['ap_preview_user'] );
	}

	public function test_preview_user_fails_for_unauthorized_user(): void {
		MockStorage::$users[1]      = (object) array( 'roles' => array( 'member' ) );
		MockStorage::$users[2]      = (object) array( 'roles' => array( 'artist' ) );
		MockStorage::$current_roles = array();

		$_GET['ap_preview_user']      = '2';
			$_GET['ap_preview_nonce'] = 'test-nonce';
		$layout                       = DashboardController::get_user_dashboard_layout( 1 );
		$this->assertSame(
			array(
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
			),
			$layout
		);
		unset( $_GET['ap_preview_user'], $_GET['ap_preview_nonce'] );
	}

	public function test_preview_user_does_not_persist_layout(): void {
		MockStorage::$users[1]                            = (object) array( 'roles' => array( 'administrator' ) );
		MockStorage::$users[2]                            = (object) array( 'roles' => array( 'artist' ) );
		MockStorage::$user_meta[1]['ap_dashboard_layout'] = array(
			array(
				'id'      => 'widget_alpha',
				'visible' => true,
			),
		);

		$_GET['ap_preview_user']          = '2';
				$_GET['ap_preview_nonce'] = 'test-nonce';
		DashboardController::get_user_dashboard_layout( 1 );
		unset( $_GET['ap_preview_user'], $_GET['ap_preview_nonce'] );

		$this->assertSame(
			array(
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
			),
			MockStorage::$user_meta[1]['ap_dashboard_layout']
		);
	}
}
