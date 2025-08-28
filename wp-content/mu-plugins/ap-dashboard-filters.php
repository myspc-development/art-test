<?php
/**
 * Plugin Name: AP Dashboard Filters
 * Description: Removes API-only dashboard widgets when the API is disabled.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove widgets that rely on the ArtPulse API when the API is disabled.
 *
 * @param array $widgets Widget configuration array.
 * @return array
 */
function ap_filter_dashboard_widgets_for_api( array $widgets ): array {
	$api_enabled = false;

	if ( get_option( 'ap_api_enabled' ) ) {
		$api_enabled = true;
	}

	if ( defined( 'AP_API_ENABLED' ) && AP_API_ENABLED ) {
		$api_enabled = true;
	}

	if ( $api_enabled ) {
		return $widgets;
	}

	foreach ( $widgets as $id => $def ) {
		if ( ! empty( $def['api_only'] ) || ! empty( $def['requires_api'] ) ) {
			unset( $widgets[ $id ] );
			continue;
		}

		if ( isset( $def['availability'] ) ) {
			$availability = $def['availability'];
			if ( ! is_array( $availability ) ) {
				$availability = explode( '|', (string) $availability );
			}
			$availability = array_map( 'strtolower', $availability );
			if ( array_intersect( array( 'api', 'remote' ), $availability ) ) {
				unset( $widgets[ $id ] );
				continue;
			}
		}
	}

	return $widgets;
}
add_filter( 'ap_dashboard_widgets_metadata', 'ap_filter_dashboard_widgets_for_api', 10, 1 );
