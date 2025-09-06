<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Core\MultiOrgRoles;
use function ArtPulse\Core\ap_user_has_org_role;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class OrgUserRolesController {
	use RestResponder;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/orgs/(?P<id>\d+)/roles' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/orgs/(?P<id>\d+)/roles',
				array(
					array(
						'methods'             => 'GET',
						'callback'            => array( self::class, 'list_roles' ),
						'permission_callback' => array( self::class, 'can_view' ),
					),
					array(
						'methods'             => 'POST',
						'callback'            => array( self::class, 'add_role' ),
						'permission_callback' => array( self::class, 'can_manage' ),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/orgs/(?P<id>\d+)/roles/(?P<user_id>\d+)' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/orgs/(?P<id>\d+)/roles/(?P<user_id>\d+)',
				array(
					'methods'             => 'DELETE',
					'callback'            => array( self::class, 'remove_role' ),
					'permission_callback' => array( self::class, 'can_manage' ),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/users/me/orgs' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/users/me/orgs',
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'my_orgs' ),
					'permission_callback' => Auth::require_login_and_cap( null ),
				)
			);
		}
	}

	public static function can_manage( WP_REST_Request $request ): bool {
		$org_id  = absint( $request['id'] );
		$user_id = get_current_user_id();
		return ap_user_has_org_role( $user_id, $org_id, 'admin' ) || user_can( $user_id, 'manage_options' );
	}

	public static function can_view( WP_REST_Request $request ): bool {
		$org_id  = absint( $request['id'] );
		$user_id = get_current_user_id();
		return ap_user_has_org_role( $user_id, $org_id ) || user_can( $user_id, 'manage_options' );
	}

	public static function list_roles( WP_REST_Request $request ): WP_REST_Response|WP_Error {
			global $wpdb;
			$org_id = absint( $request['id'] );
			$table  = $wpdb->prefix . 'ap_org_user_roles';
			$exists = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( ! $exists ) {
				return ( new self() )->fail( 'ap_db_missing', 'Required table missing', 500 );
		}
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT user_id, role, status FROM $table WHERE org_id = %d", $org_id ), ARRAY_A );
			return \rest_ensure_response( $rows );
	}

	public static function add_role( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$org_id  = absint( $request['id'] );
		$user_id = absint( $request->get_param( 'user_id' ) );
		$role    = sanitize_key( $request->get_param( 'role' ) );
		if ( ! $user_id || ! $role ) {
			return new WP_REST_Response( array( 'error' => 'invalid' ), 400 );
		}
		MultiOrgRoles::assign_roles( $user_id, $org_id, array( $role ) );
		return \rest_ensure_response( array( 'success' => true ) );
	}

	public static function remove_role( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$org_id  = absint( $request['id'] );
		$user_id = absint( $request['user_id'] );
		MultiOrgRoles::remove_role( $user_id, $org_id );
		return \rest_ensure_response( array( 'success' => true ) );
	}

	public static function my_orgs( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$user_id = get_current_user_id();
		$data    = MultiOrgRoles::get_user_orgs( $user_id );
		return \rest_ensure_response( $data );
	}
}
