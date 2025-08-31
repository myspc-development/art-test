<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;

class ImportTemplateController {

	private const OPTION = 'ap_import_templates';

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/import-template/(?P<post_type>[^/]+)' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/import-template/(?P<post_type>[^/]+)',
				array(
					array(
						'methods'             => 'GET',
						'callback'            => array( self::class, 'get_template' ),
						'permission_callback' => array( ImportRestController::class, 'check_permissions' ),
						'args'                => array(
							'post_type' => array(
								'validate_callback' => 'sanitize_key',
							),
						),
					),
					array(
						'methods'             => 'POST',
						'callback'            => array( self::class, 'save_template' ),
						'permission_callback' => array( ImportRestController::class, 'check_permissions' ),
					),
				)
			);
		}
	}

	public static function get_template( WP_REST_Request $request ): WP_REST_Response {
		$post_type = sanitize_key( $request['post_type'] );
		$templates = get_option( self::OPTION, array() );
		return \rest_ensure_response( $templates[ $post_type ] ?? new \stdClass() );
	}

	public static function save_template( WP_REST_Request $request ): WP_REST_Response {
		$post_type = sanitize_key( $request['post_type'] );
		$params    = $request->get_json_params();
		$mapping   = $params['mapping'] ?? array();
		$trim      = ! empty( $params['trim'] );

		if ( ! is_array( $mapping ) ) {
			$mapping = array();
		}

		$templates               = get_option( self::OPTION, array() );
		$templates[ $post_type ] = array(
			'mapping' => $mapping,
			'trim'    => $trim,
		);
		update_option( self::OPTION, $templates );

		return \rest_ensure_response( array( 'success' => true ) );
	}
}
