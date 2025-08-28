<?php
namespace ArtPulse\Core;

/**
 * Simple helper for widget feature flags.
 */
class WidgetFlags {
	/**
	 * Determine if a widget is active based on stored options.
	 */
	public static function is_active( string $id ): bool {
		$flags = get_option( 'artpulse_widget_status', array() );
		if ( is_string( $flags ) ) {
			$decoded = json_decode( $flags, true );
			$flags   = is_array( $decoded ) ? $decoded : array();
		}
		$id = DashboardWidgetRegistry::canon_slug( $id );
		if ( $id === '' ) {
			return true;
		}
		if ( array_key_exists( $id, $flags ) ) {
			return (bool) $flags[ $id ];
		}
		return true;
	}
}
