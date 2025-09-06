<?php
namespace ArtPulse\Rest;

use ArtPulse\Rest\Util\Auth;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Rest\RestResponder;

class LocationRestController {
	use RestResponder;

	public static function register(): void {
		add_action(
			'rest_api_init',
			function () {
				if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/location/geonames' ) ) {
					register_rest_route(
						ARTPULSE_API_NAMESPACE,
						'/location/geonames',
						array(
							'methods'             => 'GET',
							'callback'            => array( self::class, 'geonames' ),
							'permission_callback' => Auth::require_login_and_cap( null ),
						)
					);
				}
				if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/location/google' ) ) {
					register_rest_route(
						ARTPULSE_API_NAMESPACE,
						'/location/google',
						array(
							'methods'             => 'GET',
							'callback'            => array( self::class, 'google' ),
							'permission_callback' => Auth::require_login_and_cap( null ),
						)
					);
				}
			}
		);
	}

	public static function geonames( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$opts = get_option( 'artpulse_settings', array() );
		$user = $opts['geonames_username'] ?? '';
		if ( ! $user ) {
			return new WP_Error( 'missing_key', 'Geonames username not set.', array( 'status' => 400 ) );
		}
		$type    = sanitize_key( $req->get_param( 'type' ) );
		$country = sanitize_text_field( $req->get_param( 'country' ) );
		$state   = sanitize_text_field( $req->get_param( 'state' ) );

		if ( $type === 'countries' ) {
			$url  = "https://api.geonames.org/countryInfoJSON?maxRows=1000&username={$user}";
			$resp = wp_remote_get( $url );
			if ( is_wp_error( $resp ) ) {
				return $resp;
			}
			$data      = json_decode( wp_remote_retrieve_body( $resp ), true );
			$countries = array();
			foreach ( $data['geonames'] ?? array() as $c ) {
				$countries[] = array(
					'code' => $c['countryCode'] ?? '',
					'name' => $c['countryName'] ?? '',
				);
			}
			self::merge_into_dataset( 'countries', $countries );
			return \rest_ensure_response( $countries );
		}

		if ( $type === 'states' && $country ) {
			$url  = "https://api.geonames.org/searchJSON?featureCode=ADM1&country={$country}&maxRows=1000&username={$user}";
			$resp = wp_remote_get( $url );
			if ( is_wp_error( $resp ) ) {
				return $resp;
			}
			$data   = json_decode( wp_remote_retrieve_body( $resp ), true );
			$states = array();
			foreach ( $data['geonames'] ?? array() as $s ) {
				$states[] = array(
					'code'    => $s['adminCode1'] ?? '',
					'name'    => $s['name'] ?? '',
					'country' => $country,
				);
			}
			self::merge_into_dataset( 'states', $states );
			return \rest_ensure_response( $states );
		}

		if ( $type === 'cities' && $country && $state ) {
			$url  = "https://api.geonames.org/searchJSON?featureClass=P&country={$country}&adminCode1={$state}&maxRows=1000&username={$user}";
			$resp = wp_remote_get( $url );
			if ( is_wp_error( $resp ) ) {
				return $resp;
			}
			$data   = json_decode( wp_remote_retrieve_body( $resp ), true );
			$cities = array();
			foreach ( $data['geonames'] ?? array() as $c ) {
				$cities[] = array(
					'name'    => $c['name'] ?? '',
					'state'   => $state,
					'country' => $country,
				);
			}
			self::merge_into_dataset( 'cities', $cities );
			return \rest_ensure_response( $cities );
		}
		return new WP_Error( 'invalid_params', 'Invalid parameters', array( 'status' => 400 ) );
	}

	public static function google( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$opts = get_option( 'artpulse_settings', array() );
		$key  = $opts['google_places_key'] ?? '';
		if ( ! $key ) {
			// Gracefully degrade when no API key is configured
			return \rest_ensure_response( array() );
		}
		$query = urlencode( $req->get_param( 'query' ) );
		$url   = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input={$query}&key={$key}";
		$resp  = wp_remote_get( $url );
		if ( is_wp_error( $resp ) ) {
			return $resp;
		}
		$data = json_decode( wp_remote_retrieve_body( $resp ), true );
		return \rest_ensure_response( $data['predictions'] ?? array() );
	}

	private static function merge_into_dataset( string $key, array $items ): void {
		$upload = wp_upload_dir();
		$dir    = trailingslashit( $upload['basedir'] ) . 'artpulse-data';
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		switch ( $key ) {
			case 'countries':
				$file = $dir . '/countries.json';
				break;
			case 'states':
				$file = $dir . '/states.json';
				break;
			case 'cities':
				$file = $dir . '/cities.json';
				break;
			default:
				return;
		}

		if ( ! file_exists( $file ) ) {
			file_put_contents( $file, json_encode( $items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
			return;
		}

		$current = json_decode( file_get_contents( $file ), true );
		if ( ! is_array( $current ) ) {
			$current = array();
		}

		foreach ( $items as $item ) {
			$exists = false;
			foreach ( $current as $existing ) {
				if (
					$key === 'countries' && isset( $existing['code'] ) && $existing['code'] === $item['code']
				) {
					$exists = true;
					break;
				}
				if (
					$key === 'states' &&
					isset( $existing['code'], $existing['country'] ) &&
					$existing['code'] === $item['code'] &&
					$existing['country'] === $item['country']
				) {
					$exists = true;
					break;
				}
				if (
					$key === 'cities' &&
					isset( $existing['name'], $existing['state'], $existing['country'] ) &&
					$existing['name'] === $item['name'] &&
					$existing['state'] === $item['state'] &&
					$existing['country'] === $item['country']
				) {
					$exists = true;
					break;
				}
			}

			if ( ! $exists ) {
				$current[] = $item;
			}
		}

		file_put_contents( $file, json_encode( $current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
	}
}
