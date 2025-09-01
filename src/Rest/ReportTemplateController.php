<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\RestResponder;

class ReportTemplateController {
	use RestResponder;

	private const OPTION = 'ap_report_templates';

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/report-template/(?P<type>[^/]+)' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/report-template/(?P<type>[^/]+)',
				array(
					array(
						'methods'             => 'GET',
						'callback'            => array( self::class, 'get_template' ),
						'permission_callback' => array( self::class, 'permission' ),
					),
					array(
						'methods'             => 'POST',
						'callback'            => array( self::class, 'save_template' ),
						'permission_callback' => array( self::class, 'permission' ),
					),
				)
			);
		}
	}

	public static function permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public static function get_template( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$type      = sanitize_key( $request['type'] );
		$templates = get_option( self::OPTION, array() );
		return \rest_ensure_response( $templates[ $type ] ?? new \stdClass() );
	}

	public static function save_template( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$type   = sanitize_key( $request['type'] );
		$params = $request->get_json_params();
		$tpl    = $params['template'] ?? array();
		if ( ! is_array( $tpl ) ) {
			$tpl = array();
		}
		$templates          = get_option( self::OPTION, array() );
		$templates[ $type ] = $tpl;
		update_option( self::OPTION, $templates );
		return \rest_ensure_response( array( 'success' => true ) );
	}
}
