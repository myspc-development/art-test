<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use ArtPulse\Rest\RestResponder;
use ArtPulse\Rest\Util\Auth;

class ImportRestController {
		use RestResponder;

	private static function verify_nonce( WP_REST_Request $request, string $action ): bool|\WP_Error {
			return Auth::verify_nonce( $request->get_header( 'X-AP-Nonce' ), $action );
	}

	/**
	 * Allowed post types for import.
	 *
	 * @var string[]
	 */
	protected static array $allowed_post_types = array(
		'artpulse_org',
		'artpulse_event',
		'artpulse_artist',
		'artpulse_artwork',
	);

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
				register_rest_route(
					'artpulse/v1',
					'/import',
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( self::class, 'handle_import' ),
						'permission_callback' => Auth::require_login_and_cap( 'manage_options' ),
					)
				);
	}

	public static function handle_import( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		if ( ! current_user_can( 'edit_posts' ) ) {
				return new WP_REST_Response( array( 'message' => 'Insufficient permissions' ), 403 );
		}
			$nonce = self::verify_nonce( $request, 'ap_import_content' );
		if ( is_wp_error( $nonce ) ) {
					return $nonce;
		}
				$params = $request->get_json_params();
		if ( empty( $params ) ) {
				$params = $request->get_body_params();
		}
				$rows      = $params['rows'] ?? array();
				$post_type = sanitize_key( $params['post_type'] ?? '' );
				$trim      = ! empty( $params['trim_whitespace'] );

		if ( ! in_array( $post_type, self::$allowed_post_types, true ) ) {
			return new WP_REST_Response( array( 'message' => 'Invalid post type' ), 400 );
		}

				$created = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$postarr = array(
				'post_type'   => $post_type,
				'post_status' => 'publish',
			);

			if ( isset( $row['post_title'] ) ) {
				$title = $row['post_title'];
				if ( $trim ) {
					$title = trim( (string) $title );
				}
				$postarr['post_title'] = sanitize_text_field( $title );
				unset( $row['post_title'] );
			}
			if ( isset( $row['post_content'] ) ) {
				$content = $row['post_content'];
				if ( $trim ) {
					$content = trim( (string) $content );
				}
				$postarr['post_content'] = wp_kses_post( $content );
				unset( $row['post_content'] );
			}

			$post_id = wp_insert_post( $postarr, true );
			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			foreach ( $row as $key => $value ) {
				if ( $trim ) {
					$value = trim( (string) $value );
				}
				update_post_meta( $post_id, sanitize_key( $key ), sanitize_text_field( $value ) );
			}
			$created[] = $post_id;
		}

				return \rest_ensure_response( array( 'created' => $created ) );
	}
}
