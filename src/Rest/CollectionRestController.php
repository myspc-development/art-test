<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;

class CollectionRestController {

	private const NAMESPACE = 'artpulse/v1';

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/collections',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
                                        'callback'            => array( self::class, 'get_collections' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
                                        'callback'            => array( self::class, 'create_or_update' ),
                                        'permission_callback' => Auth::require_login_and_cap( 'edit_ap_collections' ),
					'args'                => array(
						'id'    => array(
							'type'     => 'integer',
							'required' => false,
						),
						'title' => array(
							'type'     => 'string',
							'required' => true,
						),
						'items' => array(
							'type'     => 'array',
							'items'    => array( 'type' => 'integer' ),
							'required' => false,
						),
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/collection/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
                                'callback'            => array( self::class, 'get_collection' ),
                                'permission_callback' => array( Auth::class, 'guard_read' ),
				'args'                => array(
					'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
				),
			)
		);
	}

	public static function get_collections( WP_REST_Request $request ): WP_REST_Response {
		$posts = get_posts(
			array(
				'post_type'      => 'ap_collection',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		$data = array();
		foreach ( $posts as $id ) {
			$post = get_post( $id );
			if ( ! $post ) {
				continue;
			}

			$item_ids = get_post_meta( $id, 'ap_collection_items', true ) ?: array();
			$items    = array();
			foreach ( $item_ids as $pid ) {
				$p = get_post( $pid );
				if ( ! $p ) {
					continue;
				}
				$items[] = array(
					'type'      => $p->post_type,
					'id'        => $p->ID,
					'title'     => $p->post_title,
					'excerpt'   => get_the_excerpt( $p ),
					'thumbnail' => get_the_post_thumbnail_url( $p, 'thumbnail' ) ?: '',
				);
			}

			$data[] = array(
				'id'          => $post->ID,
				'title'       => $post->post_title,
				'description' => $post->post_content,
				'thumbnail'   => get_the_post_thumbnail_url( $post, 'thumbnail' ) ?: '',
				'items'       => $items,
			);
		}
		return \rest_ensure_response( $data );
	}

	public static function create_or_update( WP_REST_Request $request ): WP_REST_Response {
		$id    = intval( $request->get_param( 'id' ) );
		$title = sanitize_text_field( $request->get_param( 'title' ) );
		$items = $request->get_param( 'items' );

		$data = array(
			'post_title'  => $title,
			'post_type'   => 'ap_collection',
			'post_status' => 'publish',
		);

		if ( $id ) {
			$data['ID'] = $id;
			$result     = wp_update_post( $data, true );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			$id = $result;
		} else {
			$id = wp_insert_post( $data, true );
			if ( is_wp_error( $id ) ) {
				return $id;
			}
		}

		if ( is_array( $items ) ) {
			update_post_meta( $id, 'ap_collection_items', array_map( 'intval', $items ) );
		}

		return \rest_ensure_response( array( 'id' => $id ) );
	}

	public static function get_collection( WP_REST_Request $request ): WP_REST_Response {
		$id   = intval( $request['id'] );
		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'ap_collection' ) {
			return new WP_REST_Response( array( 'message' => 'Collection not found' ), 404 );
		}

		$item_ids = get_post_meta( $post->ID, 'ap_collection_items', true ) ?: array();
		$items    = array();
		foreach ( $item_ids as $pid ) {
			$p = get_post( $pid );
			if ( ! $p ) {
				continue;
			}
			$items[] = array(
				'type'      => $p->post_type,
				'id'        => $p->ID,
				'title'     => $p->post_title,
				'excerpt'   => get_the_excerpt( $p ),
				'thumbnail' => get_the_post_thumbnail_url( $p, 'thumbnail' ) ?: '',
			);
		}

		$data = array(
			'id'          => $post->ID,
			'title'       => $post->post_title,
			'description' => $post->post_content,
			'thumbnail'   => get_the_post_thumbnail_url( $post, 'thumbnail' ) ?: '',
			'items'       => $items,
		);
		return \rest_ensure_response( $data );
	}
}
