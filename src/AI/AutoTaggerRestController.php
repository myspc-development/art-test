<?php
namespace ArtPulse\AI;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\AI\OpenAIClient;

/**
 * REST controller for generating tags from text via the OpenAI API.
 */
class AutoTaggerRestController {

	/**
	 * Hook into rest_api_init to register the route.
	 */
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	/**
	 * Register REST routes for the controller.
	 */
	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/tag' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/tag',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'generate_tags' ),
					'permission_callback' => static fn() => current_user_can( 'edit_posts' ),
					'args'                => array(
						'text' => array(
							'required'          => true,
							'sanitize_callback' => 'sanitize_textarea_field',
							'type'              => 'string',
						),
					),
				)
			);
		}
	}

	/**
	 * Generate tags for provided text using OpenAI.
	 */
	public static function generate_tags( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$text = sanitize_textarea_field( $request->get_param( 'text' ) );
		if ( $text === '' ) {
			return new WP_Error( 'invalid_text', __( 'Invalid text.', 'artpulse' ), array( 'status' => 400 ) );
		}

		$result = OpenAIClient::generateTags( $text );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return \rest_ensure_response( array( 'tags' => $result ) );
	}
}
