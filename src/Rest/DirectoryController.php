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
        $tax_query  = array( 'relation' => 'AND' );

        foreach ( array( 'vibe', 'accessibility', 'age_range' ) as $key ) {
                $val = $request->get_param( $key );
                if ( ! empty( $val ) ) {
                        $meta_query[] = array(
                                'key'     => $key,
                                'value'   => (array) $val,
                                'compare' => 'IN',
                        );
                }
        }

        foreach ( array( 'genre', 'medium' ) as $taxonomy ) {
                $val = $request->get_param( $taxonomy );
                if ( ! empty( $val ) ) {
                        $tax_query[] = array(
                                'taxonomy' => $taxonomy,
                                'field'    => 'slug',
                                'terms'    => (array) $val,
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
                'fields'         => 'ids',
        );
        if ( count( $meta_query ) > 1 ) {
                $args['meta_query'] = $meta_query;
        }
        if ( count( $tax_query ) > 1 ) {
                $args['tax_query'] = $tax_query;
        }

        $ids = get_posts( $args );

        if ( $lat && $lng && $within_km ) {
                $ids = array_filter(
                        $ids,
                        function ( $id ) use ( $lat, $lng, $within_km ) {
                                $e_lat = get_post_meta( $id, 'event_lat', true );
                                $e_lng = get_post_meta( $id, 'event_lng', true );
                                if ( empty( $e_lat ) || empty( $e_lng ) ) {
                                        return false;
                                }
                                $dist = self::haversine_distance( $lat, $lng, (float) $e_lat, (float) $e_lng );
                                return $dist <= $within_km;
                        }
                );
        }

        $events = array();
        foreach ( $ids as $id ) {
                $genre_terms  = wp_get_post_terms( $id, 'genre', array( 'fields' => 'slugs' ) );
                $medium_terms = wp_get_post_terms( $id, 'medium', array( 'fields' => 'slugs' ) );
                $events[]     = array(
                        'id'                   => $id,
                        'title'                => get_the_title( $id ),
                        'link'                 => get_permalink( $id ),
                        'event_start_date'     => get_post_meta( $id, 'event_start_date', true ),
                        'event_end_date'       => get_post_meta( $id, 'event_end_date', true ),
                        'event_lat'            => get_post_meta( $id, 'event_lat', true ),
                        'event_lng'            => get_post_meta( $id, 'event_lng', true ),
                        'event_street_address' => get_post_meta( $id, 'event_street_address', true ),
                        'genre'                => is_wp_error( $genre_terms ) ? array() : $genre_terms,
                        'medium'               => is_wp_error( $medium_terms ) ? array() : $medium_terms,
                        'vibe'                 => get_post_meta( $id, 'vibe', true ),
                        'accessibility'        => get_post_meta( $id, 'accessibility', true ),
                        'age_range'            => get_post_meta( $id, 'age_range', true ),
                );
        }

        return self::ok( array_values( $events ) );
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
