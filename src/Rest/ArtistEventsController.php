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

        /**
         * Retrieve events created by the authenticated artist.
         *
         * @param WP_REST_Request $request Request object.
         * @return WP_REST_Response|WP_Error REST response on success or error on failure.
         */
        public static function get_events( WP_REST_Request $request ): WP_REST_Response|WP_Error {
                $permission = Auth::guard_read( $request );
                if ( is_wp_error( $permission ) ) {
                        return $permission;
                }

                // Ensure the custom post type exists before querying.
                if ( ! post_type_exists( 'artpulse_event' ) ) {
                        if ( class_exists( '\\ArtPulse\\Core\\PostTypeRegistrar' ) ) {
                                \ArtPulse\Core\PostTypeRegistrar::register();
                        } else {
                                register_post_type(
                                        'artpulse_event',
                                        array(
                                                'public'   => true,
                                                'supports' => array( 'title' ),
                                        )
                                );
                        }
                }

                $query = new WP_Query(
                        array(
                                'post_type'      => 'artpulse_event',
                                'author'         => get_current_user_id(),
                                'post_status'    => array( 'publish', 'pending', 'draft', 'future' ),
                                'fields'         => 'ids',
                                'posts_per_page' => -1,
                        )
                );

                $data = array();
                foreach ( $query->posts as $post_id ) {
                        $status = get_post_status( $post_id );
                        $color  = $status === 'publish' ? '#3b82f6' : '#9ca3af';
                        $data[] = array(
                                'id'     => (int) $post_id,
                                'title'  => get_the_title( $post_id ),
                                'start'  => get_post_meta( $post_id, '_ap_event_date', true ),
                                'end'    => get_post_meta( $post_id, 'event_end_date', true ),
                                'status' => $status,
                                'color'  => $color,
                        );
                }

                // Wrap the results in a WP REST response object.
                return new \WP_REST_Response( $data );
       }

}
