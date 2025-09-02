<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use function ArtPulse\Core\ap_user_has_org_role;
use ArtPulse\Rest\RestResponder;

class OrgDashboardController {
	use RestResponder;
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'routes' ) );
	}

	public static function routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/org/(?P<id>\d+)/events/summary' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/org/(?P<id>\d+)/events/summary',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'event_summary' ),
					'permission_callback' => array( self::class, 'can_view' ),
					'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/org/(?P<id>\d+)/team/invite' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/org/(?P<id>\d+)/team/invite',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'team_invite' ),
					'permission_callback' => array( self::class, 'can_manage' ),
					'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/org/(?P<id>\d+)/tickets/metrics' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/org/(?P<id>\d+)/tickets/metrics',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'ticket_metrics' ),
					'permission_callback' => array( self::class, 'can_view' ),
					'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/org/(?P<id>\d+)/message/broadcast' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/org/(?P<id>\d+)/message/broadcast',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'broadcast' ),
					'permission_callback' => array( self::class, 'can_manage' ),
					'args'                => array( 'id' => array( 'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ) ) ),
				)
			);
		}
	}

	public static function can_view( WP_REST_Request $req ): bool {
		$org_id = absint( $req['id'] );
		$uid    = get_current_user_id();
		return $org_id && ap_user_has_org_role( $uid, $org_id );
	}

	public static function can_manage( WP_REST_Request $req ): bool {
		$org_id = absint( $req['id'] );
		$uid    = get_current_user_id();
		return $org_id && ap_user_has_org_role( $uid, $org_id, 'admin' );
	}

	public static function event_summary( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$org_id = absint( $req['id'] );
		$query  = new \WP_Query(
			array(
				'post_type'   => 'artpulse_event',
				'post_status' => array( 'publish', 'future', 'draft', 'pending' ),
				'meta_key'    => '_ap_event_organization',
				'meta_value'  => $org_id,
				'fields'      => 'ids',
				'nopaging'    => true,
			)
		);
		$events = array();
		foreach ( $query->posts as $id ) {
			$events[] = array(
				'id'     => $id,
				'title'  => get_the_title( $id ),
				'status' => get_post_status( $id ),
			);
		}
		return \rest_ensure_response( array( 'events' => $events ) );
	}

	public static function team_invite( WP_REST_Request $req ) {
		return UserInvitationController::invite( $req );
	}

        public static function ticket_metrics( WP_REST_Request $req ): WP_REST_Response|WP_Error {
                global $wpdb;
                $org_id = absint( $req['id'] );
                $table  = $wpdb->prefix . 'ap_tickets';
                $exists = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
                if ( ! $exists ) {
                        return ( new self() )->fail( 'ap_db_missing', 'Required table missing', 500 );
                }
                $sales   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM $table WHERE org_id = %d AND status = 'active'", $org_id ) );
                $revenue = (float) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(total) FROM $table WHERE org_id = %d AND status = 'active'", $org_id ) );
                return \rest_ensure_response(
                        array(
                                'sales'   => $sales,
                                'revenue' => $revenue,
                        )
                );
        }

	public static function broadcast( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$org_id = absint( $req['id'] );
		$msg    = sanitize_text_field( $req->get_param( 'message' ) );
		if ( ! $msg ) {
			return new WP_Error( 'invalid_message', 'Message required', array( 'status' => 400 ) );
		}
		$key = 'ap_org_broadcast_' . $org_id;
		$log = get_transient( $key );
		if ( ! is_array( $log ) ) {
			$log = array(); }
		$log[] = array(
			'user' => get_current_user_id(),
			'time' => current_time( 'mysql' ),
			'msg'  => $msg,
		);
		set_transient( $key, $log, DAY_IN_SECONDS );
		return \rest_ensure_response( array( 'sent' => true ) );
	}
}
