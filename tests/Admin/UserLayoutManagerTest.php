<?php
namespace {
	if ( ! function_exists( '__return_null' ) ) {
		function __return_null() {
			return null; }
	}
}

namespace ArtPulse\Admin {
	// WordPress function stubs
	if ( ! function_exists( __NAMESPACE__ . '\\current_user_can' ) ) {
		function current_user_can( $cap ) {
			return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$can;
		}
	}
	if ( ! function_exists( __NAMESPACE__ . '\\wp_die' ) ) {
		function wp_die( $msg = '' ) {
			\ArtPulse\Admin\Tests\UserLayoutManagerTest::$died = $msg ?: true; }
	}
	if ( ! function_exists( __NAMESPACE__ . '\\check_admin_referer' ) ) {
		function check_admin_referer( $action ) {}
	}
	if ( ! function_exists( __NAMESPACE__ . '\\wp_safe_redirect' ) ) {
		function wp_safe_redirect( $url ) {
			\ArtPulse\Admin\Tests\UserLayoutManagerTest::$redirect = $url;
			throw new \Exception( 'redirect' ); }
	}
	if ( ! function_exists( __NAMESPACE__ . '\\wp_get_referer' ) ) {
		function wp_get_referer() {
			return '/ref'; }
	}
	if ( ! function_exists( __NAMESPACE__ . '\\add_query_arg' ) ) {
		function add_query_arg( $key, $value, $base ) {
			return $base . ( str_contains( $base, '?' ) ? '&' : '?' ) . $key . '=' . $value; }
	}
	if ( ! function_exists( __NAMESPACE__ . '\\update_option' ) ) {
		function update_option( $key, $value ) {
			\ArtPulse\Admin\Tests\UserLayoutManagerTest::$options[ $key ] = $value; }
	}
	if ( ! function_exists( __NAMESPACE__ . '\\get_option' ) ) {
		function get_option( $key, $default = array() ) {
			return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$options[ $key ] ?? $default; }
	}
	if ( ! function_exists( __NAMESPACE__ . '\\file_get_contents' ) ) {
		function file_get_contents( $path ) {
			return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$file_contents; }
	}
	if ( ! function_exists( __NAMESPACE__ . '\\get_user_meta' ) ) {
		function get_user_meta( $uid, $key, $single = false ) {
			return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$meta[ $uid ][ $key ] ?? ''; }
	}
	if ( ! function_exists( __NAMESPACE__ . '\\update_user_meta' ) ) {
		function update_user_meta( $uid, $key, $value ) {
			\ArtPulse\Admin\Tests\UserLayoutManagerTest::$meta[ $uid ][ $key ] = $value; }
	}
	if ( ! function_exists( __NAMESPACE__ . '\\get_userdata' ) ) {
		function get_userdata( $uid ) {
			return \ArtPulse\Admin\Tests\UserLayoutManagerTest::$users[ $uid ] ?? null; }
	}
	if ( ! function_exists( __NAMESPACE__ . '\\sanitize_key' ) ) {
		function sanitize_key( $key ) {
			return preg_replace( '/[^a-z0-9_]/i', '', strtolower( $key ) ); }
	}
	if ( ! function_exists( __NAMESPACE__ . '\\wp_json_encode' ) ) {
		function wp_json_encode( $data, $flags = 0 ) {
			return json_encode( $data, $flags ); }
	}
	if ( ! function_exists( __NAMESPACE__ . '\\error_log' ) ) {
		function error_log( $msg ) {
			\ArtPulse\Admin\Tests\UserLayoutManagerTest::$logs[] = $msg; }
	}
	if ( ! function_exists( __NAMESPACE__ . '\\header' ) ) {
		function header( $string, $replace = true, $code = 0 ) {
			\ArtPulse\Admin\Tests\UserLayoutManagerTest::$headers[] = $string; }
	}
}

namespace ArtPulse\Admin\Tests {

	use PHPUnit\Framework\TestCase;
	use ArtPulse\Admin\UserLayoutManager;
	use ArtPulse\Core\DashboardWidgetRegistry;
	use Brain\Monkey;
	use Brain\Monkey\Functions;

	/**

	 * @group admin

	 */

	class UserLayoutManagerTest extends TestCase {

		public static bool $can             = true;
		public static $died                 = null;
		public static string $redirect      = '';
		public static array $meta           = array();
		public static array $users          = array();
		public static array $options        = array();
		public static string $file_contents = '';
		public static array $headers        = array();
		public static array $logs           = array();

		protected function setUp(): void {
			parent::setUp();
			Monkey\setUp();
			Functions\when( 'admin_url' )->alias( fn( $path = '' ) => $path );

			self::$can           = true;
			self::$died          = null;
			self::$redirect      = '';
			self::$meta          = array();
			self::$users         = array();
			self::$options       = array();
			self::$file_contents = '';
			self::$headers       = array();
			self::$logs          = array();
			$_FILES              = array();
		}

		protected function tearDown(): void {
			$_FILES              = array();
			self::$meta          = array();
			self::$users         = array();
			self::$options       = array();
			self::$file_contents = '';
			self::$headers       = array();
			self::$logs          = array();
			$ref                 = new \ReflectionClass( DashboardWidgetRegistry::class );
			$prop                = $ref->getProperty( 'widgets' );
			$prop->setAccessible( true );
			$prop->setValue( null, array() );
			Monkey\tearDown();
			parent::tearDown();
		}

		public function test_user_layout_is_stored_and_retrieved(): void {
                       DashboardWidgetRegistry::register( 'widget_foo', 'Foo', '', '', '__return_null' );
			DashboardWidgetRegistry::register( 'bar', 'Bar', '', '', '__return_null' );

			UserLayoutManager::save_layout(
				1,
				array(
                                       array( 'id' => 'bar' ),
                                       array( 'id' => 'widget_foo' ),
                                       array( 'id' => 'widget_foo' ),
					'invalid',
				)
			);

			$expected_saved = array(
                               array(
                                       'id'      => 'bar',
                                       'visible' => true,
                               ),
                               array(
                                       'id'      => 'widget_foo',
                                       'visible' => true,
                               ),
			);

			$this->assertSame( $expected_saved, self::$meta[1]['ap_dashboard_layout'] );

			$layout = UserLayoutManager::get_layout( 1 );
			$this->assertSame( $expected_saved, $layout );
		}

		public function test_get_layout_falls_back_to_role_then_registry(): void {
			DashboardWidgetRegistry::register( 'a', 'A', '', '', '__return_null' );
			DashboardWidgetRegistry::register( 'b', 'B', '', '', '__return_null' );

			self::$users[2]                              = (object) array( 'roles' => array( 'subscriber' ) );
			self::$options['ap_dashboard_widget_config'] = array( 'subscriber' => array( array( 'id' => 'b' ) ) );

			$layout = UserLayoutManager::get_layout( 2 );
			$this->assertSame(
				array(
					array(
						'id'      => 'b',
						'visible' => true,
					),
				),
				$layout
			);

			self::$options['ap_dashboard_widget_config'] = array();
			$layout                                      = UserLayoutManager::get_layout( 2 );
			$expected                                    = array_map(
				fn( $id ) => array(
					'id'      => $id,
					'visible' => true,
				),
				\ArtPulse\Core\DashboardController::get_widgets_for_role( 'subscriber' )
			);
			$this->assertSame( $expected, $layout );

			self::$meta[2]['ap_dashboard_layout'] = array( 'a' );
			$layout                               = UserLayoutManager::get_layout( 2 );
			$this->assertSame(
				array(
					array(
						'id'      => 'a',
						'visible' => true,
					),
				),
				$layout
			);
		}

		public function test_save_role_layout_sanitizes_and_updates_option(): void {
			DashboardWidgetRegistry::register( 'sr_one', 'One', '', '', '__return_null' );
			DashboardWidgetRegistry::register( 'sr_two', 'Two', '', '', '__return_null' );

			UserLayoutManager::save_role_layout(
				'editor<script>',
				array(
					array( 'id' => 'sr_two' ),
					array( 'id' => 'sr_one' ),
					array( 'id' => 'sr_one' ),
					'invalid',
				)
			);

			$expected = array(
				'editorscript' => array(
					array(
						'id'      => 'sr_two',
						'visible' => true,
					),
					array(
						'id'      => 'sr_one',
						'visible' => true,
					),
				),
			);

			$this->assertSame( $expected, self::$options['ap_dashboard_widget_config'] ?? null );
		}

                public function test_get_role_layout_returns_saved_or_fallback(): void {
			DashboardWidgetRegistry::register( 'gr_one', 'One', '', '', '__return_null' );
			DashboardWidgetRegistry::register( 'gr_two', 'Two', '', '', '__return_null' );

			self::$options['ap_dashboard_widget_config'] = array(
				'subscriber' => array(
					array( 'id' => 'gr_two' ),
					array( 'id' => 'GR_ONE' ),
				),
			);

			$result = UserLayoutManager::get_role_layout( 'subscriber' );
			$layout = $result['layout'];
			$this->assertSame(
				array(
					array(
						'id'      => 'gr_two',
						'visible' => true,
					),
					array(
						'id'      => 'gr_one',
						'visible' => true,
					),
				),
				$layout
			);
			$this->assertSame( array(), $result['logs'] );

			self::$options['ap_dashboard_widget_config'] = array();
			$expected                                    = array_map(
				fn( $id ) => array(
					'id'      => $id,
					'visible' => true,
				),
				\ArtPulse\Core\DashboardController::get_widgets_for_role( 'subscriber' )
			);
                        $this->assertSame( $expected, UserLayoutManager::get_role_layout( 'subscriber' )['layout'] );
                }

               public function test_administrator_role_returns_empty_layout_without_logs(): void {
                       $result = UserLayoutManager::get_role_layout( 'administrator' );
                       $this->assertSame( array(), $result['layout'] );
                       $this->assertSame( array(), $result['logs'] );
                       $this->assertSame( array(), self::$logs );
               }

               public function test_get_role_layout_logs_and_stubs_invalid_widget(): void {
			DashboardWidgetRegistry::register( 'good', 'Good', '', '', '__return_null' );

			self::$options['ap_dashboard_widget_config'] = array(
				'subscriber' => array(
					array( 'id' => 'good' ),
					array( 'id' => 'missing' ),
				),
			);

			$result = UserLayoutManager::get_role_layout( 'subscriber' );
			$this->assertSame( array( 'missing' ), $result['logs'] );
			$this->assertSame( 'missing', $result['layout'][1]['id'] );
			$stub = DashboardWidgetRegistry::getById( 'missing' );
			$this->assertNotNull( $stub );
			$this->assertIsCallable( $stub['callback'] );
		}

		public function test_get_role_layout_logs_invalid_widgets(): void {
			DashboardWidgetRegistry::register( 'good', 'Good', '', '', '__return_null' );
			DashboardWidgetRegistry::register( 'broken', 'Broken', '', '', '__return_null' );

			// Make the "broken" widget callback uncallable.
			$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
			$prop = $ref->getProperty( 'widgets' );
			$prop->setAccessible( true );
			$widgets                       = $prop->getValue();
			$widgets['broken']['callback'] = 'not_callable';
			$prop->setValue( null, $widgets );

			self::$options['ap_dashboard_widget_config'] = array(
				'subscriber' => array(
					array( 'id' => 'good' ),
					array( 'id' => 'missing' ),
					array( 'id' => 'broken' ),
				),
			);

			$result = UserLayoutManager::get_role_layout( 'subscriber' );
			$this->assertSame(
				array(
					array(
						'id'      => 'good',
						'visible' => true,
					),
				),
				$result['layout']
			);
			$this->assertSame( array( 'missing', 'broken' ), $result['logs'] );

			$this->assertNotEmpty( self::$logs );
			$log = self::$logs[0];
			$this->assertStringContainsString( 'subscriber', $log );
			$this->assertStringContainsString( 'missing', $log );
			$this->assertStringContainsString( 'broken', $log );
		}

		public function test_export_layout_returns_pretty_json(): void {
                       DashboardWidgetRegistry::register( 'widget_foo', 'Foo', '', '', '__return_null' );
			UserLayoutManager::save_role_layout( 'subscriber', array( array( 'id' => 'widget_foo' ) ) );

			$expected = json_encode(
				array(
					array(
						'id'      => 'widget_foo',
						'visible' => true,
					),
				),
				JSON_PRETTY_PRINT
			);
			$this->assertSame( $expected, UserLayoutManager::export_layout( 'subscriber' ) );
		}

		public function test_import_layout_decodes_and_saves(): void {
                       DashboardWidgetRegistry::register( 'widget_foo', 'Foo', '', '', '__return_null' );
			DashboardWidgetRegistry::register( 'bar', 'Bar', '', '', '__return_null' );

			$json = json_encode(
				array(
					array( 'id' => 'bar' ),
					array( 'id' => 'widget_foo' ),
				)
			);
			UserLayoutManager::import_layout( 'subscriber', $json );

			$this->assertSame(
				array(
					array(
						'id'      => 'bar',
						'visible' => true,
					),
					array(
						'id'      => 'widget_foo',
						'visible' => true,
					),
				),
				self::$options['ap_dashboard_widget_config']['subscriber']
			);
		}

		public function test_reset_layout_for_role_removes_config(): void {
			self::$options['ap_dashboard_widget_config'] = array(
				'subscriber' => array( array( 'id' => 'widget_foo' ) ),
			);

			UserLayoutManager::reset_layout_for_role( 'subscriber' );

			$this->assertArrayNotHasKey( 'subscriber', self::$options['ap_dashboard_widget_config'] );
		}

		public function test_default_role_layout_for_unknown_role_is_empty(): void {
			$ref  = new \ReflectionClass( DashboardWidgetRegistry::class );
			$prop = $ref->getProperty( 'widgets' );
			$prop->setAccessible( true );
			$prop->setValue( null, array() );

			DashboardWidgetRegistry::register( 'artpulse_dashboard_widget', 'Manager', '', '', '__return_null' );
                       DashboardWidgetRegistry::register( 'widget_foo', 'Foo', '', '', '__return_null' );

			$layout = UserLayoutManager::get_role_layout( 'subscriber' )['layout'];
			$this->assertSame( array(), $layout );
		}
	}
}
