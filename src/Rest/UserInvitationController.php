<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_Error;
use WP_REST_Response;
use ArtPulse\Rest\RestResponder;

class UserInvitationController {
	use RestResponder;

	public static function register(): void {
		if ( did_action( 'rest_api_init' ) ) {
			self::register_routes();
		} else {
			add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		}
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/org/(?P<id>\\d+)/invite' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/org/(?P<id>\\d+)/invite',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'invite' ),
					'permission_callback' => array( self::class, 'check_permissions' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ),
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/org/(?P<id>\\d+)/users/batch' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/org/(?P<id>\\d+)/users/batch',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'batch_users' ),
					'permission_callback' => array( self::class, 'check_permissions' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => static fn( $value, $request, $param ) => \is_numeric( $value ),
						),
					),
				)
			);
		}
	}

	public static function check_permissions( WP_REST_Request $request ): bool {
		$org_id = absint( $request->get_param( 'id' ) );
		if ( ! $org_id ) {
			return false;
		}
		$user_id = get_current_user_id();
		if ( ! \ArtPulse\Core\ap_user_has_org_role( $user_id, $org_id, 'admin' ) ) {
			return false;
		}
		return current_user_can( 'view_artpulse_dashboard' );
	}

	public static function invite( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$params = $request->get_json_params();
		$emails = $params['emails'] ?? null;
		$role   = sanitize_key( $params['role'] ?? 'viewer' );
		$org_id = absint( $request->get_param( 'id' ) );
		$valid  = array_keys( \ArtPulse\Core\OrgRoleManager::get_roles( $org_id ) );
		if ( ! in_array( $role, $valid, true ) ) {
			$role = 'viewer';
		}
		if ( ! is_array( $emails ) || empty( $emails ) ) {
			return new WP_Error( 'invalid_emails', __( 'Invalid emails', 'artpulse' ), array( 'status' => 400 ) );
		}
		$invited = array();
		foreach ( $emails as $email ) {
			$email = sanitize_email( $email );
			if ( ! $email || ! is_email( $email ) ) {
				return new WP_Error( 'invalid_emails', __( 'Invalid emails', 'artpulse' ), array( 'status' => 400 ) );
			}
			\ArtPulse\Core\EmailService::send(
				$email,
				__( 'Invitation', 'artpulse' ),
				sprintf( __( 'You are invited to organization %d', 'artpulse' ), $org_id )
			);
			$user = get_user_by( 'email', $email );
			if ( $user ) {
				\ArtPulse\Core\MultiOrgRoles::assign_roles( $user->ID, $org_id, array( $role ) );
			}
			$invited[] = $email;
		}
		return \rest_ensure_response(
			array(
				'invited' => $invited,
				'role'    => $role,
			)
		);
	}

	public static function batch_users( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$params   = $request->get_json_params();
		$action   = $params['action'] ?? '';
		$user_ids = $params['user_ids'] ?? array();
		if ( ! in_array( $action, array( 'update', 'suspend', 'delete' ), true ) ) {
			return new WP_Error( 'invalid_action', 'Invalid action', array( 'status' => 400 ) );
		}
		if ( ! is_array( $user_ids ) || empty( $user_ids ) ) {
			return new WP_Error( 'invalid_users', 'Invalid users', array( 'status' => 400 ) );
		}
		$processed = array();
		foreach ( $user_ids as $uid ) {
			$uid = absint( $uid );
			if ( ! $uid ) {
				continue;
			}
			if ( $action === 'update' ) {
				$data = $params['data'] ?? array();
				foreach ( $data as $key => $value ) {
					update_user_meta( $uid, sanitize_key( $key ), sanitize_text_field( $value ) );
				}
				$processed[] = $uid;
			} elseif ( $action === 'suspend' ) {
				update_user_meta( $uid, 'ap_suspended', 1 );
				$processed[] = $uid;
			} elseif ( $action === 'delete' ) {
				wp_delete_user( $uid );
				$processed[] = $uid;
			}
		}
		return \rest_ensure_response(
			array(
				'action'    => $action,
				'processed' => $processed,
			)
		);
	}
}
