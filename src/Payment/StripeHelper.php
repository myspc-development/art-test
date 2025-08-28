<?php
namespace ArtPulse\Payment;

use Stripe\StripeClient;
use WP_Error;

class StripeHelper {

	/**
	 * Create a Stripe client using plugin settings.
	 *
	 * @return StripeClient|WP_Error
	 */
	public static function create_client() {
		$settings = get_option( 'artpulse_settings', array() );
		$secret   = $settings['stripe_secret_key'] ?? ( $settings['stripe_secret'] ?? '' );

		if ( ! $secret || ! class_exists( StripeClient::class ) ) {
			return new WP_Error( 'stripe_unavailable', 'Stripe not configured', array( 'status' => 500 ) );
		}

		return new StripeClient( $secret );
	}

	/**
	 * Create a Stripe Checkout session with unified error handling.
	 *
	 * @param array $params Session parameters for Stripe API.
	 * @return \Stripe\Checkout\Session|WP_Error
	 */
	public static function create_session( array $params ) {
		$stripe = self::create_client();
		if ( is_wp_error( $stripe ) ) {
			return $stripe;
		}

		try {
			$session = $stripe->checkout->sessions->create( $params );
		} catch ( \Exception $e ) {
			return new WP_Error( 'stripe_error', $e->getMessage(), array( 'status' => 500 ) );
		}

		return $session;
	}

	/**
	 * Create a Stripe Payment Intent with unified error handling.
	 *
	 * @param array $params Payment intent parameters.
	 * @return \Stripe\PaymentIntent|WP_Error
	 */
	public static function create_intent( array $params ) {
		$stripe = self::create_client();
		if ( is_wp_error( $stripe ) ) {
			return $stripe;
		}

		try {
			$intent = $stripe->paymentIntents->create( $params );
		} catch ( \Exception $e ) {
			return new WP_Error( 'stripe_error', $e->getMessage(), array( 'status' => 500 ) );
		}

		return $intent;
	}
}
