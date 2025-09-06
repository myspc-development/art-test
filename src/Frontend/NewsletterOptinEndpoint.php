<?php
namespace ArtPulse\Frontend;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class NewsletterOptinEndpoint {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/newsletter-optin' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/newsletter-optin',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'handle' ),
					'permission_callback' => function () {
						if ( ! current_user_can( 'read' ) ) {
							return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
						}
						return true;
					},
					'args'                => array(
						'email' => array(
							'type'     => 'string',
							'required' => true,
						),
					),
				)
			);
		}
	}

	public static function handle( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$email = sanitize_email( $req['email'] );
		if ( ! is_email( $email ) ) {
			return new WP_Error( 'invalid_email', 'Invalid email', array( 'status' => 400 ) );
		}
		$opts    = get_option( 'artpulse_settings', array() );
		$api_key = $opts['mailchimp_api_key'] ?? '';
		$list_id = $opts['mailchimp_list_id'] ?? '';
		if ( ! $api_key || ! $list_id ) {
			return new WP_Error( 'missing_config', 'Mailchimp not configured', array( 'status' => 500 ) );
		}
		$dc       = substr( $api_key, strpos( $api_key, '-' ) + 1 );
			$url  = sprintf( 'https://%1$s.api.mailchimp.com/3.0/lists/%2$s/members', $dc, $list_id );
		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'apikey ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'email_address' => $email,
						'status'        => 'subscribed',
					)
				),
			)
		);
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'request_failed', 'Request failed', array( 'status' => 500 ) );
		}
		$code = wp_remote_retrieve_response_code( $response );
		if ( $code >= 200 && $code < 300 ) {
			return new WP_REST_Response( array( 'status' => 'subscribed' ) );
		}
		return new WP_Error( 'api_error', 'Mailchimp error', array( 'status' => $code ) );
	}
}
