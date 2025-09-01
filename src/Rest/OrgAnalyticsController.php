<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class OrgAnalyticsController {
	use RestResponder;
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/org-metrics' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/org-metrics',
				array(
                                        'methods'             => 'GET',
                                        'callback'            => array( self::class, 'get_metrics' ),
                                        'permission_callback' => Auth::require_login_and_cap( null ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\d+)/rsvp-stats' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\d+)/rsvp-stats',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_event_rsvp_stats' ),
					'permission_callback' => array( \ArtPulse\Rest\RsvpRestController::class, 'check_permissions' ),
					'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
				)
			);
		}
	}

	public static function get_metrics( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$user_id = get_current_user_id();
		$org_id  = get_user_meta( $user_id, 'ap_organization_id', true );
		if ( ! $org_id ) {
			return \rest_ensure_response( array() );
		}

		$key  = 'ap_org_metrics_' . $org_id;
		$data = get_transient( $key );
		if ( $data === false ) {
			$event_query = new \WP_Query(
				array(
					'post_type'     => 'artpulse_event',
					'post_status'   => array( 'publish', 'pending', 'draft' ),
					'fields'        => 'ids',
					'no_found_rows' => true,
					'meta_key'      => '_ap_event_organization',
					'meta_value'    => $org_id,
				)
			);
			$event_count = count( $event_query->posts );

			$artwork_query = new \WP_Query(
				array(
					'post_type'     => 'artpulse_artwork',
					'post_status'   => array( 'publish', 'pending', 'draft' ),
					'fields'        => 'ids',
					'no_found_rows' => true,
					'meta_query'    => array(
						array(
							'key'   => 'org_id',
							'value' => $org_id,
						),
					),
				)
			);
			$artwork_count = count( $artwork_query->posts );

			$data = array(
				'event_count'   => $event_count,
				'artwork_count' => $artwork_count,
			);
			set_transient( $key, $data, MINUTE_IN_SECONDS * 15 );
		}

		return \rest_ensure_response( $data );
	}

	public static function get_event_rsvp_stats( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$event_id = absint( $request->get_param( 'id' ) );
		$history  = get_post_meta( $event_id, 'event_rsvp_history', true );
		if ( ! is_array( $history ) ) {
			$history = array();
		}
		ksort( $history );
		$favorites   = (int) get_post_meta( $event_id, 'ap_favorite_count', true );
		$waitlist    = get_post_meta( $event_id, 'event_waitlist', true );
		$attended    = get_post_meta( $event_id, 'event_attended', true );
		$waitlist_ct = is_array( $waitlist ) ? count( $waitlist ) : 0;
		$attended_ct = is_array( $attended ) ? count( $attended ) : 0;
		return \rest_ensure_response(
			array(
				'dates'       => array_keys( $history ),
				'counts'      => array_values( $history ),
				'views'       => (int) get_post_meta( $event_id, 'view_count', true ),
				'total_rsvps' => array_sum( $history ),
				'favorites'   => $favorites,
				'waitlist'    => $waitlist_ct,
				'attended'    => $attended_ct,
			)
		);
	}
}
