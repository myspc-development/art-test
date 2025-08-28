<?php
namespace ArtPulse\Frontend;

class EventCalendarShortcode {

	public static function register(): void {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_event_calendar', 'Event Calendar', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function enqueue(): void {
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}
		wp_enqueue_style(
			'fullcalendar-css',
			plugins_url( 'assets/libs/fullcalendar/6.1.11/main.min.css', ARTPULSE_PLUGIN_FILE )
		);
		wp_enqueue_script(
			'fullcalendar-js',
			plugins_url( 'assets/libs/fullcalendar/6.1.11/main.min.js', ARTPULSE_PLUGIN_FILE ),
			array(),
			null,
			true
		);
		wp_enqueue_script(
			'ap-event-calendar',
			plugin_dir_url( ARTPULSE_PLUGIN_FILE ) . 'assets/js/ap-event-calendar.js',
			array( 'fullcalendar-js' ),
			'1.0',
			true
		);
		if ( function_exists( 'wp_script_add_data' ) ) {
			wp_script_add_data( 'ap-event-calendar', 'type', 'module' );
		}

		wp_localize_script(
			'ap-event-calendar',
			'APCalendar',
			array(
				'apiRoot' => esc_url_raw( rest_url() ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	public static function render(): string {
		return '<div id="ap-event-calendar"></div>';
	}
}
