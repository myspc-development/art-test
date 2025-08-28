<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Dashboard\WidgetGuard;

/**
 * Ensure all dashboard widgets referenced in config, manifest or role layouts are registered.
 */
function ap_dashboard_bootstrap(): void {
	$base = plugin_dir_path( ARTPULSE_PLUGIN_FILE );

	$config = file_exists( $base . 'config/dashboard-widgets.php' )
		? include $base . 'config/dashboard-widgets.php'
		: array();

	$manifest      = array();
	$manifest_path = $base . 'widget-manifest.json';
	if ( file_exists( $manifest_path ) ) {
		$json     = file_get_contents( $manifest_path );
		$manifest = is_string( $json ) ? json_decode( $json, true ) : array();
		if ( ! is_array( $manifest ) ) {
			$manifest = array();
		}
	}

	$option = get_option( 'ap_dashboard_widget_config', array() );

	$ids = array_merge( array_keys( $config ), array_keys( $manifest ) );
	foreach ( $option as $entry ) {
		$layout = $entry['layout'] ?? $entry;
		if ( is_array( $layout ) ) {
			foreach ( $layout as $row ) {
				$ids[] = sanitize_key( is_array( $row ) ? ( $row['id'] ?? '' ) : $row );
			}
		}
	}
	$ids = array_unique( array_filter( $ids ) );

	foreach ( $ids as $id ) {
		$def      = DashboardWidgetRegistry::getById( $id );
		$callback = $def['callback'] ?? null;
		$class    = $def['class'] ?? null;
		$callable = $def && ( is_callable( $callback ) || ( is_string( $class ) && method_exists( $class, 'render' ) ) );
		if ( $callable ) {
			continue;
		}
		WidgetGuard::register_stub_widget( $id, array(), $def ?? array() );
	}

	WidgetGuard::validate_and_patch();
}

add_action( 'plugins_loaded', 'ap_dashboard_bootstrap', 20 );
