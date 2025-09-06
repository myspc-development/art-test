<?php

namespace ArtPulse\Core;

/**
 * Validate widget access for a given role.
 */
class WidgetAccessValidator {
	/**
	 * Determine if a widget is accessible for a role.
	 *
	 * @param string $id   Widget ID.
	 * @param string $role Role slug.
	 * @param array  $entry Layout entry providing extra capability requirements.
	 * @return array{allowed:bool,reason?:string,cap?:string}
	 */
	public static function validate( string $id, string $role, array $entry = array() ): array {
		$config = DashboardWidgetRegistry::getById( $id );
		if ( ! $config ) {
			return array(
				'allowed' => false,
				'reason'  => 'unregistered',
			);
		}

		$roles = isset( $config['roles'] ) ? (array) $config['roles'] : array();
		if ( $roles && ! in_array( $role, $roles, true ) ) {
			return array(
				'allowed' => false,
				'reason'  => 'role_mismatch',
			);
		}

		$caps = array();
		if ( ! empty( $config['capability'] ) ) {
			$caps[] = $config['capability'];
		}
		if ( ! empty( $entry['capability'] ) ) {
			$caps[] = $entry['capability'];
		}

		if ( $caps ) {
			$role_obj = function_exists( 'get_role' ) ? get_role( $role ) : null;
			foreach ( $caps as $cap ) {
				if ( $cap && $role !== 'administrator' ) {
					if ( ! $role_obj || ! $role_obj->has_cap( $cap ) ) {
						return array(
							'allowed' => false,
							'reason'  => 'missing_capability',
							'cap'     => $cap,
						);
					}
				}
			}
		}

		return array( 'allowed' => true );
	}
}
