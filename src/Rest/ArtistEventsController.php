<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_Query;
use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Rest\Util\Auth;

class ArtistEventsController {

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
			'/artist-events',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'get_events' ),
				'permission_callback' => Auth::require_login_and_cap( 'read' ),
			)
		);
	}

       public static function get_events( WP_REST_Request $request ): WP_REST_Response|WP_Error {
               $query = new WP_Query(
                       array(
                               'post_type'      => 'artpulse_event',
                               'author'         => get_current_user_id(),
                               'post_status'    => array( 'publish', 'pending', 'draft', 'future' ),
                               'fields'         => 'ids',
                               'posts_per_page' => -1,
                       )
               );

               return rest_ensure_response( $query->posts );
       }

}
