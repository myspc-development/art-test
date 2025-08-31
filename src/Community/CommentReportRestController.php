<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class CommentReportRestController {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/comment/(?P<id>\\d+)/report' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/comment/(?P<id>\\d+)/report',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'report' ),
					'permission_callback' => fn() => is_user_logged_in(),
					'args'                => array(
						'id'     => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
						'reason' => array(
							'type'     => 'string',
							'required' => false,
						),
					),
				)
			);
		}
	}

	public static function report( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$comment_id = absint( $request['id'] );
		$comment    = get_comment( $comment_id );
		if ( ! $comment ) {
			return new WP_Error( 'invalid_comment', 'Comment not found.', array( 'status' => 404 ) );
		}

		$reason  = sanitize_text_field( $request['reason'] ?? '' );
		$user_id = get_current_user_id();
		CommentReports::add_report( $comment_id, $user_id, $reason );

		$count = CommentReports::count_reports( $comment_id );
		if ( $count >= 3 ) {
			update_comment_meta( $comment_id, 'ap_hidden', 1 );
		}

		return \rest_ensure_response(
			array(
				'reported' => true,
				'count'    => $count,
			)
		);
	}
}
