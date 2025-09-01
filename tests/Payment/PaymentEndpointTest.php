<?php
namespace ArtPulse\Payment\Tests;

use Brain\Monkey;
use function Brain\Monkey\Functions\when;

/**

 * @group PAYMENT

 */

class PaymentEndpointTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		Monkey\setUp();

		require_once dirname( __DIR__, 2 ) . '/includes/rest-dedupe.php';
		if ( ! defined( 'ARTPULSE_API_NAMESPACE' ) ) {
			define( 'ARTPULSE_API_NAMESPACE', 'artpulse/v1' );
		}
		require_once dirname( __DIR__, 2 ) . '/payments.php';
		do_action( 'rest_api_init' );
	}

	public function tear_down() {
		Monkey\tearDown();
		parent::tear_down();
	}

	public function test_create_payment_intent_requires_read_capability(): void {
		wp_set_current_user( 0 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/payment/intent' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 403, $res->get_status() );
	}

	public function test_create_payment_intent_returns_client_secret_for_authorized_user(): void {
		when( 'ArtPulse\\Payment\\StripeHelper::create_intent' )->justReturn( (object) array( 'client_secret' => 'pi_secret_123' ) );

		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/payment/intent' );
		$req->set_body_params(
			array(
				'amount'   => 500,
				'currency' => 'usd',
			)
		);
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( 'pi_secret_123', $data['client_secret'] );
	}

	public function test_create_checkout_session_requires_read_capability(): void {
		wp_set_current_user( 0 );
		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/payment/checkout' );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 403, $res->get_status() );
	}

	public function test_create_checkout_session_returns_session_id_for_authorized_user(): void {
		when( 'ArtPulse\\Payment\\StripeHelper::create_session' )->justReturn( (object) array( 'id' => 'sess_999' ) );

		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$req = new \WP_REST_Request( 'POST', '/artpulse/v1/payment/checkout' );
		$req->set_body_params( array( 'price_id' => 'price_123' ) );
		$res = rest_get_server()->dispatch( $req );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertSame( 'sess_999', $data['id'] );
	}
}
