<?php
namespace ArtPulse\Dashboard;

function error_log( $msg ) {
	\WidgetGuardTest::$logs[] = $msg; }

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Dashboard\WidgetGuard;

/**

 * @group WIDGETS

 */

class WidgetGuardTest extends WP_UnitTestCase {

	public static array $logs = array();
	private function reset_registry(): void {
		$ref  = new ReflectionClass( DashboardWidgetRegistry::class );
		$prop = $ref->getProperty( 'widgets' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
		$prop = $ref->getProperty( 'id_map' );
		$prop->setAccessible( true );
		$prop->setValue( null, null );
	}

	public function set_up() {
		parent::set_up();
		$this->reset_registry();
		self::$logs = array();
		if ( ! get_role( 'member' ) ) {
			add_role( 'member', 'Member' );
		}
		$uid = self::factory()->user->create( array( 'role' => 'member' ) );
		wp_set_current_user( $uid );
	}

	public function test_invalid_callback_gets_placeholder(): void {
		DashboardWidgetRegistry::register( 'bad', 'Bad', 'alert', 'desc', 'missing_cb' );
		WidgetGuard::validate_and_patch( 'member' );
		$cb = DashboardWidgetRegistry::get_widget_callback( 'bad' );
		ob_start();
		call_user_func( $cb, 1 );
		$html = ob_get_clean();
		$this->assertStringContainsString( 'Widget Unavailable', $html );
	}

	public function test_valid_widget_unchanged(): void {
		DashboardWidgetRegistry::register(
			'good',
			'Good',
			'info',
			'desc',
			static function () {
				echo 'OK';
			}
		);
		WidgetGuard::validate_and_patch( 'member' );
		$cb = DashboardWidgetRegistry::get_widget_callback( 'good' );
		ob_start();
		call_user_func( $cb, 1 );
		$html = ob_get_clean();
		$this->assertSame( 'OK', $html );
	}

	public function test_feature_flag_disabled_does_nothing(): void {
		add_filter( 'ap_widget_placeholder_enabled', '__return_false' );
		DashboardWidgetRegistry::register( 'bad2', 'Bad2', 'info', 'desc', 'missing_cb' );
		WidgetGuard::validate_and_patch( 'member' );
		$cb = DashboardWidgetRegistry::get_widget_callback( 'bad2' );
		ob_start();
		call_user_func( $cb );
		$html = ob_get_clean();
		$this->assertStringContainsString( 'Widget callback is missing', $html );
		remove_all_filters( 'ap_widget_placeholder_enabled' );
	}

	public function test_debug_summary_logged_when_patched(): void {
		DashboardWidgetRegistry::register( 'one', 'One', 'info', 'desc', 'missing_cb' );
		DashboardWidgetRegistry::register( 'two', 'Two', 'info', 'desc', 'missing_cb' );
		WidgetGuard::validate_and_patch();
		$this->assertNotEmpty( self::$logs );
		$summary = end( self::$logs );
		$this->assertStringContainsString( 'Patched widgets: one, two', $summary );
	}

	public function test_debug_filter_modifies_payload(): void {
		add_filter(
			'ap_widget_placeholder_debug_payload',
			function ( $args, $id ) {
				$args['debug'] = $id;
				return $args;
			},
			10,
			2
		);
               DashboardWidgetRegistry::register( 'widget_foo', 'Foo', 'info', 'desc', 'missing_cb' );
               WidgetGuard::validate_and_patch();
               $cb = DashboardWidgetRegistry::get_widget_callback( 'widget_foo' );
		ob_start();
		call_user_func( $cb );
		$html = ob_get_clean();
               $this->assertStringContainsString( 'widget_foo', $html );
		remove_all_filters( 'ap_widget_placeholder_debug_payload' );
	}

	public function test_register_stub_widget_registers_placeholder(): void {
		WidgetGuard::register_stub_widget( 'stub_widget', array( 'title' => 'Stub Widget' ) );
		$cb = DashboardWidgetRegistry::get_widget_callback( 'stub_widget' );
		ob_start();
		call_user_func( $cb, 1 );
		$html = ob_get_clean();
		$this->assertStringContainsString( 'Widget Unavailable', $html );
	}
}
