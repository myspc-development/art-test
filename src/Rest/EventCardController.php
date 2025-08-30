<?php
namespace ArtPulse\Rest;

use ArtPulse\Rest\Util\Auth;
use WP_REST_Request;
use WP_Error;

class EventCardController {

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
                register_rest_route(
                        'artpulse/v1',
                        '/event-card/(?P<id>\\d+)',
                        array(
                                'methods'             => 'GET',
                                'callback'            => array( self::class, 'get_card' ),
                                'permission_callback' => array( Auth::class, 'guard_read' ),
                                'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
                        )
                );
        }

	public static function get_card( WP_REST_Request $request ) {
		$id = absint( $request->get_param( 'id' ) );
		if ( ! $id || get_post_type( $id ) !== 'artpulse_event' ) {
			return new WP_Error( 'invalid_event', 'Invalid event.', array( 'status' => 404 ) );
		}

		$html = ap_get_event_card( $id );
		return rest_ensure_response( $html );
	}
}
