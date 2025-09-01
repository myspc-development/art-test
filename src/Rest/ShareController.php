<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class ShareController {
	use RestResponder;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/share' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/share',
				array(
                                        'methods'             => 'POST',
                                        'callback'            => array( self::class, 'log_share' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
					'args'                => array(
						'object_id'   => array(
							'type'     => 'integer',
							'required' => true,
						),
						'object_type' => array(
							'type'     => 'string',
							'required' => true,
						),
						'network'     => array(
							'type'     => 'string',
							'required' => false,
						),
					),
				)
			);
		}
	}

	public static function log_share( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id   = absint( $request['object_id'] );
		$type = sanitize_key( $request['object_type'] );
		$net  = sanitize_key( $request->get_param( 'network' ) );

		if ( ! $id || ! $type ) {
			return new WP_Error( 'invalid_params', 'Invalid parameters.', array( 'status' => 400 ) );
		}

		if ( $type === 'artpulse_event' ) {
			do_action( 'ap_event_shared', $id, $net );
		} elseif ( $type === 'user' ) {
			do_action( 'ap_profile_shared', $id, $net );
		}

		return \rest_ensure_response( array( 'success' => true ) );
	}
}
