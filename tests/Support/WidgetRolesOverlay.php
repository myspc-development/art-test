<?php
declare(strict_types=1);

namespace ArtPulse\Tests;

final class WidgetRolesOverlay {
	public static function register(): void {
		add_filter( 'ap_dashboard_widget_definitions', array( self::class, 'overlay' ), 1200, 1 );
		add_filter( 'artpulse_dashboard_widget_definitions', array( self::class, 'overlay' ), 1200, 1 );
		add_filter( 'ap_dashboard_widget_definitions_final', array( self::class, 'overlay' ), 1200, 1 );
		add_filter( 'artpulse_dashboard_widget_definitions_final', array( self::class, 'overlay' ), 1200, 1 );
	}

	public static function overlay( $defs ) {
		if ( ! is_array( $defs ) ) {
			return $defs;
		}
		$opt = get_option( 'artpulse_widget_roles' );
		if ( ! is_array( $opt ) ) {
			return $defs;
		}
		foreach ( $opt as $id => $conf ) {
			if ( ! isset( $defs[ $id ] ) || ! is_array( $conf ) ) {
				continue;
			}
			if ( array_key_exists( 'roles', $conf ) ) {
				$roles = $conf['roles'];
				if ( $roles === null ) {
					$defs[ $id ]['roles'] = null;
				} elseif ( is_array( $roles ) ) {
					$normalized = array_values( array_unique( array_map( 'strval', $roles ) ) );
					if ( ! empty( $normalized ) ) {
						$defs[ $id ]['roles'] = $normalized;
					}
				} else {
					$defs[ $id ]['roles'] = array( (string) $roles );
				}
			}
			if ( array_key_exists( 'capability', $conf ) ) {
				$defs[ $id ]['capability'] = is_string( $conf['capability'] ) ? $conf['capability'] : '';
			}
		}
		return $defs;
	}
}
