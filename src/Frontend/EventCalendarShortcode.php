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
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css'
        );
        wp_enqueue_script(
            'fullcalendar-js',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.js',
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
            'events' => ap_get_events_for_calendar(),
        ]);
    }

    public static function render(): string
    {
        return '<div id="ap-event-calendar"></div>';
    }
}
