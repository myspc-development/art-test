<?php
namespace ArtPulse\Monetization;

/**
 * Handles promo codes and discounts.
 */
class PromoManager {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\\d+)/promo-code/apply' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\\d+)/promo-code/apply',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'apply_code' ),
					'permission_callback' => '__return_true',
					'args'                => array( 'id' => array( 'validate_callback' => 'absint' ) ),
				)
			);
		}
	}

	public static function apply_code( \WP_REST_Request $req ) {
		$event_id = absint( $req->get_param( 'id' ) );
		$code     = sanitize_text_field( $req->get_param( 'code' ) );

		if ( ! $event_id || ! $code ) {
			return new \WP_Error( 'invalid_params', 'Invalid parameters.', array( 'status' => 400 ) );
		}

		$codes = get_post_meta( $event_id, 'ap_promo_codes', true );
		if ( ! is_array( $codes ) || empty( $codes[ $code ] ) ) {
			return new \WP_Error( 'invalid_code', 'Promo code not found.', array( 'status' => 404 ) );
		}

		$discount = floatval( $codes[ $code ] );

		return \rest_ensure_response(
			array(
				'code'     => $code,
				'discount' => $discount,
			)
		);
	}
}
