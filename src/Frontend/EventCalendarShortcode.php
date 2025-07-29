<?php
namespace ArtPulse\Frontend;

class EventCalendarShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_event_calendar', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void
    {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
        wp_enqueue_style(
            'fullcalendar-css',
            plugins_url('assets/libs/fullcalendar/6.1.11/main.min.css', ARTPULSE_PLUGIN_FILE)
        );
        wp_enqueue_script(
            'fullcalendar-js',
            plugins_url('assets/libs/fullcalendar/6.1.11/main.min.js', ARTPULSE_PLUGIN_FILE),
            [],
            null,
            true
        );
        wp_enqueue_script(
            'ap-event-calendar',
            plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/js/ap-event-calendar.js',
            ['fullcalendar-js', 'jquery'],
            '1.0',
            true
        );

        wp_localize_script('ap-event-calendar', 'APCalendar', [
            'events'    => ap_get_events_for_calendar(),
            'rest_root' => esc_url_raw(rest_url()),
            'close_text' => __('Close', 'artpulse'),
            'nonce'      => wp_create_nonce('wp_rest'),
        ]);
    }

    public static function render(): string
    {
        return '<div id="ap-event-calendar"></div>';
    }
}
