<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class SpotlightRestController {
	use RestResponder;

	private const NAMESPACE = 'artpulse/v1';

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/spotlights',
			array(
                                'methods'             => WP_REST_Server::READABLE,
                                'callback'            => array( self::class, 'get_current' ),
                                'permission_callback' => array( Auth::class, 'guard_read' ),
			)
		);
	}

	public static function get_current( WP_REST_Request $request ): WP_REST_Response {
		$today = current_time( 'Y-m-d' );
		$query = new \WP_Query(
			array(
				'post_type'      => 'artpulse_artist',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'   => 'artist_spotlight',
						'value' => '1',
					),
					array(
						'key'     => 'spotlight_start_date',
						'value'   => $today,
						'compare' => '<=',
						'type'    => 'DATE',
					),
					array(
						'relation' => 'OR',
						array(
							'key'     => 'spotlight_end_date',
							'value'   => $today,
							'compare' => '>=',
							'type'    => 'DATE',
						),
						array(
							'key'     => 'spotlight_end_date',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => 'spotlight_end_date',
							'value'   => '',
							'compare' => '=',
						),
					),
				),
			)
		);

		$data = array();
		foreach ( $query->posts as $id ) {
			$data[] = array(
				'id'    => $id,
				'title' => get_the_title( $id ),
				'link'  => get_permalink( $id ),
				'thumb' => get_the_post_thumbnail_url( $id, 'thumbnail' ),
			);
		}

		return \rest_ensure_response( $data );
	}
}
