<?php
namespace ArtPulse\Monetization;

/**
 * Provides membership level utilities.
 */
class MembershipManager {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/user/membership',
			array(
				'methods'             => array( 'GET', 'POST' ),
				'callback'            => array( self::class, 'handle' ),
				'permission_callback' => array( self::class, 'check_logged_in' ),
			)
		);
	}

	public static function check_logged_in() {
		if ( ! current_user_can( 'read' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	public static function handle( \WP_REST_Request $req ) {
		$user_id = get_current_user_id();

		if ( $req->get_method() === 'GET' ) {
			$level   = get_user_meta( $user_id, 'ap_membership_level', true ) ?: 'Free';
			$expires = get_user_meta( $user_id, 'ap_membership_expires', true );

			return \rest_ensure_response(
				array(
					'level'   => $level,
					'expires' => $expires ? intval( $expires ) : 0,
				)
			);
		}

		$level   = sanitize_text_field( $req->get_param( 'level' ) );
		$expires = absint( $req->get_param( 'expires' ) );

		if ( ! $level ) {
			return new \WP_Error( 'invalid_level', 'Invalid membership level.', array( 'status' => 400 ) );
		}

		update_user_meta( $user_id, 'ap_membership_level', $level );
		if ( $expires ) {
			update_user_meta( $user_id, 'ap_membership_expires', $expires );
		} else {
			delete_user_meta( $user_id, 'ap_membership_expires' );
		}

		do_action( 'artpulse_membership_updated', $user_id, $level, $expires );

		return \rest_ensure_response(
			array(
				'level'   => $level,
				'expires' => $expires,
			)
		);
	}
}
