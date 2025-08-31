<?php
namespace ArtPulse\Monetization;

use WP_REST_Request;

/**
 * Simple event promotion actions.
 */
class EventPromotionManager {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\\d+)/feature' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\\d+)/feature',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'feature_event' ),
					'permission_callback' => array( self::class, 'can_edit' ),
					'args'                => array( 'id' => array( 'validate_callback' => 'absint' ) ),
				)
			);
		}
	}

	public static function can_edit( WP_REST_Request $req ) {
		$id = absint( $req->get_param( 'id' ) );
		if ( ! current_user_can( 'edit_post', $id ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	public static function feature_event( WP_REST_Request $req ) {
		$id = absint( $req->get_param( 'id' ) );
		if ( ! $id ) {
			return new \WP_Error( 'invalid_event', 'Invalid event.', array( 'status' => 400 ) );
		}
		update_post_meta( $id, 'ap_featured', 1 );
		return \rest_ensure_response( array( 'featured' => true ) );
	}
}
