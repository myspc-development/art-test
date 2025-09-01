<?php
namespace ArtPulse\Rest;

use ArtPulse\Rest\Util\Auth;
use WP_REST_Request;
use WP_Error;
use ArtPulse\Rest\RestResponder;

class SpotlightAnalyticsController {
	use RestResponder;

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/spotlight/view',
			array(
				'methods'             => 'POST',
				'callback'            => array( self::class, 'log_view' ),
                                'permission_callback' => Auth::require_login_and_cap(null),
				'args'                => array(
					'id' => array(
						'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ),
						'required'          => true,
					),
				),
			)
		);
	}

	public static function log_view( WP_REST_Request $request ) {
		$id = absint( $request['id'] );
		if ( ! $id ) {
			return new WP_Error( 'invalid_id', 'Invalid spotlight ID', array( 'status' => 400 ) );
		}
		$views = (int) get_post_meta( $id, 'spotlight_views', true );
		update_post_meta( $id, 'spotlight_views', $views + 1 );
		return \rest_ensure_response( array( 'views' => $views + 1 ) );
	}
}
