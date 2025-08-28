<?php
namespace ArtPulse\Frontend;

class EventMapShortcode {

	public static function register(): void {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_event_map', 'Event Map', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function enqueue(): void {
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}

		wp_enqueue_style(
			'leaflet-css',
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
		);
		wp_enqueue_script(
			'leaflet',
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
			array(),
			null,
			true
		);
		wp_enqueue_script(
			'ap-event-map',
			plugin_dir_url( ARTPULSE_PLUGIN_FILE ) . 'assets/js/ap-event-map.js',
			array( 'leaflet' ),
			'1.0',
			true
		);

		wp_localize_script(
			'ap-event-map',
			'APEventMap',
			array(
				'events' => ap_get_events_for_map(),
			)
		);
	}

	public static function render(): string {
		return '<div id="ap-event-map"></div>';
	}
}
