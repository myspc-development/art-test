<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class ArtworkCommentsController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/artwork/(?P<id>\\d+)/comments' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/artwork/(?P<id>\\d+)/comments',
				array(
					array(
						'methods'             => 'GET',
						'callback'            => array( self::class, 'list' ),
						'permission_callback' => '__return_true',
						'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
					),
					array(
						'methods'             => 'POST',
						'callback'            => array( self::class, 'add' ),
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

	public static function list( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$artwork_id = absint( $request['id'] );
		if ( ! $artwork_id || get_post_type( $artwork_id ) !== 'artpulse_artwork' ) {
			return new WP_Error( 'invalid_artwork', 'Invalid artwork.', array( 'status' => 404 ) );
		}

		$comments = get_comments(
			array(
				'post_id' => $artwork_id,
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

	public static function add( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$artwork_id = absint( $request['id'] );
		if ( ! $artwork_id || get_post_type( $artwork_id ) !== 'artpulse_artwork' ) {
			return new WP_Error( 'invalid_artwork', 'Invalid artwork.', array( 'status' => 404 ) );
		}

		$content = sanitize_text_field( $request['content'] );
		if ( $content === '' ) {
			return new WP_Error( 'empty_content', 'Comment content is required.', array( 'status' => 400 ) );
		}

		$user = wp_get_current_user();
		$data = array(
			'comment_post_ID'      => $artwork_id,
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
