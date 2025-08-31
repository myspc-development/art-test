<?php
namespace ArtPulse\Marketplace;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class PromotionManager {

	public static function register(): void {
		add_action( 'init', array( self::class, 'maybe_install_table' ) );
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_promotions';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			self::install_table();
		}
	}

	public static function install_table(): void {
		\ArtPulse\DB\create_monetization_tables();
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/promoted' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/promoted',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'list_promoted' ),
					'permission_callback' => function () {
						return current_user_can( 'read' );
					},
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/promote' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/promote',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'create_promotion' ),
					'permission_callback' => fn() => current_user_can( 'edit_posts' ),
					'args'                => array(
						'artwork_id'     => array(
							'type'     => 'integer',
							'required' => true,
						),
						'start_date'     => array(
							'type'     => 'string',
							'required' => true,
						),
						'end_date'       => array(
							'type'     => 'string',
							'required' => true,
						),
						'type'           => array(
							'type'    => 'string',
							'default' => 'featured',
						),
						'priority_level' => array(
							'type'    => 'integer',
							'default' => 0,
						),
					),
				)
			);
		}
	}

	public static function list_promoted(): WP_REST_Response {
		global $wpdb;
		$table = $wpdb->prefix . 'ap_promotions';
		$today = current_time( 'Y-m-d' );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE start_date <= %s AND end_date >= %s ORDER BY priority_level DESC, start_date DESC",
				$today,
				$today
			),
			ARRAY_A
		);
		return \rest_ensure_response( $rows );
	}

	public static function create_promotion( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$artwork_id = absint( $req->get_param( 'artwork_id' ) );
		$start      = sanitize_text_field( $req->get_param( 'start_date' ) );
		$end        = sanitize_text_field( $req->get_param( 'end_date' ) );
		$type       = sanitize_key( $req->get_param( 'type' ) ?? 'featured' );
		$priority   = intval( $req->get_param( 'priority_level' ) );
		if ( ! $artwork_id || ! $start || ! $end ) {
			return new WP_Error( 'invalid_params', 'Invalid parameters', array( 'status' => 400 ) );
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ap_promotions';
		$wpdb->insert(
			$table,
			array(
				'artwork_id'     => $artwork_id,
				'start_date'     => $start,
				'end_date'       => $end,
				'type'           => $type,
				'priority_level' => $priority,
			)
		);
		return \rest_ensure_response( array( 'id' => (int) $wpdb->insert_id ) );
	}
}
