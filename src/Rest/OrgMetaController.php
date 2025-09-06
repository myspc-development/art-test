<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class OrgMetaController {
	use RestResponder;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'routes' ) );
	}

	public static function routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/org/(?P<id>\d+)/meta' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/org/(?P<id>\d+)/meta',
				array(
					'methods'             => array( 'GET', 'POST' ),
					'callback'            => array( self::class, 'handle' ),
					'permission_callback' => array( Auth::class, 'guard_read' ),
					'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
				)
			);
		}
	}

	public static function handle( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$id = absint( $req['id'] );
		if ( $req->get_method() === 'GET' ) {
			$data = array(
				'logo'   => get_post_meta( $id, 'branding_logo', true ),
				'color'  => get_post_meta( $id, 'branding_color', true ),
				'footer' => get_post_meta( $id, 'branding_footer', true ),
			);
			return \rest_ensure_response( $data );
		}
		$body = json_decode( $req->get_body(), true ) ?: array();
		update_post_meta( $id, 'branding_logo', sanitize_text_field( $body['logo'] ?? '' ) );
		update_post_meta( $id, 'branding_color', sanitize_hex_color( $body['color'] ?? '' ) );
		update_post_meta( $id, 'branding_footer', sanitize_text_field( $body['footer'] ?? '' ) );
		return \rest_ensure_response( array( 'success' => true ) );
	}
}
