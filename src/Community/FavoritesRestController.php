<?php

namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Community\FavoritesManager;

class FavoritesRestController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/favorites' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/favorites',
				array(
					'methods'             => 'DELETE',
					'callback'            => array( self::class, 'remove_favorite' ),
					'permission_callback' => fn() => is_user_logged_in(),
					'args'                => array(
						'object_id'   => array(
							'type'     => 'integer',
							'required' => true,
						),
						'object_type' => array(
							'type'     => 'string',
							'required' => true,
						),
					),
				)
			);
		}
	}

	public static function remove_favorite( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$user_id     = get_current_user_id();
		$object_id   = absint( $request['object_id'] );
		$object_type = sanitize_key( $request['object_type'] );

		if ( ! $object_id || ! $object_type ) {
			return new WP_Error( 'invalid_params', 'Invalid parameters.', array( 'status' => 400 ) );
		}

		FavoritesManager::remove_favorite( $user_id, $object_id, $object_type );

		return \rest_ensure_response(
			array(
				'status' => 'removed',
				'id'     => $object_id,
			)
		);
	}
}
