<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class RsvpRestController {
	use RestResponder;

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/rsvp' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/rsvp',
				array(
                                        'methods'             => 'POST',
                                        'callback'            => array( self::class, 'join' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
					'args'                => array(
						'event_id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/rsvp/cancel' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/rsvp/cancel',
				array(
                                        'methods'             => 'POST',
                                        'callback'            => array( self::class, 'cancel' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
					'args'                => array(
						'event_id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/waitlist/remove' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/waitlist/remove',
				array(
                                        'methods'             => 'POST',
                                        'callback'            => array( self::class, 'remove_waitlist' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
					'args'                => array(
						'event_id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\d+)/attendees' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\d+)/attendees',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_attendees' ),
					'permission_callback' => array( self::class, 'check_permissions' ),
					'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\d+)/attendees/export' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\d+)/attendees/export',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'export_attendees' ),
					'permission_callback' => array( self::class, 'check_permissions' ),
					'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<event_id>\d+)/attendees/(?P<user_id>\d+)/attended' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<event_id>\d+)/attendees/(?P<user_id>\d+)/attended',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'toggle_attended' ),
					'permission_callback' => array( self::class, 'check_permissions' ),
					'args'                => array(
						'event_id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
						'user_id'  => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<event_id>\d+)/attendees/(?P<user_id>\d+)/remove' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<event_id>\d+)/attendees/(?P<user_id>\d+)/remove',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'remove_attendee' ),
					'permission_callback' => array( self::class, 'check_permissions' ),
					'args'                => array(
						'event_id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
						'user_id'  => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<event_id>\d+)/email-rsvps' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<event_id>\d+)/email-rsvps',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'email_rsvps' ),
					'permission_callback' => array( self::class, 'check_permissions' ),
					'args'                => array(
						'event_id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<event_id>\d+)/attendees/(?P<user_id>\d+)/message' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<event_id>\d+)/attendees/(?P<user_id>\d+)/message',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'email_attendee' ),
					'permission_callback' => array( self::class, 'check_permissions' ),
					'args'                => array(
						'event_id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
						'user_id'  => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
					),
				)
			);
		}
	}

	protected static function get_lists( int $event_id ): array {
		$rsvps    = get_post_meta( $event_id, 'event_rsvp_list', true );
		$waitlist = get_post_meta( $event_id, 'event_waitlist', true );
		return array(
			'rsvps'    => is_array( $rsvps ) ? $rsvps : array(),
			'waitlist' => is_array( $waitlist ) ? $waitlist : array(),
		);
	}

	protected static function store_lists( int $event_id, array $rsvps, array $waitlist ): void {
		update_post_meta( $event_id, 'event_rsvp_list', array_values( $rsvps ) );
		update_post_meta( $event_id, 'event_waitlist', array_values( $waitlist ) );
	}

	/**
	 * Record a signup for analytics purposes.
	 */
	protected static function log_rsvp( int $event_id ): void {
		$history = get_post_meta( $event_id, 'event_rsvp_history', true );
		if ( ! is_array( $history ) ) {
			$history = array();
		}
		$date = current_time( 'Y-m-d' );
		if ( isset( $history[ $date ] ) ) {
			++$history[ $date ];
		} else {
			$history[ $date ] = 1;
		}
		update_post_meta( $event_id, 'event_rsvp_history', $history );
	}

	protected static function validate_event( int $event_id ): bool {
		return $event_id && get_post_type( $event_id ) === 'artpulse_event';
	}

	public static function join( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_not_logged_in', 'Authentication required.', array( 'status' => 401 ) );
		}
		$event_id = absint( $request->get_param( 'event_id' ) );
		if ( ! self::validate_event( $event_id ) ) {
			return new WP_Error( 'invalid_event', 'Invalid event.', array( 'status' => 400 ) );
		}

		// Ensure RSVPs are enabled
		$rsvp_enabled = get_post_meta( $event_id, 'event_rsvp_enabled', true );
		if ( $rsvp_enabled !== '1' ) {
			return new WP_Error( 'rsvp_disabled', 'RSVPs are disabled for this event.', array( 'status' => 400 ) );
		}

		$user_id = get_current_user_id();

		['rsvps' => $rsvps, 'waitlist' => $waitlist] = self::get_lists( $event_id );
		$rsvp_data                                   = get_post_meta( $event_id, 'event_rsvp_data', true );
		if ( ! is_array( $rsvp_data ) ) {
			$rsvp_data = array();
		}

		// Remove from both lists first
		$rsvps    = array_values( array_diff( $rsvps, array( $user_id ) ) );
		$waitlist = array_values( array_diff( $waitlist, array( $user_id ) ) );

		$limit            = intval( get_post_meta( $event_id, 'event_rsvp_limit', true ) );
		$waitlist_enabled = get_post_meta( $event_id, 'event_waitlist_enabled', true ) === '1';

		if ( $limit && count( $rsvps ) >= $limit ) {
			if ( ! $waitlist_enabled ) {
				return new WP_Error( 'event_full', 'Event has reached RSVP capacity.', array( 'status' => 400 ) );
			}
			if ( ! in_array( $user_id, $waitlist, true ) ) {
				$waitlist[] = $user_id;
			}
		} elseif ( ! in_array( $user_id, $rsvps, true ) ) {
				$rsvps[] = $user_id;
		}

		$rsvp_data[ $user_id ] = array(
			'date' => current_time( 'mysql' ),
		);
		update_post_meta( $event_id, 'event_rsvp_data', $rsvp_data );

		self::store_lists( $event_id, $rsvps, $waitlist );
		do_action( 'ap_event_rsvp_added', $event_id, get_current_user_id() );
		self::log_rsvp( $event_id );
		\ArtPulse\Core\UserEngagementLogger::log( $user_id, 'rsvp', $event_id );
		\ArtPulse\Personalization\RecommendationEngine::log( $user_id, 'event', $event_id, 'rsvp' );

		$events = get_user_meta( $user_id, 'ap_rsvp_events', true );
		if ( ! is_array( $events ) ) {
			$events = array();
		}
		if ( ! in_array( $event_id, $events, true ) ) {
			$events[] = $event_id;
			update_user_meta( $user_id, 'ap_rsvp_events', $events );
		}

		$user    = wp_get_current_user();
		$subject = sprintf( __( 'RSVP Confirmation for "%s"', 'artpulse' ), get_the_title( $event_id ) );
		$message = sprintf( __( 'Hi %1$s,\n\nYou have successfully RSVPed for "%2$s".', 'artpulse' ), $user->display_name, get_the_title( $event_id ) );
		if ( $user && is_email( $user->user_email ) ) {
			\ArtPulse\Core\EmailService::send( $user->user_email, $subject, $message );
		}

		$org_email = get_post_meta( $event_id, 'event_organizer_email', true );
		if ( $org_email && is_email( $org_email ) ) {
			$org_subject = sprintf( __( 'New RSVP for "%s"', 'artpulse' ), get_the_title( $event_id ) );
			$org_message = sprintf( __( '%1$s (%2$s) just RSVPed.', 'artpulse' ), $user->display_name, $user->user_email );
			\ArtPulse\Core\EmailService::send( $org_email, $org_subject, $org_message );
		}

		return \rest_ensure_response(
			array(
				'rsvp_count'     => count( $rsvps ),
				'waitlist_count' => count( $waitlist ),
			)
		);
	}

	public static function cancel( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_not_logged_in', 'Authentication required.', array( 'status' => 401 ) );
		}
		$event_id = absint( $request->get_param( 'event_id' ) );
		if ( ! self::validate_event( $event_id ) ) {
			return new WP_Error( 'invalid_event', 'Invalid event.', array( 'status' => 400 ) );
		}

		$user_id                                     = get_current_user_id();
		['rsvps' => $rsvps, 'waitlist' => $waitlist] = self::get_lists( $event_id );

		$rsvps    = array_values( array_diff( $rsvps, array( $user_id ) ) );
		$waitlist = array_values( array_diff( $waitlist, array( $user_id ) ) );

		$limit = intval( get_post_meta( $event_id, 'event_rsvp_limit', true ) );
		if ( ( $limit === 0 || count( $rsvps ) < $limit ) && ! empty( $waitlist ) ) {
			$promote = array_shift( $waitlist );
			$rsvps[] = $promote;
		}

		self::store_lists( $event_id, $rsvps, $waitlist );

		$events = get_user_meta( $user_id, 'ap_rsvp_events', true );
		if ( is_array( $events ) ) {
			$events = array_values( array_diff( $events, array( $event_id ) ) );
			update_user_meta( $user_id, 'ap_rsvp_events', $events );
		}

		return \rest_ensure_response(
			array(
				'rsvp_count'     => count( $rsvps ),
				'waitlist_count' => count( $waitlist ),
			)
		);
	}

	public static function remove_waitlist( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$event_id = absint( $request->get_param( 'event_id' ) );
		if ( ! self::validate_event( $event_id ) ) {
			return new WP_Error( 'invalid_event', 'Invalid event.', array( 'status' => 400 ) );
		}

		$user_id                                     = get_current_user_id();
		['rsvps' => $rsvps, 'waitlist' => $waitlist] = self::get_lists( $event_id );

		$waitlist = array_values( array_diff( $waitlist, array( $user_id ) ) );

		self::store_lists( $event_id, $rsvps, $waitlist );

		return \rest_ensure_response(
			array(
				'rsvp_count'     => count( $rsvps ),
				'waitlist_count' => count( $waitlist ),
			)
		);
	}

	public static function check_permissions( WP_REST_Request $request ) {
		$event_id = absint( $request->get_param( 'id' ) ?: $request->get_param( 'event_id' ) );
		if ( ! $event_id ) {
			return new WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
		}
		$user_id   = get_current_user_id();
		$user_org  = intval( get_user_meta( $user_id, 'ap_organization_id', true ) );
		$event_org = intval( get_post_meta( $event_id, '_ap_event_organization', true ) );
		if ( $user_org && $event_org && $user_org === $event_org && current_user_can( 'view_artpulse_dashboard' ) ) {
			return true;
		}
		return new WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
	}

	public static function get_attendees( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$event_id = absint( $request->get_param( 'id' ) );
		if ( ! current_user_can( 'edit_post', $event_id ) ) {
			return new WP_Error( 'rest_forbidden', 'Insufficient permissions.', array( 'status' => 403 ) );
		}
		['rsvps' => $rsvps, 'waitlist' => $waitlist] = self::get_lists( $event_id );
		$attended                                    = get_post_meta( $event_id, 'event_attended', true );
		if ( ! is_array( $attended ) ) {
			$attended = array();
		}
		$rsvp_data = get_post_meta( $event_id, 'event_rsvp_data', true );
		if ( ! is_array( $rsvp_data ) ) {
			$rsvp_data = array();
		}

		$attendees = array();
		foreach ( $rsvps as $uid ) {
			$user = get_userdata( $uid );
			if ( ! $user ) {
				continue;
			}
			$attendees[] = array(
				'ID'        => $uid,
				'name'      => $user->display_name,
				'email'     => $user->user_email,
				'status'    => 'confirmed',
				'rsvp_date' => $rsvp_data[ $uid ]['date'] ?? '',
				'attended'  => in_array( $uid, $attended, true ),
			);
		}

		$wl = array();
		foreach ( $waitlist as $uid ) {
			$user = get_userdata( $uid );
			if ( ! $user ) {
				continue;
			}
			$wl[] = array(
				'ID'        => $uid,
				'name'      => $user->display_name,
				'email'     => $user->user_email,
				'status'    => 'waitlist',
				'rsvp_date' => $rsvp_data[ $uid ]['date'] ?? '',
				'attended'  => in_array( $uid, $attended, true ),
			);
		}

		return \rest_ensure_response(
			array(
				'attendees' => $attendees,
				'waitlist'  => $wl,
			)
		);
	}

	public static function export_attendees( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$event_id = absint( $request->get_param( 'id' ) );
		if ( ! current_user_can( 'edit_post', $event_id ) ) {
			return new WP_Error( 'rest_forbidden', 'Insufficient permissions.', array( 'status' => 403 ) );
		}
		$data = self::get_attendees( $request )->get_data();

		$rows   = array_merge( $data['attendees'], $data['waitlist'] );
		$stream = fopen( 'php://temp', 'w' );
		fputcsv( $stream, array( 'Name', 'Email', 'Status', 'RSVP Date', 'Attended' ) );
		foreach ( $rows as $row ) {
			fputcsv(
				$stream,
				array(
					$row['name'] ?? '',
					$row['email'],
					$row['status'],
					$row['rsvp_date'] ?? '',
					$row['attended'] ? 'Yes' : 'No',
				)
			);
		}
		rewind( $stream );
		$csv = stream_get_contents( $stream );
		fclose( $stream );

		return new WP_REST_Response(
			$csv,
			200,
			array(
				'Content-Type'        => 'text/csv',
				'Content-Disposition' => 'attachment; filename="attendees.csv"',
			)
		);
	}

	public static function toggle_attended( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$event_id = absint( $request->get_param( 'event_id' ) );
		$user_id  = absint( $request->get_param( 'user_id' ) );
		$attended = get_post_meta( $event_id, 'event_attended', true );
		if ( ! is_array( $attended ) ) {
			$attended = array();
		}
		if ( in_array( $user_id, $attended, true ) ) {
			$attended = array_values( array_diff( $attended, array( $user_id ) ) );
			$status   = false;
		} else {
			$attended[] = $user_id;
			$status     = true;
		}
		update_post_meta( $event_id, 'event_attended', $attended );
		return \rest_ensure_response( array( 'attended' => $status ) );
	}

	public static function remove_attendee( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$event_id = absint( $request->get_param( 'event_id' ) );
		$user_id  = absint( $request->get_param( 'user_id' ) );
		if ( ! self::validate_event( $event_id ) ) {
			return new WP_Error( 'invalid_event', 'Invalid event.', array( 'status' => 400 ) );
		}

		['rsvps' => $rsvps, 'waitlist' => $waitlist] = self::get_lists( $event_id );
		$attended                                    = get_post_meta( $event_id, 'event_attended', true );
		if ( ! is_array( $attended ) ) {
			$attended = array();
		}

		$rsvps    = array_values( array_diff( $rsvps, array( $user_id ) ) );
		$waitlist = array_values( array_diff( $waitlist, array( $user_id ) ) );
		$attended = array_values( array_diff( $attended, array( $user_id ) ) );

		$limit = intval( get_post_meta( $event_id, 'event_rsvp_limit', true ) );
		if ( ( $limit === 0 || count( $rsvps ) < $limit ) && ! empty( $waitlist ) ) {
			$promote = array_shift( $waitlist );
			$rsvps[] = $promote;
		}

		update_post_meta( $event_id, 'event_attended', $attended );
		self::store_lists( $event_id, $rsvps, $waitlist );

		return \rest_ensure_response(
			array(
				'rsvp_count'     => count( $rsvps ),
				'waitlist_count' => count( $waitlist ),
			)
		);
	}

	public static function email_rsvps( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$event_id = absint( $request->get_param( 'event_id' ) );
		if ( ! self::validate_event( $event_id ) ) {
			return new WP_Error( 'invalid_event', 'Invalid event.', array( 'status' => 400 ) );
		}

		$subject = sanitize_text_field( $request->get_param( 'subject' ) );
		if ( ! $subject ) {
			$subject = sprintf( __( 'Reminder for "%s"', 'artpulse' ), get_the_title( $event_id ) );
		}
		$message = sanitize_textarea_field( $request->get_param( 'message' ) );
		if ( ! $message ) {
			$message = __( 'This is a reminder for your upcoming event.', 'artpulse' );
		}

		['rsvps' => $rsvps] = self::get_lists( $event_id );
		$sent               = array();
		foreach ( $rsvps as $uid ) {
			$user = get_userdata( $uid );
			if ( $user && is_email( $user->user_email ) ) {
				\ArtPulse\Core\EmailService::send( $user->user_email, $subject, $message );
				$sent[] = $user->user_email;
			}
		}

		return \rest_ensure_response( array( 'sent' => $sent ) );
	}

	public static function email_attendee( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$event_id = absint( $request->get_param( 'event_id' ) );
		$user_id  = absint( $request->get_param( 'user_id' ) );
		if ( ! self::validate_event( $event_id ) || ! $user_id ) {
			return new WP_Error( 'invalid_params', 'Invalid parameters.', array( 'status' => 400 ) );
		}

		$subject = sanitize_text_field( $request->get_param( 'subject' ) );
		if ( ! $subject ) {
			$subject = sprintf( __( 'Message regarding "%s"', 'artpulse' ), get_the_title( $event_id ) );
		}
		$message = sanitize_textarea_field( $request->get_param( 'message' ) );
		if ( ! $message ) {
			$message = __( 'Hello from your event organizer.', 'artpulse' );
		}

		$user = get_userdata( $user_id );
		if ( ! $user || ! is_email( $user->user_email ) ) {
			return new WP_Error( 'invalid_user', 'Invalid user.', array( 'status' => 404 ) );
		}

		\ArtPulse\Core\EmailService::send( $user->user_email, $subject, $message );

		return \rest_ensure_response( array( 'sent' => array( $user->user_email ) ) );
	}
}
