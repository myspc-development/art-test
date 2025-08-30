<?php
namespace ArtPulse\Rest;

use ArtPulse\Core\FeedbackManager;
use ArtPulse\Rest\Util\Auth;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class FeedbackRestController {

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/feedback' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/feedback',
				array(
					array(
						'methods'             => 'POST',
						'callback'            => array( self::class, 'submit' ),
                                                'permission_callback' => Auth::require_login_and_cap(null),
					),
					array(
						'methods'             => 'GET',
						'callback'            => array( self::class, 'list' ),
                                                'permission_callback' => Auth::require_login_and_cap(null),
					),
				)
			);
		}
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/feedback/(?P<id>\\d+)/vote' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/feedback/(?P<id>\\d+)/vote',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'vote' ),
                                        'permission_callback' => Auth::require_login_and_cap(null),
					'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
				)
			);
		}
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/feedback/(?P<id>\\d+)/comments' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/feedback/(?P<id>\\d+)/comments',
				array(
					array(
						'methods'             => 'GET',
						'callback'            => array( self::class, 'comments' ),
                                                'permission_callback' => Auth::require_login_and_cap(null),
						'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
					),
					array(
						'methods'             => 'POST',
						'callback'            => array( self::class, 'add_comment' ),
                                                'permission_callback' => Auth::require_login_and_cap(null),
						'args'                => array(
							'id'      => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
							'comment' => array(
								'type'     => 'string',
								'required' => true,
							),
						),
					),
				)
			);
		}
	}

	public static function submit( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$type        = sanitize_text_field( $req['type'] ?? 'general' );
		$description = sanitize_textarea_field( $req['description'] ?? '' );
		$email       = sanitize_email( $req['email'] ?? '' );
		$tags        = sanitize_text_field( $req['tags'] ?? '' );
		$context     = sanitize_text_field( $req['context'] ?? '' );
		if ( $description === '' ) {
			return new WP_Error( 'required', 'Description required.', array( 'status' => 400 ) );
		}
		$user_id = get_current_user_id() ?: null;
		global $wpdb;
		$table = $wpdb->prefix . 'ap_feedback';
		$wpdb->insert(
			$table,
			array(
				'user_id'     => $user_id,
				'type'        => $type,
				'description' => $description,
				'email'       => $email,
				'tags'        => $tags,
				'context'     => $context,
				'created_at'  => current_time( 'mysql' ),
			)
		);
		return rest_ensure_response( array( 'success' => true ) );
	}

	public static function list(): WP_REST_Response {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_feedback';
		$rows  = $wpdb->get_results( "SELECT * FROM $table ORDER BY votes DESC, created_at DESC LIMIT 100", ARRAY_A );
		$voted = array();
		if ( is_user_logged_in() ) {
			$voted = get_user_meta( get_current_user_id(), 'ap_feedback_votes', true );
			if ( ! is_array( $voted ) ) {
				$voted = array();
			}
		}
		$items = array_map(
			static function ( $row ) use ( $voted ) {
				return array(
					'id'          => (int) $row['id'],
					'description' => $row['description'],
					'type'        => $row['type'],
					'votes'       => (int) $row['votes'],
					'status'      => $row['status'],
					'voted'       => in_array( (int) $row['id'], $voted, true ),
				);
			},
			$rows
		);
		return rest_ensure_response( $items );
	}

	public static function vote( WP_REST_Request $req ): WP_REST_Response {
		$id    = absint( $req['id'] );
		$count = FeedbackManager::upvote( $id, get_current_user_id() );
		return rest_ensure_response(
			array(
				'success' => true,
				'votes'   => $count,
			)
		);
	}

	public static function comments( WP_REST_Request $req ): WP_REST_Response {
		$id = absint( $req['id'] );
		return rest_ensure_response( FeedbackManager::get_comments( $id ) );
	}

	public static function add_comment( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$id  = absint( $req['id'] );
		$txt = sanitize_text_field( $req['comment'] );
		if ( $txt === '' ) {
			return new WP_Error( 'empty_comment', 'Comment required.', array( 'status' => 400 ) );
		}
		FeedbackManager::add_comment( $id, get_current_user_id(), $txt );
		return rest_ensure_response( array( 'success' => true ) );
	}
}
