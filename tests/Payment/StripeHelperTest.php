<?php
namespace ArtPulse\Payment\Tests;

use ArtPulse\Payment\StripeHelper;
use WP_UnitTestCase;

/**

 * @group payment

 */

class StripeHelperTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		update_option( 'artpulse_settings', array() );
	}

	public function test_create_client_returns_error_when_missing_secret(): void {
		$client = StripeHelper::create_client();
		$this->assertInstanceOf( \WP_Error::class, $client );
	}

	public function test_create_session_returns_session_object(): void {
		update_option( 'artpulse_settings', array( 'stripe_secret' => 'sk_test' ) );
		$session = StripeHelper::create_session(
			array(
				'payment_method_types' => array( 'card' ),
				'mode'                 => 'payment',
			)
		);
		$this->assertIsObject( $session );
		$this->assertSame( 'sess_123', $session->id );
	}
}
