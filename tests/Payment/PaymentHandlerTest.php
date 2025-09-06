<?php
namespace ArtPulse\Payment\Tests;

use ArtPulse\Payment\PaymentHandler;
use Brain\Monkey;
use function Brain\Monkey\Functions\when;
use WP_UnitTestCase;

/**

 * @group PAYMENT
 */

class PaymentHandlerTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		Monkey\setUp();
		update_option( 'artpulse_settings', array() );
		require_once dirname( __DIR__, 2 ) . '/includes/payment-handler.php';
	}

	public function tear_down() {
		Monkey\tearDown();
		parent::tear_down();
	}

	public function test_create_stripe_session_builds_expected_payload(): void {
		$captured = null;
		when( 'ArtPulse\\Payment\\StripeHelper::create_session' )->alias(
			function ( $params ) use ( &$captured ) {
				$captured = $params;
				return (object) array( 'id' => 'sess_dummy' );
			}
		);

		update_option( 'artpulse_settings', array( 'currency' => 'eur' ) );

		$session = PaymentHandler::create_stripe_session( 25.5, array( 'order_id' => 123 ) );

		$this->assertIsObject( $session );
		$this->assertSame( 'sess_dummy', $session->id );

		$expected_line_items = array(
			array(
				'price_data' => array(
					'currency'     => 'eur',
					'unit_amount'  => 2550,
					'product_data' => array( 'name' => 'Featured Listing' ),
				),
				'quantity'   => 1,
			),
		);

		$this->assertSame( $expected_line_items, $captured['line_items'] );
		$this->assertSame( 'http://example.org/?ap_payment=success', $captured['success_url'] );
		$this->assertSame( 'http://example.org/?ap_payment=cancel', $captured['cancel_url'] );
	}
}
