<?php

namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Community\NotificationManager;

class NotificationRestController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/notifications' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/notifications',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'list' ),
					'permission_callback' => fn() => is_user_logged_in(),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/notifications/(?P<id>\\d+)/read' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/notifications/(?P<id>\\d+)/read',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'mark_read' ),
					'permission_callback' => fn() => is_user_logged_in(),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/notifications/mark-all-read' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/notifications/mark-all-read',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'mark_all_read' ),
					'permission_callback' => fn() => is_user_logged_in(),
				)
			);
		}
	}

	public static function list( WP_REST_Request $request ): WP_REST_Response {
		$user_id = get_current_user_id();
		$limit   = isset( $request['limit'] ) ? absint( $request['limit'] ) : 25;

		$notifications = NotificationManager::get( $user_id, $limit );

		return \rest_ensure_response( $notifications );
	}

	public static function mark_read( WP_REST_Request $request ): WP_REST_Response {
		$user_id = get_current_user_id();
		$id      = absint( $request['id'] );

		if ( $id ) {
			NotificationManager::mark_read( $id, $user_id );
		}

		return \rest_ensure_response(
			array(
				'status' => 'read',
				'id'     => $id,
			)
		);
	}

	public static function mark_all_read( WP_REST_Request $request ): WP_REST_Response {
		$user_id = get_current_user_id();
		NotificationManager::mark_all_read( $user_id );

		return \rest_ensure_response( array( 'status' => 'all_read' ) );
	}
}
