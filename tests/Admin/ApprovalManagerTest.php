<?php
namespace ArtPulse\Admin;

// --- WordPress function stubs ---
if ( ! function_exists( __NAMESPACE__ . '\\current_user_can' ) ) {
	function current_user_can( $cap ) {
		return \ArtPulse\Admin\Tests\ApprovalManagerTest::$can; }
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_die' ) ) {
	function wp_die( $msg = '' ) {
		\ArtPulse\Admin\Tests\ApprovalManagerTest::$died = $msg ?: true; }
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_verify_nonce' ) ) {
	function wp_verify_nonce( $nonce, $action ) {
		return true; }
}
if ( ! function_exists( __NAMESPACE__ . '\\get_post' ) ) {
	function get_post( $post_id ) {
		return \ArtPulse\Admin\Tests\ApprovalManagerTest::$post; }
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_update_post' ) ) {
	function wp_update_post( $arr ) {
		\ArtPulse\Admin\Tests\ApprovalManagerTest::$updated = $arr; }
}
if ( ! function_exists( __NAMESPACE__ . '\\wp_safe_redirect' ) ) {
	function wp_safe_redirect( $url ) {
		\ArtPulse\Admin\Tests\ApprovalManagerTest::$redirect = $url;
		throw new \Exception( 'redirect' ); }
}
if ( ! function_exists( __NAMESPACE__ . '\\update_user_meta' ) ) {
	function update_user_meta( $user_id, $key, $value ) {
		\ArtPulse\Admin\Tests\ApprovalManagerTest::$meta[ $user_id ][ $key ] = $value; }
}
if ( ! function_exists( __NAMESPACE__ . '\\delete_user_meta' ) ) {
	function delete_user_meta( $user_id, $key ) {
		\ArtPulse\Admin\Tests\ApprovalManagerTest::$deleted[ $user_id ][] = $key; }
}

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\ApprovalManager;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**

 * @group ADMIN
 */

class ApprovalManagerTest extends TestCase {

	public static bool $can = true;
	public static $post;
	public static array $updated   = array();
	public static string $redirect = '';
	public static array $meta      = array();
	public static array $deleted   = array();
	public static $died            = null;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'admin_url' )->alias( fn( $path = '' ) => $path );

		self::$can        = true;
		self::$post       = (object) array(
			'ID'          => 7,
			'post_type'   => 'artpulse_org',
			'post_author' => 4,
		);
		self::$updated    = array();
		self::$redirect   = '';
		self::$meta       = array();
		self::$deleted    = array();
		self::$died       = null;
		$_POST['post_id'] = 7;
		$_POST['nonce']   = 'nonce';
	}

	protected function tearDown(): void {
		$_POST          = array();
		self::$updated  = array();
		self::$redirect = '';
		self::$meta     = array();
		self::$deleted  = array();
		self::$died     = null;
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_handle_approval_sets_user_meta_for_org(): void {
		try {
			ApprovalManager::handleApproval();
		} catch ( \Exception $e ) {
			$this->assertSame( 'redirect', $e->getMessage() );
		}

		$this->assertSame(
			array(
				'ID'          => 7,
				'post_status' => 'publish',
			),
			self::$updated
		);
		$this->assertSame( 7, self::$meta[4]['ap_organization_id'] ?? null );
		$this->assertContains( 'ap_pending_organization_id', self::$deleted[4] ?? array() );
	}
}
