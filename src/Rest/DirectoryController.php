<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class DirectoryController {
		use RestResponder;

	public static function register(): void {
			$controller = new self();
		if ( did_action( 'rest_api_init' ) ) {
				$controller->register_routes();
		} else {
				add_action( 'rest_api_init', array( $controller, 'register_routes' ) );
		}
	}

	public function register_routes(): void {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/events',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_events' ),
					'permission_callback' => '__return_true',
				)
			);
	}


	public function get_events( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'     => 'ap_event_end_ts',
				'value'   => time(),
				'compare' => '>=',
				'type'    => 'NUMERIC',
			),
		);
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

		$region     = $request->get_param( 'region' );
		$has_region = ! empty( $region );
		if ( $has_region ) {
				$region_terms = array_map( 'sanitize_text_field', (array) $region );
			if ( taxonomy_exists( 'region' ) ) {
					$tax_query[] = array(
						'taxonomy' => 'region',
						'field'    => 'slug',
						'terms'    => $region_terms,
					);
			} else {
					$meta_query[] = array(
						'key'     => 'region',
						'value'   => $region_terms,
						'compare' => 'IN',
					);
			}
		}

		$lat       = $request->get_param( 'lat' );
		$lng       = $request->get_param( 'lng' );
		$within_km = $request->get_param( 'within_km' );
		$do_radius = is_numeric( $lat ) && is_numeric( $lng ) && is_numeric( $within_km );
		if ( $do_radius && ! $has_region ) {
				$lat          = (float) $lat;
				$lng          = (float) $lng;
				$r            = (float) $within_km / 111.0;
				$meta_query[] = array(
					'key'     => 'event_lat',
					'value'   => array( $lat - $r, $lat + $r ),
					'compare' => 'BETWEEN',
					'type'    => 'NUMERIC',
				);
				$meta_query[] = array(
					'key'     => 'event_lng',
					'value'   => array( $lng - $r, $lng + $r ),
					'compare' => 'BETWEEN',
					'type'    => 'NUMERIC',
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
			'meta_query'     => $meta_query,
		);
		if ( count( $tax_query ) > 1 ) {
				$args['tax_query'] = $tax_query;
		}

		$ids = get_posts( $args );

		$events = array();
		foreach ( $ids as $id ) {
			if ( $do_radius ) {
					$e_lat = get_post_meta( $id, 'event_lat', true );
					$e_lng = get_post_meta( $id, 'event_lng', true );
				if ( $e_lat === '' || $e_lng === '' ) {
					if ( ! $has_region ) {
						continue;
					}
				} else {
						$dist = $this->haversine_distance( $lat, $lng, (float) $e_lat, (float) $e_lng );
					if ( $dist > (float) $within_km ) {
							continue;
					}
				}
			}

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

		return $this->ok( array_values( $events ) );
	}

	private function haversine_distance( float $lat1, float $lng1, float $lat2, float $lng2 ): float {
			$earth = 6371; // km
			$dLat  = deg2rad( $lat2 - $lat1 );
			$dLon  = deg2rad( $lng2 - $lng1 );
			$a     = sin( $dLat / 2 ) * sin( $dLat / 2 ) + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * sin( $dLon / 2 ) * sin( $dLon / 2 );
			$c     = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
			return $earth * $c;
	}
}
