<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class MessagesController {
	use RestResponder;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/messages/(?P<id>\d+)/reply' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/messages/(?P<id>\d+)/reply',
				array(
                                        'methods'             => 'POST',
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
                                        'callback'            => array( self::class, 'reply' ),
				)
			);
		}
	}

	public static function reply( WP_REST_Request $request ) {
		$msg_id  = (int) $request['id'];
		$content = sanitize_text_field( $request->get_param( 'message' ) );
		// Save to DB or email logic here...
		return \rest_ensure_response( array( 'status' => 'sent' ) );
	}
}
