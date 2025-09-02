<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class CalendarFeedController {
	use RestResponder;

	/** @var string REST namespace */
	private const NAMESPACE = 'ap/v1';

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( self::NAMESPACE, '/calendar' ) ) {
			register_rest_route(
				self::NAMESPACE,
				'/calendar',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_feed' ),
					'permission_callback' => Auth::allow_public(),
				)
			);
		}
	}

        public static function get_feed( WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
                $responder = new self();
		$lat    = $req->get_param( 'lat' );
		$lng    = $req->get_param( 'lng' );
		$radius = $req->get_param( 'radius_km' );
		$start  = $req->get_param( 'start' );
		$end    = $req->get_param( 'end' );

		$cache_key = 'ap_cal_' . md5(
			json_encode(
				array(
					round( (float) $lat, 2 ),
					round( (float) $lng, 2 ),
					(float) $radius,
					$start,
					$end,
				)
			)
		);

                $cached = get_transient( $cache_key );
                if ( $cached !== false ) {
                        return $responder->ok( $cached );
                }

                $events = \ArtPulse\Util\ap_fetch_calendar_events( $lat, $lng, $radius, $start, $end );
                set_transient( $cache_key, $events, MINUTE_IN_SECONDS * 10 );
                return $responder->ok( $events );
        }
}
