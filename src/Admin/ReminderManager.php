<?php
namespace ArtPulse\Admin;

/**
 * Schedules and sends event reminders.
 */
class ReminderManager {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		add_action( 'artpulse_send_reminder', array( self::class, 'send_reminder' ), 10, 2 );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/admin/reminders' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/admin/reminders',
				array(
					'methods'             => array( 'GET', 'POST' ),
					'callback'            => array( self::class, 'handle' ),
					'permission_callback' => array( self::class, 'check_permission' ),
				)
			);
		}
	}

	public static function check_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	public static function handle( \WP_REST_Request $request ) {
		$reminders = get_option( 'ap_event_reminders', array() );

		if ( $request->get_method() === 'GET' ) {
			return \rest_ensure_response( array_values( $reminders ) );
		}

		$event_id = absint( $request->get_param( 'event_id' ) );
		$time     = absint( $request->get_param( 'time' ) );
		$message  = sanitize_text_field( $request->get_param( 'message' ) );

		if ( ! $event_id || ! $time || ! $message ) {
			return new \WP_Error( 'invalid_params', 'Missing parameters.', array( 'status' => 400 ) );
		}

		$reminders[] = array(
			'event_id' => $event_id,
			'time'     => $time,
			'message'  => $message,
		);
		update_option( 'ap_event_reminders', $reminders );

		wp_schedule_single_event( $time, 'artpulse_send_reminder', array( $event_id, $message ) );

		return \rest_ensure_response( array( 'scheduled' => true ) );
	}

	public static function send_reminder( int $event_id, string $message ): void {
		$rsvps = get_post_meta( $event_id, 'event_rsvp_list', true );
		if ( ! is_array( $rsvps ) ) {
			$rsvps = array();
		}

		foreach ( $rsvps as $uid ) {
			$user = get_userdata( $uid );
			if ( $user && is_email( $user->user_email ) ) {
				\ArtPulse\Core\EmailService::send(
					$user->user_email,
					__( 'Event Reminder', 'artpulse' ),
					$message
				);
			}
		}
	}
}
