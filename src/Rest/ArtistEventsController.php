<?php
namespace ArtPulse\Rest;

use WP_Error;
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
               $posts = get_posts(
                       array(
                               'post_type'   => 'artpulse_event',
                               'author'      => get_current_user_id(),
                               'post_status' => array( 'publish', 'pending', 'draft', 'future' ),
                               'numberposts' => -1,
                       )
               );

               $events = array();

               foreach ( $posts as $post ) {
                       $status    = $post->post_status;
                       $events[]  = array(
                               'id'        => (int) $post->ID,
                               'title'     => get_the_title( $post ),
                               'status'    => $status,
                               'date'      => get_post_meta( $post->ID, '_ap_event_date', true ),
                               'end_date'  => get_post_meta( $post->ID, 'event_end_date', true ),
                               'permalink' => get_permalink( $post ),
                               'color'     => ( 'publish' === $status ) ? '#3b82f6' : '#9ca3af',
                       );
               }

               return self::ok( $events );
       }

       private static function ok( $data, int $status = 200 ): WP_REST_Response {
               return new WP_REST_Response( $data, $status );
       }
}
