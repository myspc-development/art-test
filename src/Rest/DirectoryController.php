<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use ArtPulse\Rest\Util\Auth;

class DirectoryController {

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

        public static function register_routes(): void {

                register_rest_route(
                        ARTPULSE_API_NAMESPACE,
                        '/events',
                        array(
                                'methods'             => WP_REST_Server::READABLE,
                                'callback'            => array( self::class, 'get_events' ),
                                'permission_callback' => '__return_true',
                        )
                );
        }


public static function get_events( WP_REST_Request $request ): WP_REST_Response|WP_Error {
$meta_query = array( 'relation' => 'AND' );

foreach ( array( 'genre', 'medium', 'vibe', 'accessibility', 'age_range' ) as $taxonomy ) {
$val = $request->get_param( $taxonomy );
if ( ! empty( $val ) ) {
$meta_query[] = array(
'key'     => $taxonomy,
'value'   => (array) $val,
'compare' => 'IN',
);
}
}

$lat       = $request->get_param( 'lat' );
$lng       = $request->get_param( 'lng' );
$within_km = $request->get_param( 'within_km' );
if ( $lat && $lng && $within_km ) {
$lat       = (float) $lat;
$lng       = (float) $lng;
$within_km = (float) $within_km;
// Approximate bounding box filter to narrow query results.
$lat_delta = $within_km / 111.045; // km per degree lat.
$min_lat   = $lat - $lat_delta;
$max_lat   = $lat + $lat_delta;
$lng_delta = $within_km / ( 111.045 * cos( deg2rad( $lat ) ) );
$min_lng   = $lng - $lng_delta;
$max_lng   = $lng + $lng_delta;

$meta_query[] = array(
'key'     => 'event_lat',
'value'   => array( $min_lat, $max_lat ),
'compare' => 'BETWEEN',
'type'    => 'DECIMAL',
);
$meta_query[] = array(
'key'     => 'event_lng',
'value'   => array( $min_lng, $max_lng ),
'compare' => 'BETWEEN',
'type'    => 'DECIMAL',
);
}

$args = array(
'post_type'      => 'artpulse_event',
'post_status'    => 'publish',
'posts_per_page' => -1,
'orderby'        => 'meta_value',
'meta_key'       => 'event_start_date',
'order'          => 'ASC',
);
if ( count( $meta_query ) > 1 ) {
$args['meta_query'] = $meta_query;
}
$query = new \WP_Query( $args );

$events = array();
foreach ( $query->posts as $post ) {
$event = array(
'id'                   => $post->ID,
'title'                => $post->post_title,
'link'                 => get_permalink( $post ),
'event_start_date'     => get_post_meta( $post->ID, 'event_start_date', true ),
'event_end_date'       => get_post_meta( $post->ID, 'event_end_date', true ),
'event_lat'            => get_post_meta( $post->ID, 'event_lat', true ),
'event_lng'            => get_post_meta( $post->ID, 'event_lng', true ),
'event_street_address' => get_post_meta( $post->ID, 'event_street_address', true ),
'genre'                => get_post_meta( $post->ID, 'genre', true ),
'medium'               => get_post_meta( $post->ID, 'medium', true ),
'vibe'                 => get_post_meta( $post->ID, 'vibe', true ),
'accessibility'        => get_post_meta( $post->ID, 'accessibility', true ),
'age_range'            => get_post_meta( $post->ID, 'age_range', true ),
);
$events[] = $event;
}

if ( $lat && $lng && $within_km ) {
$events = array_values(
array_filter(
$events,
function ( $evt ) use ( $lat, $lng, $within_km ) {
if ( empty( $evt['event_lat'] ) || empty( $evt['event_lng'] ) ) {
return false;
}
$dist = self::haversine_distance( $lat, $lng, (float) $evt['event_lat'], (float) $evt['event_lng'] );
return $dist <= $within_km;
}
)
);
}

return self::ok( $events );
}

private static function haversine_distance( float $lat1, float $lon1, float $lat2, float $lon2 ): float {
$earth = 6371; // km
$lat_d = deg2rad( $lat2 - $lat1 );
$lon_d = deg2rad( $lon2 - $lon1 );
$a     = sin( $lat_d / 2 ) ** 2 + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * sin( $lon_d / 2 ) ** 2;
$c     = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
return $earth * $c;
}

       private static function ok( $data, int $status = 200 ): WP_REST_Response {
               return new WP_REST_Response( $data, $status );
       }
}
