<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class DirectoryController {

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
			'/events',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'get_events' ),
				'permission_callback' => function () {
					if ( ! current_user_can( 'read' ) ) {
						return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
					}
					return true;
				},
			)
		);
	}


	public static function get_events( WP_REST_Request $request ): WP_REST_Response {
		$query = new \WP_Query(
			array(
				'post_type'      => 'artpulse_event',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'meta_value',
				'meta_key'       => 'event_start_date',
				'order'          => 'ASC',
			)
		);
		$data  = array();
		foreach ( $query->posts as $post ) {
			$data[] = array(
				'id'         => $post->ID,
				'title'      => $post->post_title,
				'link'       => get_permalink( $post ),
				'start_date' => get_post_meta( $post->ID, 'event_start_date', true ),
				'end_date'   => get_post_meta( $post->ID, 'event_end_date', true ),
			);
		}
		return rest_ensure_response( $data );
	}
}
