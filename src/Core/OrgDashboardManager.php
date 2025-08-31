<?php
namespace ArtPulse\Core;

use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Rest\OrgAnalyticsController;

class OrgDashboardManager {

	public static function register() {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes() {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/org/dashboard' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/org/dashboard',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'get_dashboard_data' ),
					'permission_callback' => function () {
						if ( ! current_user_can( 'read' ) ) {
							return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
						}
						return true;
					},
				)
			);
		}
	}

	public static function get_dashboard_data( WP_REST_Request $request ): WP_REST_Response {
		$user_id = get_current_user_id();
		$org_id  = get_user_meta( $user_id, 'ap_organization_id', true );
		if ( ! $org_id ) {
			return \rest_ensure_response( array() );
		}

		$data = array(
			'membership_level'   => get_user_meta( $user_id, 'ap_membership_level', true ),
			'membership_expires' => get_user_meta( $user_id, 'ap_membership_expires', true ),
			'next_payment'       => get_user_meta( $user_id, 'ap_membership_expires', true ),
			'transactions'       => array(),
			'metrics'            => array(),
		);

		if ( function_exists( 'wc_get_orders' ) ) {
			$orders = wc_get_orders(
				array(
					'customer_id' => $user_id,
					'limit'       => 5,
					'orderby'     => 'date',
					'order'       => 'DESC',
				)
			);
			foreach ( $orders as $order ) {
				$data['transactions'][] = array(
					'id'     => $order->get_id(),
					'total'  => $order->get_total(),
					'date'   => $order->get_date_created() ? $order->get_date_created()->getTimestamp() : null,
					'status' => $order->get_status(),
				);
			}
		} else {
			$charges = get_user_meta( $user_id, 'stripe_payment_ids', true );
			if ( is_array( $charges ) ) {
				$charges = array_slice( array_reverse( $charges ), 0, 5 );
				foreach ( $charges as $cid ) {
					$data['transactions'][] = array( 'id' => $cid );
				}
			}
		}

		$metrics = OrgAnalyticsController::get_metrics( new WP_REST_Request() );
		if ( $metrics instanceof WP_REST_Response ) {
			$data['metrics'] = $metrics->get_data();
		} else {
			$data['metrics'] = $metrics;
		}

		return \rest_ensure_response( $data );
	}
}
