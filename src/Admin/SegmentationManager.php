<?php
namespace ArtPulse\Admin;

/**
 * Filters and exports user segments.
 */
class SegmentationManager {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/admin/users' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/admin/users',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'handle' ),
					'permission_callback' => array( self::class, 'check_permission' ),
				)
			);
		}
	}

	public static function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public static function handle( \WP_REST_Request $request ) {
		$role  = sanitize_text_field( $request->get_param( 'role' ) );
		$level = sanitize_text_field( $request->get_param( 'level' ) );

		$args = array(
			'number' => 1000,
			'fields' => array( 'ID', 'display_name', 'user_email' ),
		);

		if ( $role ) {
			$args['role__in'] = array( $role );
		}

		if ( $level ) {
			$args['meta_query'] = array(
				array(
					'key'   => 'ap_membership_level',
					'value' => $level,
				),
			);
		}

		$users = get_users( $args );

		$rows = array_map(
			static fn( $u ) => array(
				'ID'    => $u->ID,
				'name'  => $u->display_name ?: $u->user_login,
				'email' => $u->user_email,
			),
			$users
		);

		if ( $request->get_param( 'format' ) === 'csv' ) {
			$stream = fopen( 'php://temp', 'w' );
			fputcsv( $stream, array( 'ID', 'name', 'email' ) );
			foreach ( $rows as $row ) {
				fputcsv( $stream, array( $row['ID'], $row['name'], $row['email'] ) );
			}
			rewind( $stream );
			$csv = stream_get_contents( $stream );
			fclose( $stream );

			return new \WP_REST_Response(
				$csv,
				200,
				array(
					'Content-Type'        => 'text/csv',
					'Content-Disposition' => 'attachment; filename="users.csv"',
				)
			);
		}

		return \rest_ensure_response( $rows );
	}
}
