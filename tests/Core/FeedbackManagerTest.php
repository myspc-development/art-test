<?php
namespace ArtPulse\Core;

// --- WordPress function stubs ---
if ( ! function_exists( __NAMESPACE__ . '\check_ajax_referer' ) ) {
	function check_ajax_referer( $action, $name ) {}
}
if ( ! function_exists( __NAMESPACE__ . '\sanitize_text_field' ) ) {
	function sanitize_text_field( $value ) {
		return is_string( $value ) ? trim( $value ) : $value; }
}
if ( ! function_exists( __NAMESPACE__ . '\sanitize_textarea_field' ) ) {
	function sanitize_textarea_field( $value ) {
		return is_string( $value ) ? trim( $value ) : $value; }
}
if ( ! function_exists( __NAMESPACE__ . '\sanitize_email' ) ) {
	function sanitize_email( $value ) {
		return $value; }
}
if ( ! function_exists( __NAMESPACE__ . '\get_current_user_id' ) ) {
	function get_current_user_id() {
		return \ArtPulse\Core\Tests\FeedbackManagerTest::$current_user_id; }
}
if ( ! function_exists( __NAMESPACE__ . '\current_time' ) ) {
	function current_time( $type = 'mysql' ) {
		return \ArtPulse\Core\Tests\FeedbackManagerTest::$current_time; }
}
if ( ! function_exists( __NAMESPACE__ . '\wp_send_json_success' ) ) {
	function wp_send_json_success( $data = null ) {
		\ArtPulse\Core\Tests\FeedbackManagerTest::$json_success = $data ?? true; }
}
if ( ! function_exists( __NAMESPACE__ . '\wp_send_json_error' ) ) {
	function wp_send_json_error( $data ) {
		\ArtPulse\Core\Tests\FeedbackManagerTest::$json_error = $data; }
}
if ( ! function_exists( __NAMESPACE__ . '\add_action' ) ) {
	function add_action( $hook, $callback, $priority = 10, $args = 1 ) {
		\ArtPulse\Core\Tests\FeedbackManagerTest::$hooks[ $hook ][] = $callback; }
}
if ( ! function_exists( __NAMESPACE__ . '\do_action' ) ) {
	function do_action( $hook ) {
		foreach ( \ArtPulse\Core\Tests\FeedbackManagerTest::$hooks[ $hook ] ?? array() as $cb ) {
			call_user_func( $cb ); } }
}

namespace ArtPulse\Core\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Core\FeedbackManager;

if ( ! class_exists( __NAMESPACE__ . '\\WPDBStub' ) ) {
	/**
	 * @group core
	 */
	class WPDBStub {
		public string $prefix     = 'wp_';
		public array $insert_args = array();
		public function insert( $table, $data ) {
			$this->insert_args[] = array(
				'table' => $table,
				'data'  => $data,
			);
		}
	}
}

class FeedbackManagerTest extends TestCase {

	public static array $hooks         = array();
	public static $json_success        = null;
	public static $json_error          = null;
	public static int $current_user_id = 1;
	public static string $current_time = '2024-01-01 00:00:00';
	private $old_wpdb;

	protected function setUp(): void {
		self::$hooks        = array();
		self::$json_success = null;
		self::$json_error   = null;
		global $wpdb;
		$this->old_wpdb = $wpdb ?? null;
		$wpdb           = new WPDBStub();
		FeedbackManager::register();
		$_POST = array();
	}

	protected function tearDown(): void {
		global $wpdb;
		$wpdb  = $this->old_wpdb;
		$_POST = array();
		parent::tearDown();
	}

	public function test_handle_submission_inserts_row_and_returns_success(): void {
		global $wpdb;
		$_POST = array(
			'nonce'       => 'n',
			'type'        => 'suggestion',
			'description' => 'Great plugin',
			'email'       => 'test@example.com',
			'tags'        => 'tag',
			'context'     => 'ctx',
		);

		do_action( 'wp_ajax_ap_submit_feedback' );

		$this->assertNull( self::$json_error );
		$this->assertNotNull( self::$json_success );
		$this->assertCount( 1, $wpdb->insert_args );
		$args = $wpdb->insert_args[0];
		$this->assertSame( 'wp_ap_feedback', $args['table'] );
		$this->assertSame( 1, $args['data']['user_id'] );
		$this->assertSame( 'suggestion', $args['data']['type'] );
		$this->assertSame( 'Great plugin', $args['data']['description'] );
		$this->assertSame( 'test@example.com', $args['data']['email'] );
		$this->assertSame( 'tag', $args['data']['tags'] );
		$this->assertSame( 'ctx', $args['data']['context'] );
		$this->assertSame( self::$current_time, $args['data']['created_at'] );
	}

	public function test_submission_without_description_returns_error(): void {
		global $wpdb;
		$_POST = array(
			'nonce' => 'n',
			'type'  => 'bug',
			// no description
		);

		do_action( 'wp_ajax_ap_submit_feedback' );

		$this->assertNull( self::$json_success );
		$this->assertSame( array( 'message' => 'Description required.' ), self::$json_error );
		$this->assertCount( 0, $wpdb->insert_args );
	}
}
