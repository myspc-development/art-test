<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use ArtPulse\Community\CommunityRoles;

class ForumRestController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/forum/threads' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/forum/threads',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( self::class, 'list_threads' ),
						'permission_callback' => fn() => is_user_logged_in(),
					),
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( self::class, 'create_thread' ),
						'permission_callback' => fn() => CommunityRoles::can_post_thread( get_current_user_id() ),
						'args'                => array(
							'title'   => array(
								'type'     => 'string',
								'required' => true,
							),
							'content' => array(
								'type'     => 'string',
								'required' => false,
							),
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/forum/thread/(?P<id>\\d+)/comments' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/forum/thread/(?P<id>\\d+)/comments',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( self::class, 'get_comments' ),
						'permission_callback' => fn() => is_user_logged_in(),
						'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
					),
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( self::class, 'add_comment' ),
						'permission_callback' => fn() => is_user_logged_in(),
						'args'                => array(
							'id'      => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
							'content' => array(
								'type'     => 'string',
								'required' => true,
							),
						),
					),
				)
			);
		}
	}

	public static function list_threads( WP_REST_Request $request ): WP_REST_Response {
		$posts = get_posts(
			array(
				'post_type'      => 'ap_forum_thread',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
			)
		);

		$data = array_map(
			function ( $id ) {
				$p = get_post( $id );
				if ( ! $p ) {
					return null;
				}
				return array(
					'id'      => $p->ID,
					'title'   => $p->post_title,
					'author'  => get_the_author_meta( 'display_name', $p->post_author ),
					'date'    => $p->post_date,
					'excerpt' => wp_trim_words( $p->post_content, 55 ),
				);
			},
			$posts
		);

		return \rest_ensure_response( array_values( array_filter( $data ) ) );
	}

	public static function create_thread( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$title   = sanitize_text_field( $request['title'] );
		$content = wp_kses_post( $request['content'] ?? '' );

		$post_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_type'    => 'ap_forum_thread',
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id(),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		return \rest_ensure_response( array( 'id' => $post_id ) );
	}

	public static function get_comments( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$thread_id = absint( $request['id'] );
		if ( ! $thread_id || get_post_type( $thread_id ) !== 'ap_forum_thread' ) {
			return new WP_Error( 'invalid_thread', 'Invalid thread.', array( 'status' => 404 ) );
		}

		$comments = get_comments(
			array(
				'post_id' => $thread_id,
				'status'  => 'approve',
			)
		);

		$data = array_map(
			function ( $c ) {
				if ( get_comment_meta( $c->comment_ID, 'ap_hidden', true ) ) {
					return null;
				}
				return array(
					'id'      => $c->comment_ID,
					'author'  => $c->comment_author,
					'content' => $c->comment_content,
					'date'    => $c->comment_date,
				);
			},
			$comments
		);

		return \rest_ensure_response( array_values( array_filter( $data ) ) );
	}

	public static function add_comment( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$thread_id = absint( $request['id'] );
		if ( ! $thread_id || get_post_type( $thread_id ) !== 'ap_forum_thread' ) {
			return new WP_Error( 'invalid_thread', 'Invalid thread.', array( 'status' => 404 ) );
		}

		$content = sanitize_text_field( $request['content'] );
		if ( $content === '' ) {
			return new WP_Error( 'empty_content', 'Comment content is required.', array( 'status' => 400 ) );
		}

		$user = wp_get_current_user();
		$data = array(
			'comment_post_ID'      => $thread_id,
			'comment_content'      => $content,
			'user_id'              => $user->ID,
			'comment_author'       => $user->display_name,
			'comment_author_email' => $user->user_email,
			'comment_approved'     => 0,
		);

		$comment_id = wp_insert_comment( $data );
		if ( ! $comment_id ) {
			return new WP_Error( 'create_failed', 'Unable to add comment.', array( 'status' => 500 ) );
		}

		return \rest_ensure_response(
			array(
				'id'     => $comment_id,
				'status' => 'pending',
			)
		);
	}
}
