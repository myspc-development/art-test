<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Registers REST API routes used by dashboard widgets.
 */
class ArtPulse_REST_Controller {
	public static function register() {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes() {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\\d+)/rsvp' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\\d+)/rsvp',
				array(
					array(
						'methods'             => 'POST',
						'callback'            => array( self::class, 'rsvp_event' ),
						'permission_callback' => fn() => current_user_can( 'read' ),
						'args'                => array(
							'id' => array(
								'type'              => 'integer',
								'required'          => true,
								'sanitize_callback' => 'absint',
								'validate_callback' => 'is_numeric',
							),
						),
					),
					array(
						'methods'             => 'DELETE',
						'callback'            => array( self::class, 'unrsvp_event' ),
						'permission_callback' => fn() => current_user_can( 'read' ),
						'args'                => array(
							'id' => array(
								'type'              => 'integer',
								'required'          => true,
								'sanitize_callback' => 'absint',
								'validate_callback' => 'is_numeric',
							),
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/user/(?P<id>\\d+)/follow' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/user/(?P<id>\\d+)/follow',
				array(
					array(
						'methods'             => 'POST',
						'callback'            => array( self::class, 'follow_user' ),
						'permission_callback' => fn() => current_user_can( 'read' ),
						'args'                => array(
							'id' => array(
								'type'              => 'integer',
								'required'          => true,
								'sanitize_callback' => 'absint',
								'validate_callback' => 'is_numeric',
							),
						),
					),
					array(
						'methods'             => 'DELETE',
						'callback'            => array( self::class, 'unfollow_user' ),
						'permission_callback' => fn() => current_user_can( 'read' ),
						'args'                => array(
							'id' => array(
								'type'              => 'integer',
								'required'          => true,
								'sanitize_callback' => 'absint',
								'validate_callback' => 'is_numeric',
							),
						),
					),
				)
			);
		}
	}

	public static function rsvp_event( WP_REST_Request $request ) {
		$id = absint( $request['id'] );
		if ( ! $id || get_post_type( $id ) !== 'artpulse_event' ) {
			return new WP_Error( 'invalid_event', __( 'Invalid event.', 'artpulse' ), array( 'status' => 400 ) );
		}
		$user_id = get_current_user_id();
		$events  = get_user_meta( $user_id, 'ap_rsvp_events', true );
		if ( ! is_array( $events ) ) {
			$events = array();
		}
		if ( ! in_array( $id, $events, true ) ) {
			$events[] = $id;
			update_user_meta( $user_id, 'ap_rsvp_events', $events );
		}

		return rest_ensure_response(
			array(
				'event_id' => $id,
				'status'   => 'rsvped',
			)
		);
	}

	public static function unrsvp_event( WP_REST_Request $request ) {
		$id = absint( $request['id'] );
		if ( ! $id || get_post_type( $id ) !== 'artpulse_event' ) {
			return new WP_Error( 'invalid_event', __( 'Invalid event.', 'artpulse' ), array( 'status' => 400 ) );
		}
		$user_id = get_current_user_id();
		$events  = get_user_meta( $user_id, 'ap_rsvp_events', true );
		if ( is_array( $events ) ) {
			$events = array_diff( $events, array( $id ) );
			update_user_meta( $user_id, 'ap_rsvp_events', $events );
		}

		return rest_ensure_response(
			array(
				'event_id' => $id,
				'status'   => 'cancelled',
			)
		);
	}

	public static function follow_user( WP_REST_Request $request ) {
		$id   = absint( $request['id'] );
		$user = get_user_by( 'id', $id );
		if ( ! $user ) {
			return new WP_Error( 'invalid_user', __( 'Invalid user.', 'artpulse' ), array( 'status' => 404 ) );
		}
		$current   = get_current_user_id();
		$following = get_user_meta( $current, 'ap_following', true );
		if ( ! is_array( $following ) ) {
			$following = array();
		}
		if ( ! in_array( $id, $following, true ) ) {
			$following[] = $id;
			update_user_meta( $current, 'ap_following', $following );
		}

		return rest_ensure_response(
			array(
				'user_id' => $id,
				'status'  => 'following',
			)
		);
	}

	public static function unfollow_user( WP_REST_Request $request ) {
		$id   = absint( $request['id'] );
		$user = get_user_by( 'id', $id );
		if ( ! $user ) {
			return new WP_Error( 'invalid_user', __( 'Invalid user.', 'artpulse' ), array( 'status' => 404 ) );
		}
		$current   = get_current_user_id();
		$following = get_user_meta( $current, 'ap_following', true );
		if ( is_array( $following ) ) {
			$following = array_diff( $following, array( $id ) );
			update_user_meta( $current, 'ap_following', $following );
		}

		return rest_ensure_response(
			array(
				'user_id' => $id,
				'status'  => 'unfollowed',
			)
		);
	}
}

ArtPulse_REST_Controller::register();
