<?php
namespace ArtPulse\Personalization;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class RecommendationRestController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/recommendations' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/recommendations',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_recommendations' ),
					'permission_callback' => function () {
						if ( ! current_user_can( 'read' ) ) {
							return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
						}
						return true;
					},
					'args'                => array(
						'type'     => array(
							'type'    => 'string',
							'enum'    => array( 'event', 'artist' ),
							'default' => 'event',
						),
						'user_id'  => array(
							'type'     => 'integer',
							'required' => false,
						),
						'limit'    => array(
							'type'    => 'integer',
							'default' => 6,
						),
						'location' => array(
							'type'        => 'string',
							'required'    => false,
							'description' => 'ZIP code or "lat,lng" pair',
						),
					),
				)
			);
		}
	}

	public static function get_recommendations( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$user_id = $request->get_param( 'user_id' );
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id ) {
			return new WP_Error( 'invalid_user', 'User not specified', array( 'status' => 400 ) );
		}

		$type     = sanitize_key( $request->get_param( 'type' ) );
		$limit    = absint( $request->get_param( 'limit' ) );
		$location = $request->get_param( 'location' );
		$data     = RecommendationEngine::get_recommendations( (int) $user_id, $type, $limit, $location );
		return \rest_ensure_response( $data );
	}
}
