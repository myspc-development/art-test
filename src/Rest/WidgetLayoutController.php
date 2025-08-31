<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Rest\Util\Auth;

class WidgetLayoutController {

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
			'/widget-layout',
			array(
				array(
                                        'methods'             => WP_REST_Server::CREATABLE,
                                        'callback'            => array( self::class, 'save_layout' ),
                                        'permission_callback' => array( Auth::class, 'guard_manage' ),
					'args'                => array(
						'layout' => array(
							'type'     => 'array',
							'required' => false,
						),
					),
				),
			)
		);
	}

	public static function save_layout( WP_REST_Request $request ): WP_REST_Response {
		$layout = $request->get_json_params();
		if ( ! is_array( $layout ) ) {
			$layout = array();
		}
		update_user_meta( get_current_user_id(), UserLayoutManager::META_KEY, $layout );
		return \rest_ensure_response( array( 'saved' => true ) );
	}
}
