<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Core\VisitTracker;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class VisitRestController {
	use RestResponder;

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/checkin' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/checkin',
				array(
                                        'methods'             => 'POST',
                                        'callback'            => array( self::class, 'checkin' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
					'args'                => array(
						'event_id'    => array(
							'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ),
							'required'          => true,
						),
						'institution' => array( 'sanitize_callback' => 'sanitize_text_field' ),
						'group_size'  => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\d+)/visits' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\d+)/visits',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'list' ),
					'permission_callback' => array( \ArtPulse\Rest\RsvpRestController::class, 'check_permissions' ),
					'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\d+)/visits/export' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\d+)/visits/export',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'export' ),
					'permission_callback' => array( \ArtPulse\Rest\RsvpRestController::class, 'check_permissions' ),
					'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
				)
			);
		}
	}

	public static function checkin( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$event_id = absint( $req->get_param( 'event_id' ) );
		if ( ! $event_id || get_post_type( $event_id ) !== 'artpulse_event' ) {
			return new WP_Error( 'invalid_event', __( 'Invalid event.', 'artpulse' ), array( 'status' => 400 ) );
		}
		$institution = sanitize_text_field( $req->get_param( 'institution' ) ?: '' );
		$group_size  = absint( $req->get_param( 'group_size' ) ?: 1 );
		$user_id     = get_current_user_id();
		VisitTracker::record( $event_id, $user_id, $institution, $group_size );
		return \rest_ensure_response( array( 'success' => true ) );
	}

	public static function list( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$id   = absint( $req['id'] );
		$rows = VisitTracker::get_visits( $id );
		return \rest_ensure_response( $rows );
	}

	public static function export( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$id     = absint( $req['id'] );
		$rows   = VisitTracker::get_visits( $id );
		$stream = fopen( 'php://temp', 'w' );
		fputcsv( $stream, array( 'institution', 'group_size', 'user_id', 'visit_date' ) );
		foreach ( $rows as $row ) {
			fputcsv(
				$stream,
				array(
					$row['institution'],
					$row['group_size'],
					$row['user_id'],
					$row['visit_date'],
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
				'Content-Disposition' => 'attachment; filename="visits.csv"',
			)
		);
	}
}
