<?php
namespace ArtPulse\Rest;

use WP_REST_Request;

class AdvancedEventFilters {
    public static function within_km( WP_REST_Request $request ): array {
        $lat       = (float) $request->get_param( 'lat' );
        $lng       = (float) $request->get_param( 'lng' );
        $within_km = (float) $request->get_param( 'within_km' );

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
                'fields'         => 'ids',
            )
        );

        $events = array();
        foreach ( $query->posts as $id ) {
            $e_lat = get_post_meta( $id, 'event_lat', true );
            $e_lng = get_post_meta( $id, 'event_lng', true );
            if ( $e_lat === '' || $e_lng === '' ) {
                continue;
            }
            $dist = self::haversine_distance( $lat, $lng, (float) $e_lat, (float) $e_lng );
            if ( $dist > $within_km ) {
                continue;
            }

            $categories = wp_get_post_terms( $id, 'genre', array( 'fields' => 'slugs' ) );
            $medium     = wp_get_post_terms( $id, 'medium', array( 'fields' => 'slugs' ) );

            $events[] = array(
                'id'          => $id,
                'title'       => get_the_title( $id ) ?: '',
                'link'        => get_permalink( $id ),
                'distance_km' => round( $dist, 2 ),
                'lat'         => $e_lat,
                'lng'         => $e_lng,
                'start_date'  => get_post_meta( $id, 'event_start_date', true ) ?: '',
                'end_date'    => get_post_meta( $id, 'event_end_date', true ) ?: '',
                'venue'       => get_post_meta( $id, 'venue_name', true ) ?: '',
                'city'        => get_post_meta( $id, 'event_city', true ) ?: '',
                'state'       => get_post_meta( $id, 'event_state', true ) ?: '',
                'country'     => get_post_meta( $id, 'event_country', true ) ?: '',
                'thumbnail'   => get_the_post_thumbnail_url( $id, 'thumbnail' ) ?: '',
                'categories'  => is_wp_error( $categories ) ? array() : $categories,
                'medium'      => is_wp_error( $medium ) ? array() : $medium,
                'style'       => get_post_meta( $id, 'style', true ) ?: '',
            );
        }

        usort(
            $events,
            static function ( $a, $b ) {
                return $a['distance_km'] <=> $b['distance_km'];
            }
        );

        return array_values( $events );
    }

    private static function haversine_distance( float $lat1, float $lng1, float $lat2, float $lng2 ): float {
        $earth = 6371; // km
        $dLat  = deg2rad( $lat2 - $lat1 );
        $dLng  = deg2rad( $lng2 - $lng1 );
        $a     = sin( $dLat / 2 ) * sin( $dLat / 2 ) + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * sin( $dLng / 2 ) * sin( $dLng / 2 );
        $c     = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
        return $earth * $c;
    }
}
