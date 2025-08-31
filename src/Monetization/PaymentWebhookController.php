<?php
namespace ArtPulse\Monetization;

/**
 * Receives payment provider webhooks.
 */
class PaymentWebhookController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/payment/webhook' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/payment/webhook',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'handle' ),
					'permission_callback' => function () {
						if ( ! current_user_can( 'read' ) ) {
							return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
						}
						return true;
					},
				)
			);
		}
	}

	public static function handle( \WP_REST_Request $req ) {
		$provider  = sanitize_text_field( $req->get_param( 'provider' ) );
		$status    = sanitize_text_field( $req->get_param( 'status' ) );
		$ticket_id = absint( $req->get_param( 'ticket_id' ) );
		$user_id   = absint( $req->get_param( 'user_id' ) );

		if ( $provider === 'stripe' ) {
			$opts      = get_option( 'artpulse_settings', array() );
			$secret    = $opts['stripe_webhook_secret'] ?? '';
			$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
			if ( $secret && class_exists( '\\Stripe\\Webhook' ) ) {
				try {
					\Stripe\Webhook::constructEvent( $req->get_body(), $sigHeader, $secret );
				} catch ( \Exception $e ) {
					return new \WP_Error( 'invalid_signature', 'Invalid signature', array( 'status' => 400 ) );
				}
			}
		}

		if ( $status !== 'success' || ! $ticket_id || ! $user_id ) {
			return \rest_ensure_response( array( 'ignored' => true ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ap_tickets';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			$wpdb->update(
				$table,
				array( 'status' => 'active' ),
				array(
					'id'      => $ticket_id,
					'user_id' => $user_id,
				)
			);
		}

		do_action( 'artpulse_ticket_purchased', $user_id, 0, 0, 1 );
		return \rest_ensure_response( array( 'status' => 'ok' ) );
	}
}
