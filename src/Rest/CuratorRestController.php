<?php
namespace ArtPulse\Rest;

use ArtPulse\Curator\CuratorManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;

class CuratorRestController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/curators' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/curators',
				array(
					'methods'             => WP_REST_Server::READABLE,
                                        'callback'            => array( self::class, 'get_curators' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/curator/(?P<slug>[a-z0-9-]+)' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/curator/(?P<slug>[a-z0-9-]+)',
				array(
					'methods'             => WP_REST_Server::READABLE,
                                        'callback'            => array( self::class, 'get_curator' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
					'args'                => array(
						'slug' => array( 'sanitize_callback' => 'sanitize_title' ),
					),
				)
			);
		}
	}

	public static function get_curators( WP_REST_Request $req ): WP_REST_Response {
		$list = CuratorManager::get_all();
		return rest_ensure_response( $list );
	}

	public static function get_curator( WP_REST_Request $req ): WP_REST_Response {
		$slug    = sanitize_title( $req['slug'] );
		$curator = CuratorManager::get_by_slug( $slug );
		if ( ! $curator ) {
			return new WP_REST_Response( array( 'message' => 'Curator not found' ), 404 );
		}
		$collections            = get_posts(
			array(
				'post_type'      => 'ap_collection',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'author'         => $curator['user_id'],
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		$curator['collections'] = array_map( 'intval', $collections );
		return rest_ensure_response( $curator );
	}
}
