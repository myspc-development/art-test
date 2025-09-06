<?php
declare(strict_types=1);

namespace ArtPulse\Tests;

final class DashboardDefinitionSanitizer {
	public static function stripClosures( $value ) {
		if ( $value instanceof \Closure ) {
			return '__closure__';
		}
		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				$value[ $k ] = self::stripClosures( $v );
			}
			return $value;
		}
		if ( is_object( $value ) && is_callable( $value ) ) {
			return get_class( $value ) . '::__invoke';
		}
		return $value;
	}

	public static function register(): void {
		add_filter( 'ap_dashboard_widget_definitions', array( self::class, 'filter' ), 999, 1 );
		add_filter( 'artpulse_dashboard_widget_definitions', array( self::class, 'filter' ), 999, 1 );
	}

	public static function filter( $defs ) {
		if ( ! is_array( $defs ) ) {
			return $defs;
		}
		foreach ( $defs as $id => $def ) {
			$defs[ $id ] = self::stripClosures( $def );
		}
		return $defs;
	}
}
