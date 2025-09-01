<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class UserAccountRestController {
	use RestResponder;

	public static function register(): void {
		// If rest_api_init already ran (e.g. register() invoked within the
		// hook itself) register routes immediately. Otherwise hook normally.
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/user/export' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/user/export',
				array(
                                        'methods'             => 'GET',
                                        'callback'            => array( self::class, 'export_user_data' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
					'args'                => array(
						'format' => array(
							'type'    => 'string',
							'enum'    => array( 'json', 'csv' ),
							'default' => 'json',
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/user/delete' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/user/delete',
				array(
                                        'methods'             => 'POST',
                                        'callback'            => array( self::class, 'delete_user_data' ),
                                        'permission_callback' => array( Auth::class, 'guard_read' ),
				)
			);
		}
	}

	public static function export_user_data( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$format  = $request->get_param( 'format' ) ?: 'json';
		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );

		if ( ! $user ) {
			return new WP_Error( 'invalid_user', 'Invalid user', array( 'status' => 400 ) );
		}

		$profile = array(
			'ID'                 => $user->ID,
			'display_name'       => $user->display_name,
			'email'              => $user->user_email,
			'membership_level'   => get_user_meta( $user_id, 'ap_membership_level', true ),
			'membership_expires' => get_user_meta( $user_id, 'ap_membership_expires', true ),
			'country'            => get_user_meta( $user_id, 'ap_country', true ),
			'state'              => get_user_meta( $user_id, 'ap_state', true ),
			'city'               => get_user_meta( $user_id, 'ap_city', true ),
		);

		$post_types = array( 'artpulse_event', 'artpulse_artist', 'artpulse_artwork', 'artpulse_org' );
		$posts      = get_posts(
			array(
				'post_type'   => $post_types,
				'post_status' => 'any',
				'author'      => $user_id,
				'numberposts' => -1,
			)
		);
		$post_rows  = array();
		foreach ( $posts as $p ) {
			$post_rows[] = array(
				'ID'     => $p->ID,
				'type'   => $p->post_type,
				'title'  => $p->post_title,
				'status' => $p->post_status,
			);
		}

		if ( $format === 'csv' ) {
			$stream = fopen( 'php://temp', 'w' );
			fputcsv( $stream, array_keys( $profile ) );
			fputcsv( $stream, array_values( $profile ) );
			fputcsv( $stream, array() ); // blank line
			fputcsv( $stream, array( 'post_id', 'post_type', 'post_title', 'post_status' ) );
			foreach ( $post_rows as $row ) {
				fputcsv( $stream, array( $row['ID'], $row['type'], $row['title'], $row['status'] ) );
			}
			rewind( $stream );
			$csv = stream_get_contents( $stream );
			fclose( $stream );
			return new WP_REST_Response(
				$csv,
				200,
				array(
					'Content-Type'        => 'text/csv',
					'Content-Disposition' => 'attachment; filename="user-export.csv"',
				)
			);
		}

		return \rest_ensure_response(
			array(
				'profile' => $profile,
				'posts'   => $post_rows,
			)
		);
	}

	public static function delete_user_data( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$user_id = get_current_user_id();

		$post_types = array( 'artpulse_event', 'artpulse_artist', 'artpulse_artwork', 'artpulse_org' );
		$post_ids   = get_posts(
			array(
				'post_type'   => $post_types,
				'post_status' => 'any',
				'author'      => $user_id,
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);
		foreach ( $post_ids as $pid ) {
			wp_trash_post( $pid );
		}

		$meta_keys = array(
			'ap_country',
			'ap_state',
			'ap_city',
			'ap_membership_level',
			'ap_membership_expires',
			'ap_membership_paused',
			'stripe_customer_id',
			'stripe_payment_ids',
			'ap_push_token',
			'ap_phone_number',
			'ap_sms_opt_in',
		);
		foreach ( $meta_keys as $key ) {
			delete_user_meta( $user_id, $key );
		}

		wp_delete_user( $user_id );

		return \rest_ensure_response( array( 'success' => true ) );
	}
}
