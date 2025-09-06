<?php
namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\DashboardFeedbackManager;

if ( ! function_exists( __NAMESPACE__ . '\check_ajax_referer' ) ) {
	function check_ajax_referer( $action, $name ) {}
}
if ( ! function_exists( __NAMESPACE__ . '\sanitize_textarea_field' ) ) {
	function sanitize_textarea_field( $v ) {
		return is_string( $v ) ? trim( $v ) : $v; }
}
if ( ! function_exists( __NAMESPACE__ . '\get_current_user_id' ) ) {
	function get_current_user_id() {
		return 5; }
}
if ( ! function_exists( __NAMESPACE__ . '\DashboardController_get_role' ) ) {
	function DashboardController_get_role( $uid ) {
		return 'member'; }
}
if ( ! function_exists( __NAMESPACE__ . '\wp_send_json_success' ) ) {
	function wp_send_json_success( $d = null ) {
		DashboardFeedbackManagerTest::$success = $d; }
}
if ( ! function_exists( __NAMESPACE__ . '\wp_send_json_error' ) ) {
	function wp_send_json_error( $d ) {
		DashboardFeedbackManagerTest::$error = $d; }
}
if ( ! function_exists( __NAMESPACE__ . '\is_email' ) ) {
	function is_email( $e ) {
		return true; }
}
if ( ! function_exists( __NAMESPACE__ . '\get_option' ) ) {
	function get_option( $k ) {
		return 'admin@example.com'; }
}
if ( ! function_exists( __NAMESPACE__ . '\wp_mail' ) ) {
	function wp_mail( $to, $sub, $body ) {
		DashboardFeedbackManagerTest::$mail = array( $to, $sub, $body ); }
}

/**

 * @group CORE
 */

class DBStub {
	public $prefix      = 'wp_';
	public $insert_args = array();
	public function insert( $t, $d ) {
		$this->insert_args[] = array(
			'table' => $t,
			'data'  => $d,
		);
	} public function get_charset_collate() {
		return '';
	} public function get_var( $q ) {
		return 'wp_dashboard_feedback'; }
}

class DashboardFeedbackManagerTest extends TestCase {

	public static $success = null;
	public static $error   = null;
	public static $mail    = null;
	private $old_wpdb;

	protected function setUp(): void {
		global $wpdb;
		$this->old_wpdb = $wpdb ?? null;
		$wpdb           = new DBStub();
		self::$success  = self::$error = self::$mail = null;
	}

	protected function tearDown(): void {
		global $wpdb;
		$wpdb  = $this->old_wpdb;
		$_POST = array();
		parent::tearDown();
	}

	public function test_handle_inserts_feedback_and_emails_admin(): void {
		$_POST = array(
			'nonce'   => 'n',
			'message' => 'Great dashboard',
		);
		DashboardFeedbackManager::handle();
		global $wpdb;
		$this->assertNotEmpty( $wpdb->insert_args );
		$row = $wpdb->insert_args[0];
		$this->assertSame( 'wp_dashboard_feedback', $row['table'] );
		$this->assertSame( 'Great dashboard', $row['data']['message'] );
		$this->assertSame( 5, $row['data']['user_id'] );
		$this->assertSame( 'member', $row['data']['role'] );
		$this->assertNotNull( self::$mail );
		$this->assertNotNull( self::$success );
	}
}
