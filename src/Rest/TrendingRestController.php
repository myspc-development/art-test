<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use ArtPulse\Discovery\TrendingManager;

class TrendingRestController {
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/trending' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/trending',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_trending' ),
					'permission_callback' => function () {
						return current_user_can( 'read' );
					},
					'args'                => array(
						'type'  => array(
							'type'    => 'string',
							'default' => 'artwork',
						),
						'limit' => array(
							'type'    => 'integer',
							'default' => 20,
						),
					),
				)
			);
		}
	}

	public static function get_trending( WP_REST_Request $request ) {
		$type  = sanitize_key( $request['type'] );
		$limit = max( 1, min( 50, (int) $request['limit'] ) );
		$items = TrendingManager::get_trending( $limit, $type );
		return rest_ensure_response( $items );
	}
}
