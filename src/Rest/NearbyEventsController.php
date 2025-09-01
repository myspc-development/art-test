<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

/**
 * REST controller providing a simple nearby events query.
 */
class NearbyEventsController {
	use RestResponder;

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/events/nearby' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/events/nearby',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_nearby' ),
					'permission_callback' => Auth::allow_public(),
					'args'                => array(
						'lat'    => array(
							'type'     => 'number',
							'required' => true,
						),
						'lng'    => array(
							'type'     => 'number',
							'required' => true,
						),
						'radius' => array(
							'type'     => 'number',
							'required' => false,
							'default'  => 50,
						),
						'limit'  => array(
							'type'     => 'integer',
							'required' => false,
							'default'  => 20,
						),
					),
				)
			);
		}
	}

	public static function get_nearby( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$cache_key = 'ap_nearby_events_' . get_current_user_id() . '_' . md5( serialize( $request->get_params() ) );
		if ( false !== ( $cached = get_transient( $cache_key ) ) ) {
			return \rest_ensure_response( $cached );
		}

		$lat    = floatval( $request['lat'] );
		$lng    = floatval( $request['lng'] );
		$radius = floatval( $request->get_param( 'radius' ) ); // km
		$limit  = absint( $request->get_param( 'limit' ) );
		if ( $limit < 1 ) {
			$limit = 20;
		}

		$query = new \WP_Query(
			array(
				'post_type'      => 'artpulse_event',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'event_lat',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => 'event_lng',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		$events = array();
		foreach ( $query->posts as $post ) {
			$ev_lat = get_post_meta( $post->ID, 'event_lat', true );
			$ev_lng = get_post_meta( $post->ID, 'event_lng', true );
			if ( $ev_lat === '' || $ev_lng === '' ) {
				continue;
			}
			$dist = self::haversine_distance( $lat, $lng, (float) $ev_lat, (float) $ev_lng );
			if ( $dist <= $radius ) {
				$events[] = array(
					'id'         => $post->ID,
					'title'      => $post->post_title,
					'distance'   => round( $dist, 2 ),
					'link'       => get_permalink( $post ),
					'start_date' => get_post_meta( $post->ID, 'event_start_date', true ),
				);
			}
		}
		usort( $events, static fn( $a, $b ) => ( $a['distance'] <=> $b['distance'] ) );
		$events = array_slice( $events, 0, $limit );
		set_transient( $cache_key, $events, 30 );
		return \rest_ensure_response( $events );
	}

	private static function haversine_distance( float $lat1, float $lng1, float $lat2, float $lng2 ): float {
		$earth = 6371; // km
		$dLat  = deg2rad( $lat2 - $lat1 );
		$dLon  = deg2rad( $lng2 - $lng1 );
		$a     = sin( $dLat / 2 ) * sin( $dLat / 2 ) + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * sin( $dLon / 2 ) * sin( $dLon / 2 );
		$c     = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
		return $earth * $c;
	}
}
