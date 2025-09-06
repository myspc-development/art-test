<?php
namespace ArtPulse\Rest;

// --- Stubs for WordPress functions used in the controller ---
if ( ! function_exists( __NAMESPACE__ . '\current_user_can' ) ) {
	function current_user_can( string $cap ) {
		return \ArtPulse\Rest\Tests\Stub::$can;
	}
}
if ( ! function_exists( __NAMESPACE__ . '\get_current_user_id' ) ) {
	function get_current_user_id() {
		return \ArtPulse\Rest\Tests\Stub::$current_user_id;
	}
}
if ( ! function_exists( __NAMESPACE__ . '\get_user_meta' ) ) {
	function get_user_meta( int $user_id, string $key, bool $single = false ) {
		return \ArtPulse\Rest\Tests\Stub::$user_meta[ $user_id ][ $key ] ?? '';
	}
}
if ( ! function_exists( __NAMESPACE__ . '\wp_mail' ) ) {
	function wp_mail( string $to, string $subject, string $message ) {
		\ArtPulse\Rest\Tests\Stub::$sent_emails[] = array( $to, $subject, $message );
		return true;
	}
}
if ( ! function_exists( __NAMESPACE__ . '\update_user_meta' ) ) {
	function update_user_meta( int $user_id, string $key, $value ) {
		\ArtPulse\Rest\Tests\Stub::$user_meta[ $user_id ][ $key ] = $value;
		return true;
	}
}
if ( ! function_exists( __NAMESPACE__ . '\get_user_by' ) ) {
	function get_user_by( string $field, string $value ) {
		return \ArtPulse\Rest\Tests\Stub::get_user_by( $field, $value );
	}
}
if ( ! function_exists( __NAMESPACE__ . '\wp_delete_user' ) ) {
	function wp_delete_user( int $user_id ) {
			\ArtPulse\Rest\Tests\Stub::$deleted_users[] = $user_id;
			return true;
	}
}
if ( ! function_exists( __NAMESPACE__ . '\rest_ensure_response' ) ) {
	function rest_ensure_response( $data ) {
			return new \WP_REST_Response( $data );
	}
}
if ( ! function_exists( __NAMESPACE__ . '\sanitize_email' ) ) {
	function sanitize_email( $email ) {
		return filter_var( $email, FILTER_SANITIZE_EMAIL );
	}
}
if ( ! function_exists( __NAMESPACE__ . '\is_email' ) ) {
	function is_email( $email ) {
		return (bool) filter_var( $email, FILTER_VALIDATE_EMAIL );
	}
}
if ( ! function_exists( __NAMESPACE__ . '\sanitize_text_field' ) ) {
	function sanitize_text_field( $value ) {
		return is_string( $value ) ? trim( $value ) : $value;
	}
}
if ( ! function_exists( __NAMESPACE__ . '\sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return preg_replace( '/[^a-z0-9_]/i', '', $key );
	}
}
if ( ! function_exists( __NAMESPACE__ . '\absint' ) ) {
	function absint( $num ) {
		return abs( intval( $num ) );
	}
}


namespace ArtPulse\Rest\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Rest\UserInvitationController;
use ArtPulse\Tests\Rest\RequestStub as TestRequest;
use WP_Error;
require_once __DIR__ . '/RequestStub.php';

class Stub {
	public static bool $can            = true;
	public static int $current_user_id = 1;
	public static array $user_meta     = array();
	public static array $sent_emails   = array();
	public static array $deleted_users = array();
	public static array $users         = array();

	public static function reset(): void {
		self::$can             = true;
		self::$current_user_id = 1;
		self::$user_meta       = array();
		self::$sent_emails     = array();
		self::$deleted_users   = array();
		self::$users           = array();
	}

	public static function get_user_by( string $field, string $value ) {
		foreach ( self::$users as $id => $data ) {
			if ( $field === 'email' && $data['user_email'] === $value ) {
				return (object) array( 'ID' => $id );
			}
		}
		return false;
	}
}

/**
 * @group REST
 */
class UserInvitationControllerTest extends TestCase {

	protected function setUp(): void {
		Stub::reset();
	}

	public function test_invite_success(): void {
			Stub::$user_meta[1]['ap_organization_id'] = 5;
			Stub::$users                              = array( 2 => array( 'user_email' => 'a@test.com' ) );
			$req                                      = new TestRequest( 'POST', '/' );
			$req->set_param( 'id', 5 );
			$req->set_json_params(
				array(
					'emails' => array( 'a@test.com', 'b@test.com' ),
					'role'   => 'event_manager',
				)
			);
			$res = UserInvitationController::invite( $req );
			$this->assertInstanceOf( \WP_REST_Response::class, $res );
			$this->assertSame(
				array(
					'invited' => array( 'a@test.com', 'b@test.com' ),
					'role'    => 'event_manager',
				),
				$res->get_data()
			);
			$this->assertCount( 2, Stub::$sent_emails );
			$this->assertSame( 5, Stub::$user_meta[2]['ap_organization_id'] );
			$this->assertSame( 'event_manager', Stub::$user_meta[2]['ap_org_role'] );
	}

	public function test_invite_permission_failure(): void {
		Stub::$user_meta[1]['ap_organization_id'] = 3; // user not admin of org 5
		$req                                      = new TestRequest( 'POST', '/' );
		$req->set_param( 'id', 5 );
		$req->set_json_params( array( 'emails' => array( 'a@test.com' ) ) );
		$this->assertFalse( UserInvitationController::check_permissions( $req ) );
	}

	public function test_invite_invalid_email(): void {
		Stub::$user_meta[1]['ap_organization_id'] = 5;
		$req                                      = new TestRequest( 'POST', '/' );
		$req->set_param( 'id', 5 );
		$req->set_json_params( array( 'emails' => array( 'bad-email' ) ) );
		$res = UserInvitationController::invite( $req );
		$this->assertInstanceOf( WP_Error::class, $res );
	}

	public function test_batch_suspend_success(): void {
		Stub::$user_meta[1]['ap_organization_id'] = 5;
		$req                                      = new TestRequest( 'POST', '/' );
		$req->set_param( 'id', 5 );
				$req->set_json_params(
					array(
						'action'   => 'suspend',
						'user_ids' => array( 7 ),
					)
				);
				$res = UserInvitationController::batch_users( $req );
				$this->assertInstanceOf( \WP_REST_Response::class, $res );
				$this->assertSame(
					array(
						'action'    => 'suspend',
						'processed' => array( 7 ),
					),
					$res->get_data()
				);
				$this->assertSame( 1, Stub::$user_meta[7]['ap_suspended'] );
	}

	public function test_batch_invalid_action(): void {
		Stub::$user_meta[1]['ap_organization_id'] = 5;
		$req                                      = new TestRequest( 'POST', '/' );
		$req->set_param( 'id', 5 );
				$req->set_json_params(
					array(
						'action'   => 'widget_foo',
						'user_ids' => array( 2 ),
					)
				);
		$res = UserInvitationController::batch_users( $req );
		$this->assertInstanceOf( WP_Error::class, $res );
	}
}
