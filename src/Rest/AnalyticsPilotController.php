<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

final class AnalyticsPilotController {
	use RestResponder;
        public static function register(): void {
                add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
        }

        public static function register_routes(): void {
                if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/analytics/pilot/invite' ) ) {
                        register_rest_route(
                                ARTPULSE_API_NAMESPACE,
                                '/analytics/pilot/invite',
                                array(
                                        'methods'             => 'POST',
                                        'callback'            => array( self::class, 'invite' ),
                                        'permission_callback' => Auth::require_login_and_cap( 'manage_options' ),
                                        'args'                => array(
                                                'user_id' => array( 'type' => 'integer' ),
                                                'email'   => array( 'type' => 'string' ),
                                        ),
                                )
                        );
                }
        }

        public static function invite( WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
                $responder = new self();

                if ( ! wp_verify_nonce( $req->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                        return $responder->fail( 'invalid_nonce', 'Invalid nonce', 401 );
                }

		$user    = null;
		$user_id = absint( $req->get_param( 'user_id' ) );
		if ( $user_id ) {
			$user = get_user_by( 'id', $user_id );
		}
		if ( ! $user ) {
			$email = sanitize_email( $req->get_param( 'email' ) );
			if ( $email ) {
				$user = get_user_by( 'email', $email );
			}
		}
                if ( ! $user ) {
                        return $responder->fail( 'user_not_found', 'User not found', 404 );
                }
                $user->add_cap( 'ap_analytics_pilot' );

                return $responder->ok( array( 'granted' => true ) );
        }
}
