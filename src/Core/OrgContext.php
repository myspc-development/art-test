<?php
namespace ArtPulse\Core;

use function ArtPulse\Core\ap_user_has_org_role;

class OrgContext {

	public static function register(): void {
		add_action( 'init', array( self::class, 'start_session' ), 0 );
		add_action( 'init', array( self::class, 'handle_switch' ), 1 );
	}

	public static function start_session(): void {
		if ( PHP_SAPI !== 'cli' && ! headers_sent() && ! session_id() ) {
			session_start();
		}
	}

	public static function handle_switch(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}
		if ( isset( $_GET['ap_switch_org'] ) ) {
			$org  = absint( $_GET['ap_switch_org'] );
			$user = get_current_user_id();
			if ( $org && ap_user_has_org_role( $user, $org ) ) {
				$_SESSION['ap_active_org'] = $org;
			}
		}
	}

	public static function set_active_org( int $org_id ): void {
		$_SESSION['ap_active_org'] = $org_id;
	}

	public static function get_active_org( int $user_id = 0 ): int {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( isset( $_SESSION['ap_active_org'] ) ) {
			$org = absint( $_SESSION['ap_active_org'] );
			if ( $org && ap_user_has_org_role( $user_id, $org ) ) {
				return $org;
			}
		}
		$orgs = MultiOrgRoles::get_user_orgs( $user_id );
		if ( ! empty( $orgs ) ) {
			return intval( $orgs[0]['org_id'] );
		}
		return absint( get_user_meta( $user_id, 'ap_organization_id', true ) );
	}

	/**
	 * Get the current organization taking admin override into account.
	 */
	public static function get_current_org_id(): int {
		if ( current_user_can( 'administrator' ) && isset( $_GET['org_id'] ) ) {
			return absint( $_GET['org_id'] );
		}

		return self::get_active_org();
	}
}
