<?php
namespace ArtPulse\Admin\Tests;

require_once __DIR__ . '/../TestStubs.php';
use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Tests\Stubs\MockStorage;
use ArtPulse\Widgets\Placeholder\ApPlaceholderWidget;

class UserLayoutManagerRoleLayoutTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		MockStorage::$options       = array();
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

                  DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', [self::class, 'returnEmpty'], array( 'roles' => array( 'member' ) ) );
                  DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', [self::class, 'returnEmpty'], array( 'roles' => array( 'artist' ) ) );
                  WidgetRegistry::register( 'widget_alpha', [self::class, 'renderSection'] );
                  WidgetRegistry::register( 'widget_beta', [self::class, 'renderSection'] );
        }

        public static function returnEmpty(): string { return ''; }
        public static function renderSection(): string { return '<section></section>'; }

	public function test_role_layout_registers_placeholders_for_missing_widgets(): void {
		MockStorage::$options['ap_dashboard_widget_config'] = array(
			'member' => array(
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
				array(
					'id'      => 'ghost',
					'visible' => true,
				),
			),
		);

		$result = UserLayoutManager::get_role_layout( 'member' );
		$this->assertSame(
			array(
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
				array(
					'id'      => 'ghost',
					'visible' => true,
				),
			),
			$result['layout']
		);
               $this->assertSame( array( 'ghost' ), $result['logs'] );
		$def = DashboardWidgetRegistry::getById( 'ghost' );
		$this->assertSame( ApPlaceholderWidget::class, $def['class'] );
	}

	public function test_role_layout_falls_back_to_default_widgets(): void {
		$result = UserLayoutManager::get_role_layout( 'artist' );
		$this->assertSame(
			array(
				array(
					'id'      => 'widget_beta',
					'visible' => true,
				),
			),
			$result['layout']
		);
	}
}
