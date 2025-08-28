<?php
namespace ArtPulse\Core;

class RoleResolver {

	/**
	 * Determine the effective plugin role for a user.
	 */
	public static function resolve( int $user_id = 0 ): string {
		$preview = isset( $_GET['ap_preview_role'] ) ? sanitize_key( $_GET['ap_preview_role'] ) : null;
		$allowed = array( 'member', 'artist', 'organization' );
		$nonce   = isset( $_GET['ap_preview_nonce'] ) ? sanitize_key( $_GET['ap_preview_nonce'] ) : '';
		if (
			$preview &&
			in_array( $preview, $allowed, true ) &&
			current_user_can( 'manage_options' ) &&
			wp_verify_nonce( $nonce, 'ap_preview' )
		) {
			return $preview;
		}

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$user = get_userdata( $user_id );
		if ( ! $user || empty( $user->roles ) ) {
			return 'member';
		}

		$roles = array_map( 'sanitize_key', (array) $user->roles );

		if ( in_array( 'administrator', $roles, true ) ) {
			return 'organization';
		}

		foreach ( $roles as $r ) {
			if ( isset( self::ROLE_MAP[ $r ] ) ) {
				return self::ROLE_MAP[ $r ];
			}
		}

		$priority = array( 'member', 'artist', 'organization' );
		foreach ( $priority as $r ) {
			if ( in_array( $r, $roles, true ) ) {
				return $r;
			}
		}

		return 'member';
	}

	private const ROLE_MAP = array(
		'subscriber'  => 'member',
		'contributor' => 'member',
		'author'      => 'member',
		'editor'      => 'member',
	);
}

if ( ! function_exists( 'ap_get_effective_role' ) ) {
	function ap_get_effective_role(): string {
		return RoleResolver::resolve( get_current_user_id() );
	}
}
