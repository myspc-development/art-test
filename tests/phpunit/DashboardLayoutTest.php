<?php
namespace {
        require_once __DIR__ . '/../TestStubs.php';
        if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
                define( 'ARTPULSE_PLUGIN_FILE', dirname( __DIR__, 2 ) . '/artpulse.php' );
        }
        if ( ! defined( 'ABSPATH' ) ) {
                define( 'ABSPATH', __DIR__ . '/' );
        }
}

namespace ArtPulse\Core {
	/** Simple role object for capability checks. */
	class RoleStub {
		private array $caps;
		public function __construct( array $caps ) {
			$this->caps = $caps; }
		public function has_cap( $cap ): bool {
			return in_array( $cap, $this->caps, true ); }
	}
	function get_role( string $role ) {
		return new RoleStub( array() ); // roles have no capabilities in tests
	}
	function do_action( $tag, ...$args ) {
		\ArtPulse\Core\Tests\DashboardLayoutTest::$actions[] = array( $tag, $args );
	}
}

namespace ArtPulse\Core\Tests {
        use PHPUnit\Framework\TestCase;
        use Brain\Monkey;
        use Brain\Monkey\Functions;
        use ArtPulse\Core\DashboardController;
        use ArtPulse\Core\DashboardWidgetRegistry;
        use ArtPulse\Tests\Stubs\MockStorage;

        class DashboardLayoutTest extends TestCase {
                public static array $actions = array();
                public static function noop(): void {}

		protected function setUp(): void {
			parent::setUp();
                        Monkey\setUp();
                        MockStorage::$user_meta     = array();
			MockStorage::$options       = array();
			MockStorage::$users         = array();
			MockStorage::$current_roles = array();
			self::$actions              = array();

			$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
			$prop = $ref->getProperty( 'widgets' );
			$prop->setAccessible( true );
			$prop->setValue( null, array() );

			$ref2  = new \ReflectionClass( DashboardController::class );
			$prop2 = $ref2->getProperty( 'role_widgets' );
			$prop2->setAccessible( true );
			$prop2->setValue( null, array() );
		}

		protected function tearDown(): void {
			Monkey\tearDown();
			parent::tearDown();
		}

		public function test_default_presets_loaded_per_role(): void {
			DashboardWidgetRegistry::register( 'widget_news', 'News', '', '', [self::class, 'noop'], array( 'roles' => array( 'member' ) ) );
			DashboardWidgetRegistry::register( 'artist_inbox_preview', 'Inbox Preview', '', '', [self::class, 'noop'], array( 'roles' => array( 'artist' ) ) );

			$presets = DashboardController::get_default_presets();
			$this->assertSame( 'member', $presets['member_default']['role'] );
			$this->assertSame(
				array(
					array(
						'id'      => 'widget_news',
						'visible' => true,
					),
				),
				$presets['member_default']['layout']
			);
			$this->assertSame( 'artist', $presets['artist_default']['role'] );
			$this->assertSame(
				array(
					array(
						'id'      => 'artist_inbox_preview',
						'visible' => true,
					),
				),
				$presets['artist_default']['layout']
			);
		}

		public function test_fallback_layout_and_filtering(): void {
                        DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', [self::class, 'noop'], array( 'roles' => array( 'member' ), 'capability' => 'edit_posts' ) );
                        DashboardWidgetRegistry::register( 'widget_gamma', 'Gamma', '', '', [self::class, 'noop'], array( 'roles' => array( 'artist' ) ) );

                        $ref2  = new \ReflectionClass( DashboardController::class );
                        $prop2 = $ref2->getProperty( 'role_widgets' );
                        $prop2->setAccessible( true );
                        $prop2->setValue(
                                null,
                                array(
                                        'member' => array( 'widget_alpha', 'widget_gamma' ),
                                )
                        );

			MockStorage::$users[1] = (object) array( 'roles' => array( 'member' ) );
			$layout                = DashboardController::get_user_dashboard_layout( 1 );
			$this->assertSame(
				array(
					array(
						'id'      => 'empty_dashboard',
						'visible' => true,
					),
				),
				$layout
			);
		}

		public function test_saved_layout_overrides_fallback(): void {
			DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', [self::class, 'noop'], array( 'roles' => array( 'member' ) ) );
			DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', [self::class, 'noop'], array( 'roles' => array( 'member' ) ) );

			$ref2  = new \ReflectionClass( DashboardController::class );
			$prop2 = $ref2->getProperty( 'role_widgets' );
			$prop2->setAccessible( true );
			$prop2->setValue( null, array( 'member' => array( 'widget_beta' ) ) );

			MockStorage::$users[2]                            = (object) array( 'roles' => array( 'member' ) );
			MockStorage::$user_meta[2]['ap_dashboard_layout'] = array(
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
			);
			$layout = DashboardController::get_user_dashboard_layout( 2 );
			$this->assertSame(
				array(
					array(
						'id'      => 'widget_alpha',
						'visible' => true,
					),
				),
				$layout
			);
		}

		public function test_emits_action_when_layout_empty(): void {
			DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', [self::class, 'noop'], array( 'roles' => array( 'artist' ) ) );
			$ref2  = new \ReflectionClass( DashboardController::class );
			$prop2 = $ref2->getProperty( 'role_widgets' );
			$prop2->setAccessible( true );
			$prop2->setValue( null, array( 'member' => array( 'widget_beta' ) ) );
			MockStorage::$users[3] = (object) array( 'roles' => array( 'member' ) );
			$layout                = DashboardController::get_user_dashboard_layout( 3 );
			$this->assertSame(
				array(
					array(
						'id'      => 'empty_dashboard',
						'visible' => true,
					),
				),
				$layout
			);
			$this->assertSame( array( array( 'ap_dashboard_empty_layout', array( 3, 'member' ) ) ), self::$actions );
		}

		public function test_preview_role_renders_layout(): void {
			DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', [self::class, 'noop'], array( 'roles' => array( 'member' ) ) );
			DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', [self::class, 'noop'], array( 'roles' => array( 'artist' ) ) );
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
			MockStorage::$users[4]      = (object) array( 'roles' => array( 'administrator' ) );
			MockStorage::$current_roles = array( 'manage_options' );
			$_GET['ap_preview_role']    = 'artist';
                        $_GET['ap_preview_nonce']   = 'nonce_ap_preview';
			$layout                     = DashboardController::get_user_dashboard_layout( 4 );
			unset( $_GET['ap_preview_role'], $_GET['ap_preview_nonce'] );
			$this->assertSame(
				array(
					array(
						'id'      => 'widget_beta',
						'visible' => true,
					),
				),
				$layout
			);
		}

		public function test_preview_role_does_not_persist_layout(): void {
			DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', [self::class, 'noop'], array( 'roles' => array( 'member' ) ) );
			DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', [self::class, 'noop'], array( 'roles' => array( 'artist' ) ) );
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
			MockStorage::$users[6]                            = (object) array( 'roles' => array( 'administrator' ) );
			MockStorage::$current_roles                       = array( 'manage_options' );
			MockStorage::$user_meta[6]['ap_dashboard_layout'] = array(
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
			);
			$_GET['ap_preview_role']                          = 'artist';
                        $_GET['ap_preview_nonce']                         = 'nonce_ap_preview';
			DashboardController::get_user_dashboard_layout( 6 );
			unset( $_GET['ap_preview_role'], $_GET['ap_preview_nonce'] );
			$this->assertSame(
				array(
					array(
						'id'      => 'widget_alpha',
						'visible' => true,
					),
				),
				MockStorage::$user_meta[6]['ap_dashboard_layout']
			);
		}

		public function test_filter_accessible_layout_excludes_by_capability_and_role(): void {
			DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', [self::class, 'noop'], array( 'roles' => array( 'member' ) ) );
			DashboardWidgetRegistry::register(
				'widget_beta',
				'Beta',
				'',
				'',
				[self::class, 'noop'],
				array(
					'roles'      => array( 'member' ),
					'capability' => 'edit_posts',
				)
			);
			DashboardWidgetRegistry::register( 'widget_gamma', 'Gamma', '', '', [self::class, 'noop'], array( 'roles' => array( 'artist' ) ) );
			$layout = array(
				array(
					'id'      => 'widget_alpha',
					'visible' => true,
				),
				array(
					'id'      => 'widget_beta',
					'visible' => true,
				),
				array(
					'id'      => 'widget_gamma',
					'visible' => true,
				),
				array(
					'id'      => 'missing',
					'visible' => true,
				),
			);
			$ref    = new \ReflectionClass( DashboardController::class );
			$m      = $ref->getMethod( 'filter_accessible_layout' );
			$m->setAccessible( true );
			$filtered = $m->invoke( null, $layout, 'member' );
			$this->assertSame(
				array(
					array(
						'id'      => 'widget_alpha',
						'visible' => true,
					),
				),
				$filtered
			);
		}

		public function test_filter_accessible_layout_respects_entry_capability(): void {
			DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', [self::class, 'noop'], array( 'roles' => array( 'member' ) ) );
			DashboardWidgetRegistry::register( 'widget_beta', 'Beta', '', '', [self::class, 'noop'], array( 'roles' => array( 'member' ) ) );
			$layout = array(
				array(
					'id'      => 'widget_alpha',
					'visible' => false,
				),
				array(
					'id'         => 'widget_beta',
					'visible'    => true,
					'capability' => 'edit_posts',
				),
			);
			$ref    = new \ReflectionClass( DashboardController::class );
			$m      = $ref->getMethod( 'filter_accessible_layout' );
			$m->setAccessible( true );
			$filtered = $m->invoke( null, $layout, 'member' );
			$this->assertSame(
				array(
					array(
						'id'      => 'widget_alpha',
						'visible' => false,
					),
				),
				$filtered
			);
		}

		public function test_register_widgets_invoked_and_layout_not_empty(): void {
			DashboardWidgetRegistry::register( 'widget_alpha', 'Alpha', '', '', [self::class, 'noop'], array( 'roles' => array( 'member' ) ) );

			$refCtrl     = new \ReflectionClass( DashboardController::class );
			$propWidgets = $refCtrl->getProperty( 'role_widgets' );
			$propWidgets->setAccessible( true );
			$propWidgets->setValue( null, array( 'member' => array( 'widget_alpha' ) ) );

			MockStorage::$users[5] = (object) array( 'roles' => array( 'member' ) );
			$layout                = DashboardController::get_user_dashboard_layout( 5 );

			$this->assertSame(
				array(
					array(
						'id'      => 'widget_alpha',
						'visible' => true,
					),
				),
				$layout
			);
		}
	}
}
