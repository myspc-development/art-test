<?php
namespace ArtPulse\Admin;

if ( ! function_exists( __NAMESPACE__ . '\\check_ajax_referer' ) ) {
	function check_ajax_referer( $action, $name ) {}
}
if ( ! function_exists( __NAMESPACE__ . '\\current_user_can' ) ) {
	function current_user_can( $cap ) {
		return \ArtPulse\Admin\Tests\DashboardWidgetConfigAjaxTest::$can;
	}
}
if ( ! function_exists( __NAMESPACE__ . '\\sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return preg_replace( '/[^a-z0-9_]/i', '', strtolower( $key ) ); }
}
if ( ! function_exists( __NAMESPACE__ . '\\update_option' ) ) {
	function update_option( $key, $value ) {
		\ArtPulse\Admin\Tests\DashboardWidgetConfigAjaxTest::$options[ $key ] = $value; }
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_send_json_success' ) ) {
	function wp_send_json_success( $data = null ) {
		\ArtPulse\Admin\Tests\DashboardWidgetConfigAjaxTest::$json_success = $data ?? true; }
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_send_json_error' ) ) {
	function wp_send_json_error( $data ) {
		\ArtPulse\Admin\Tests\DashboardWidgetConfigAjaxTest::$json_error = $data; }
}
if ( ! function_exists( __NAMESPACE__ . '\\add_action' ) ) {
	function add_action( $hook, $callback, $priority = 10, $args = 1 ) {
		\ArtPulse\Admin\Tests\DashboardWidgetConfigAjaxTest::$hooks[ $hook ][] = $callback; }
}
if ( ! function_exists( __NAMESPACE__ . '\\do_action' ) ) {
	function do_action( $hook ) {
		foreach ( \ArtPulse\Admin\Tests\DashboardWidgetConfigAjaxTest::$hooks[ $hook ] ?? array() as $cb ) {
			call_user_func( $cb ); } }
}

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/dashboard-widgets.php';

/**

 * @group ADMIN

 */

class DashboardWidgetConfigAjaxTest extends TestCase {

	public static bool $can      = true;
	public static array $options = array();
	public static array $hooks   = array();
	public static $json_success  = null;
	public static $json_error    = null;

	protected function setUp(): void {
		self::$can          = true;
		self::$options      = array();
		self::$hooks        = array();
		self::$json_success = null;
		self::$json_error   = null;
		$_POST              = array();
	}

	protected function tearDown(): void {
		$_POST              = array();
		self::$options      = array();
		self::$hooks        = array();
		self::$json_success = null;
		self::$json_error   = null;
		parent::tearDown();
	}

	public function test_save_dashboard_widget_config_saves_arrays_per_role(): void {
		$_POST['nonce']  = 'n';
		$_POST['config'] = array(
			'administrator'  => array( 'widget_membership', 'upgrade' ),
			'editor<script>' => array( 'content', 'favorites', 'bad<>' ),
		);

		do_action( 'wp_ajax_ap_save_dashboard_widget_config' );

		$expected = array(
			'administrator' => array( 'widget_membership', 'upgrade' ),
			'editorscript'  => array( 'content', 'favorites', 'bad' ),
		);
		$this->assertSame( $expected, self::$options['ap_dashboard_widget_config'] ?? null );
		$this->assertSame( array( 'saved' => true ), self::$json_success );
		$this->assertNull( self::$json_error );
	}

	public function test_permission_denied_returns_error(): void {
		self::$can       = false;
		$_POST['nonce']  = 'n';
		$_POST['config'] = array( 'member' => array( 'events' ) );

		do_action( 'wp_ajax_ap_save_dashboard_widget_config' );

		$this->assertArrayNotHasKey( 'ap_dashboard_widget_config', self::$options );
		$this->assertNull( self::$json_success );
		$this->assertSame( array( 'message' => 'Permission denied' ), self::$json_error );
	}
}
